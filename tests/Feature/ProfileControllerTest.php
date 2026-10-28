<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Exercises authenticated profile management.
 */
class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Guests are redirected to login before accessing profile pages.
     */
    public function test_guest_cannot_access_the_profile(): void
    {
        $this->get(route('profile.edit'))->assertRedirect(route('login'));

        $this->put(route('profile.update'), [])->assertRedirect(route('login'));

        $this->get(route('profile.avatar'))->assertRedirect(route('login'));

        $this->post(route('profile.avatar.uploads.store'))->assertRedirect(route('login'));

        $this->delete(route('profile.avatar.uploads.destroy'))->assertRedirect(route('login'));
    }

    /**
     * A valid update changes the profile and password without accepting a client-supplied avatar path.
     */
    public function test_user_can_update_their_own_profile(): void
    {
        $user = User::factory()->create(['user_pfp' => 'existing-avatar.png']);

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'user_name' => 'Updated Name',
            'user_email' => 'updated@example.com',
            'user_pfp' => 'avatars/profile.png',
            'avatar_upload_id' => 'false',
            'password' => 'updated-secure-password',
            'password_confirmation' => 'updated-secure-password',
            'user_id' => 999,
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('status', 'Profile updated.');
        $this->assertDatabaseHas('users', [
            'user_id' => $user->user_id,
            'user_name' => 'Updated Name',
            'user_email' => 'updated@example.com',
            'user_pfp' => 'existing-avatar.png',
        ]);
        $this->assertTrue(Hash::check('updated-secure-password', $user->fresh()->user_password));
        $this->assertDatabaseMissing('users', ['user_id' => 999]);
    }

    /**
     * Omitting a replacement password preserves the current password hash.
     */
    public function test_profile_update_keeps_the_current_password_when_omitted(): void
    {
        $user = User::factory()->create();
        $passwordHash = $user->user_password;

        $response = $this->actingAs($user)->put(route('profile.update'), [
            'user_name' => $user->user_name,
            'user_email' => $user->user_email,
            'user_pfp' => null,
        ]);

        $response->assertRedirect(route('profile.edit'));
        $this->assertSame($passwordHash, $user->fresh()->user_password);
    }

    /**
     * A valid FilePond upload is staged and applied only when its session-bound ID is submitted.
     */
    public function test_user_can_stage_and_apply_an_avatar_upload(): void
    {
        Storage::fake('avatars');
        Storage::fake('tmp');
        $user = User::factory()->create();

        $uploadResponse = $this->actingAs($user)
            ->withHeader('Accept', 'application/json')
            ->post(route('profile.avatar.uploads.store'), [
                'avatar' => $this->createPngUpload(),
            ]);

        $uploadResponse->assertCreated()->assertJsonStructure(['id']);
        $uploadId = $uploadResponse->json('id');
        $temporaryPath = "{$uploadId}.png";
        Storage::disk('tmp')->assertExists($temporaryPath);
        $this->assertSame(
            $temporaryPath,
            session("profile_avatar_uploads.{$user->getKey()}.{$uploadId}"),
        );

        $updateResponse = $this->put(route('profile.update'), [
            'user_name' => $user->user_name,
            'user_email' => $user->user_email,
            'avatar_upload_id' => $uploadId,
        ]);

        $updateResponse->assertRedirect(route('profile.edit'));
        $avatarPath = "{$uploadId}.png";
        $this->assertDatabaseHas('users', [
            'user_id' => $user->user_id,
            'user_pfp' => $avatarPath,
        ]);
        Storage::disk('avatars')->assertExists($avatarPath);
        Storage::disk('tmp')->assertMissing($temporaryPath);
        $this->assertFalse(session()->has('profile_avatar_uploads'));

        $this->get(route('profile.avatar'))
            ->assertOk()
            ->assertHeader('content-type', 'image/png');
    }

    /**
     * The upload endpoint rejects an unsupported filename extension despite valid PNG content.
     */
    public function test_avatar_upload_rejects_an_unsupported_extension(): void
    {
        Storage::fake('avatars');
        Storage::fake('tmp');
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withHeader('Accept', 'application/json')
            ->post(route('profile.avatar.uploads.store'), [
                'avatar' => $this->createPngUpload('avatar.gif'),
            ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('avatar');
        $this->assertSame([], Storage::disk('avatars')->allFiles());
        $this->assertFalse(session()->has('profile_avatar_uploads'));
    }

    /**
     * The upload endpoint rejects an extension that does not match the image MIME type.
     */
    public function test_avatar_upload_rejects_an_extension_that_does_not_match_its_mime_type(): void
    {
        Storage::fake('avatars');
        Storage::fake('tmp');
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withHeader('Accept', 'application/json')
            ->post(route('profile.avatar.uploads.store'), [
                'avatar' => UploadedFile::fake()
                    ->createWithContent('avatar.jpg', $this->pngContents())
                    ->mimeType('image/png'),
            ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('avatar');
        $this->assertSame([], Storage::disk('avatars')->allFiles());
    }

    /**
     * The upload endpoint rejects files whose contents are not a supported image MIME type.
     */
    public function test_avatar_upload_rejects_an_unsupported_mime_type(): void
    {
        Storage::fake('avatars');
        Storage::fake('tmp');
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withHeader('Accept', 'application/json')
            ->post(route('profile.avatar.uploads.store'), [
                'avatar' => UploadedFile::fake()
                    ->createWithContent('avatar.png', 'not an image')
                    ->mimeType('text/plain'),
            ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('avatar');
        $this->assertSame([], Storage::disk('avatars')->allFiles());
    }

    /**
     * The upload endpoint rejects valid image content larger than 2 MB.
     */
    public function test_avatar_upload_rejects_files_larger_than_two_megabytes(): void
    {
        Storage::fake('avatars');
        Storage::fake('tmp');
        $user = User::factory()->create();
        $contents = $this->pngContents().str_repeat("\0", 2 * 1024 * 1024);

        $response = $this->actingAs($user)
            ->withHeader('Accept', 'application/json')
            ->post(route('profile.avatar.uploads.store'), [
                'avatar' => UploadedFile::fake()->createWithContent('large.png', $contents),
            ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('avatar');
        $this->assertSame([], Storage::disk('avatars')->allFiles());
        $this->assertFalse(session()->has('profile_avatar_uploads'));
    }

    /**
     * A mismatched upload ID cannot change the avatar and staged files are discarded on save.
     */
    public function test_profile_update_ignores_an_unmatched_avatar_upload_id(): void
    {
        Storage::fake('avatars');
        Storage::fake('tmp');
        $user = User::factory()->create(['user_pfp' => 'existing-avatar.png']);

        $uploadResponse = $this->actingAs($user)
            ->withHeader('Accept', 'application/json')
            ->post(route('profile.avatar.uploads.store'), [
                'avatar' => $this->createPngUpload(),
            ]);
        $uploadId = $uploadResponse->json('id');

        $response = $this->put(route('profile.update'), [
            'user_name' => $user->user_name,
            'user_email' => $user->user_email,
            'user_pfp' => 'avatars/attacker-controlled.png',
            'avatar_upload_id' => '00000000-0000-4000-8000-000000000000',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $this->assertDatabaseHas('users', [
            'user_id' => $user->user_id,
            'user_pfp' => 'existing-avatar.png',
        ]);
        Storage::disk('tmp')->assertMissing("{$uploadId}.png");
        $this->assertFalse(session()->has('profile_avatar_uploads'));
    }

    /**
     * A user can revert a staged upload without applying it to the profile.
     */
    public function test_user_can_revert_a_staged_avatar_upload(): void
    {
        Storage::fake('avatars');
        Storage::fake('tmp');
        $user = User::factory()->create();

        $uploadResponse = $this->actingAs($user)
            ->withHeader('Accept', 'application/json')
            ->post(route('profile.avatar.uploads.store'), [
                'avatar' => $this->createPngUpload(),
            ]);
        $uploadId = $uploadResponse->json('id');
        $temporaryPath = "{$uploadId}.png";

        $response = $this->call(
            'DELETE',
            route('profile.avatar.uploads.destroy'),
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'text/plain', 'HTTP_ACCEPT' => 'application/json'],
            $uploadId,
        );

        $response->assertOk()->assertExactJson([]);
        Storage::disk('tmp')->assertMissing($temporaryPath);
        $this->assertFalse(session()->has('profile_avatar_uploads'));
    }

    /**
     * A missing avatar path returns the bundled default image.
     */
    public function test_user_sees_the_default_avatar_when_no_avatar_is_stored(): void
    {
        Storage::fake('avatars');
        Storage::fake('tmp');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('profile.avatar'));

        $response->assertOk()->assertHeader('content-type', 'image/svg+xml');
    }

    /**
     * The profile form includes the FilePond endpoints and initializes its hidden ID to false.
     */
    public function test_profile_form_configures_filepond_and_uses_a_false_upload_id_by_default(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('profile.edit'));

        $response->assertOk()
            ->assertSee($user->user_name)
            ->assertSee(route('profile.avatar'), false)
            ->assertSee('value="false"', false)
            ->assertSee('data-upload-url="'.route('profile.avatar.uploads.store').'"', false)
            ->assertSee('data-revert-url="'.route('profile.avatar.uploads.destroy').'"', false);
    }

    /**
     * Profile validation errors produce a toast and leave a staged avatar available for retry.
     */
    public function test_profile_validation_errors_show_a_toast_and_preserve_the_staged_avatar(): void
    {
        Storage::fake('avatars');
        Storage::fake('tmp');
        $user = User::factory()->create();

        $uploadResponse = $this->actingAs($user)
            ->withHeader('Accept', 'application/json')
            ->post(route('profile.avatar.uploads.store'), [
                'avatar' => $this->createPngUpload(),
            ]);
        $uploadId = $uploadResponse->json('id');

        $updateResponse = $this->followingRedirects()
            ->from(route('profile.edit'))
            ->withHeader('Accept', 'text/html')
            ->put(route('profile.update'), [
                'user_name' => $user->user_name,
                'user_email' => 'not-an-email',
                'avatar_upload_id' => $uploadId,
            ]);

        $updateResponse->assertOk()
            ->assertSee('data-toast-type="danger"', false)
            ->assertSee('value="'.$uploadId.'"', false);
        Storage::disk('tmp')->assertExists("{$uploadId}.png");
    }

    /**
     * Create a fake PNG upload from valid image bytes without requiring the GD extension.
     *
     * @param  string  $filename  The client-supplied filename for the uploaded PNG.
     * @return UploadedFile The fake uploaded file with PNG content.
     */
    private function createPngUpload(string $filename = 'avatar.png'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($filename, $this->pngContents());
    }

    /**
     * Return valid one-pixel PNG bytes for upload tests.
     *
     * @return string The decoded PNG image content.
     */
    private function pngContents(): string
    {
        $contents = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/XioAAAAASUVORK5CYII=',
            true,
        );

        if (! is_string($contents)) {
            throw new \RuntimeException('The test PNG fixture could not be decoded.');
        }

        return $contents;
    }
}
