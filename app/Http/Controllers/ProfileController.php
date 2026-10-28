<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use League\Flysystem\FilesystemException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Displays and updates the authenticated user's own profile.
 */
class ProfileController extends Controller
{
    private const AVATAR_UPLOADS_SESSION_KEY = 'profile_avatar_uploads';

    private const MAX_FILE_SIZE = 2097152; // 2 MB in bytes

    /**
     * Display the authenticated user's profile form.
     *
     * @param  Request  $request  The authenticated browser request.
     * @return View The profile form populated with the current user.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    /**
     * Return the authenticated user's private avatar or the default avatar.
     *
     * @param  Request  $request  The authenticated browser request.
     * @return Response The avatar image response.
     */
    public function avatar(Request $request): Response
    {
        $user = $request->user();
        $avatarPath = $user->user_pfp;
        $avatarDisk = Storage::disk('avatars');

        if (
            is_string($avatarPath)
            && $this->isAvatarPathForUser($avatarPath, $user)
            && $avatarDisk->exists($avatarPath)
        ) {
            $stream = $avatarDisk->readStream($avatarPath);

            if (is_resource($stream)) {
                $mimeType = str_ends_with(strtolower($avatarPath), '.png')
                    ? 'image/png'
                    : 'image/jpeg';

                return response()->stream(function () use ($stream): void {
                    fpassthru($stream);
                    fclose($stream);
                }, 200, [
                    'Content-Type' => $mimeType,
                    'Cache-Control' => 'private, no-store',
                ]);
            }
        }

        return response()->file(public_path('images/default-avatar.svg'), [
            'Cache-Control' => 'private, no-store',
        ]);
    }

    /**
     * Validate and stage an avatar, returning its session-bound upload ID.
     *
     * @param  Request  $request  The authenticated multipart upload request.
     * @return JsonResponse The upload ID used by FilePond and the profile form.
     *
     * @throws ValidationException When the file is not a JPEG or PNG up to 2 MB.
     */
    public function storeAvatarUpload(Request $request): JsonResponse
    {

        /*
        Classical validation doesnt work with images with filepond??
        Interesting: This validation fails with a 422 error, but the file is still uploaded to the server. So we have to validate it manually.
        Why?, because image it's the first on fail, then continue...
        $validated = $request->validate([
                'avatar' => [
                    'required',
                    'image',
                    'mimetypes:image/jpeg,image/png',
                    'mimes:jpg,jpeg,png',
                    'extensions:jpg,jpeg,png',
                    'max:2048',
                ],
            $avatarFile = $validated['avatar'];
        ]); */

        $avatarFile = $request->file('avatar');

        if (! $avatarFile instanceof UploadedFile) {
            throw ValidationException::withMessages([
                'avatar' => 'The avatar file is not present in the request.',
            ]);
        }

        $sizeInBytes = $avatarFile->getSize();

        if (! is_int($sizeInBytes) || $sizeInBytes > static::MAX_FILE_SIZE) {
            throw ValidationException::withMessages([
                'avatar' => 'The avatar must be no larger than 2 MB.',
            ]);
        }

        // Check both the client extension and the detected MIME type manually.
        $clientExtension = strtolower((string) $avatarFile->getClientOriginalExtension());
        $detectedExtension = strtolower((string) $avatarFile->guessExtension());
        $mimeType = $avatarFile->getMimeType();
        $extensionsByMimeType = [
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png' => ['png'],
        ];

        if (! in_array($clientExtension, ['jpg', 'jpeg', 'png'], true)
            || ! in_array($detectedExtension, ['jpg', 'jpeg', 'png'], true)
            || $clientExtension !== $detectedExtension
            || ! in_array($clientExtension, $extensionsByMimeType[$mimeType] ?? [], true)) {
            throw ValidationException::withMessages([
                'avatar' => 'The avatar must be a JPEG or PNG image.',
            ]);
        }

        $extension = $detectedExtension;

        $user = $request->user();
        $uploadId = (string) Str::uuid();
        try {
            $temporaryPath = $avatarFile->storeAs(
                '',
                "{$uploadId}.{$extension}",
                'tmp',
            );
        } catch (FilesystemException $exception) {
            Log::error('Unable to stage a profile avatar.', [
                'user_id' => $user->getKey(),
                'exception' => $exception,
            ]);

            return response()->json([
                'message' => 'The avatar could not be saved. Please try again.',
            ], 500);
        }

        if ($temporaryPath === false) {
            Log::error('Unable to stage a profile avatar.', [
                'user_id' => $user->getKey(),
            ]);

            return response()->json([
                'message' => 'The avatar could not be saved. Please try again.',
            ], 500);
        }

        $uploads = $this->temporaryAvatarUploads($request, $user);
        $uploads[$uploadId] = $temporaryPath;
        $this->storeTemporaryAvatarUploads($request, $user, $uploads);

        return response()->json(['id' => $uploadId], 201);
    }

    /**
     * Revert a staged avatar requested by FilePond.
     *
     * @param  Request  $request  The authenticated request containing the raw upload ID.
     * @return JsonResponse A successful empty response or a JSON error response.
     */
    public function revertAvatarUpload(Request $request): JsonResponse
    {
        $uploadId = trim($request->getContent());
        $user = $request->user();
        $uploads = $this->temporaryAvatarUploads($request, $user);
        $temporaryPath = $uploads[$uploadId] ?? null;

        if (
            ! Str::isUuid($uploadId)
            || ! is_string($temporaryPath)
            || ! $this->isTemporaryAvatarPathForUser($temporaryPath, $user)
        ) {
            return response()->json(['message' => 'The temporary avatar was not found.'], 404);
        }

        $temporaryDisk = Storage::disk('tmp');

        try {
            $removed = ! $temporaryDisk->exists($temporaryPath) || $temporaryDisk->delete($temporaryPath);
        } catch (FilesystemException $exception) {
            Log::error('Unable to remove a temporary profile avatar.', [
                'user_id' => $user->getKey(),
                'upload_id' => $uploadId,
                'exception' => $exception,
            ]);

            return response()->json([
                'message' => 'The temporary avatar could not be removed. Please try again.',
            ], 500);
        }

        if (! $removed) {
            Log::error('Unable to remove a temporary profile avatar.', [
                'user_id' => $user->getKey(),
                'upload_id' => $uploadId,
            ]);

            return response()->json([
                'message' => 'The temporary avatar could not be removed. Please try again.',
            ], 500);
        }

        unset($uploads[$uploadId]);
        $this->storeTemporaryAvatarUploads($request, $user, $uploads);

        return response()->json([], 200);
    }

    /**
     * Validate and update the authenticated user's profile.
     *
     * @param  Request  $request  The profile form submission.
     * @return RedirectResponse A redirect to the updated profile form.
     *
     * @throws ValidationException When the submitted profile data is invalid.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'user_name' => ['required', 'string', 'max:30'],
            'user_email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'user_email')->ignore($user),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed', 'max:255'],
        ]);

        $temporaryUploads = $this->temporaryAvatarUploads($request, $user);
        $uploadId = $request->input('avatar_upload_id', 'false');
        $temporaryPath = is_string($uploadId) && $uploadId !== 'false'
            ? ($temporaryUploads[$uploadId] ?? null)
            : null;
        $avatarPath = null;

        if (is_string($temporaryPath) && $this->isTemporaryAvatarPathForUser($temporaryPath, $user)) {
            try {
                $temporaryDisk = Storage::disk('tmp');
                $avatarDisk = Storage::disk('avatars');

                if ($temporaryDisk->exists($temporaryPath)) {
                    $avatarPath = basename($temporaryPath);
                    $contents = $temporaryDisk->get($temporaryPath);

                    if (! is_string($contents) || ! $avatarDisk->put($avatarPath, $contents)) {
                        Log::error('Unable to save a staged profile avatar.', [
                            'user_id' => $user->getKey(),
                            'upload_id' => $uploadId,
                        ]);

                        return redirect()
                            ->route('profile.edit')
                            ->withErrors(['avatar' => 'The avatar could not be saved. Please try again.'])
                            ->withInput();
                    }
                } else {
                    $avatarPath = null;
                }
            } catch (FilesystemException $exception) {
                Log::error('Unable to save a staged profile avatar.', [
                    'user_id' => $user->getKey(),
                    'upload_id' => $uploadId,
                    'exception' => $exception,
                ]);

                return redirect()
                    ->route('profile.edit')
                    ->withErrors(['avatar' => 'The avatar could not be saved. Please try again.'])
                    ->withInput();
            }
        }

        $previousAvatarPath = $user->user_pfp;
        $user->fill([
            'user_name' => $validated['user_name'],
            'user_email' => $validated['user_email'],
        ]);

        if ($avatarPath !== null) {
            $user->user_pfp = $avatarPath;
        }

        if (filled($validated['password'] ?? null)) {
            $user->user_password = $validated['password'];
        }

        $user->save();

        $temporaryUploadsRemoved = $this->clearTemporaryAvatarUploads($request, $user, $temporaryUploads);
        $previousAvatarRemoved = $this->deletePreviousAvatar($previousAvatarPath, $avatarPath, $user);
        $response = redirect()->route('profile.edit')->with('status', 'Profile updated.');

        if (! $temporaryUploadsRemoved || ! $previousAvatarRemoved) {
            $response->with('warning', 'Your profile was updated, but an old image could not be removed.');
        }

        return $response;
    }

    /**
     * Read the current user's staged avatar paths from the session.
     *
     * @param  Request  $request  The current request and session.
     * @param  User  $user  The owner of the staged uploads.
     * @return array<string, string> Upload IDs mapped to paths on the avatars disk.
     */
    private function temporaryAvatarUploads(Request $request, User $user): array
    {
        $uploadsByUser = $request->session()->get(self::AVATAR_UPLOADS_SESSION_KEY, []);

        if (! is_array($uploadsByUser)) {
            return [];
        }

        $uploads = $uploadsByUser[$user->getKey()] ?? [];

        return is_array($uploads)
            ? array_filter($uploads, 'is_string')
            : [];
    }

    /**
     * Save the current user's staged avatar paths in the session.
     *
     * @param  Request  $request  The current request and session.
     * @param  User  $user  The owner of the staged uploads.
     * @param  array<string, string>  $uploads  Upload IDs mapped to temporary disk paths.
     */
    private function storeTemporaryAvatarUploads(Request $request, User $user, array $uploads): void
    {
        $uploadsByUser = $request->session()->get(self::AVATAR_UPLOADS_SESSION_KEY, []);
        $uploadsByUser = is_array($uploadsByUser) ? $uploadsByUser : [];

        if ($uploads === []) {
            unset($uploadsByUser[$user->getKey()]);
        } else {
            $uploadsByUser[$user->getKey()] = $uploads;
        }

        if ($uploadsByUser === []) {
            $request->session()->forget(self::AVATAR_UPLOADS_SESSION_KEY);

            return;
        }

        $request->session()->put(self::AVATAR_UPLOADS_SESSION_KEY, $uploadsByUser);
    }

    /**
     * Remove a user's temporary avatar files and their session references.
     *
     * Failed deletions are retained in the session for a later cleanup attempt.
     *
     * @param  Request  $request  The current request and session.
     * @param  User  $user  The owner of the staged uploads.
     * @param  array<string, string>  $uploads  Upload IDs mapped to temporary disk paths.
     * @return bool Whether every valid temporary file was removed.
     */
    private function clearTemporaryAvatarUploads(Request $request, User $user, array $uploads): bool
    {
        $temporaryDisk = Storage::disk('tmp');
        $failedUploads = [];

        foreach ($uploads as $uploadId => $path) {
            if (! $this->isTemporaryAvatarPathForUser($path, $user)) {
                continue;
            }

            try {
                $removed = ! $temporaryDisk->exists($path) || $temporaryDisk->delete($path);
            } catch (FilesystemException $exception) {
                Log::warning('A temporary profile avatar could not be removed.', [
                    'user_id' => $user->getKey(),
                    'upload_id' => $uploadId,
                    'exception' => $exception,
                ]);

                $failedUploads[$uploadId] = $path;

                continue;
            }

            if (! $removed) {
                Log::warning('A temporary profile avatar could not be removed.', [
                    'user_id' => $user->getKey(),
                    'upload_id' => $uploadId,
                ]);

                $failedUploads[$uploadId] = $path;
            }
        }

        $this->storeTemporaryAvatarUploads($request, $user, $failedUploads);

        return $failedUploads === [];
    }

    /**
     * Remove the previous avatar after a replacement has been saved.
     *
     * @param  mixed  $previousPath  The previous user_pfp attribute value.
     * @param  string|null  $newPath  The new avatar path, if one was saved.
     * @param  User  $user  The owner of the avatar paths.
     * @return bool Whether the previous avatar was removed or did not need removal.
     */
    private function deletePreviousAvatar(mixed $previousPath, ?string $newPath, User $user): bool
    {
        if (
            $newPath === null
            || ! is_string($previousPath)
            || ! $this->isAvatarPathForUser($previousPath, $user)
        ) {
            return true;
        }

        $avatarDisk = Storage::disk('avatars');

        try {
            return ! $avatarDisk->exists($previousPath) || $avatarDisk->delete($previousPath);
        } catch (FilesystemException $exception) {
            Log::warning('The previous profile avatar could not be removed.', [
                'user_id' => $user->getKey(),
                'exception' => $exception,
            ]);

            return false;
        }
    }

    /**
     * Check that a stored avatar path belongs to the given user and has a generated filename.
     *
     * @param  string  $path  The relative path from user_pfp.
     * @param  User  $user  The expected owner of the avatar.
     * @return bool Whether the path is a generated avatar path for the user.
     */
    private function isAvatarPathForUser(string $path, User $user): bool
    {
        return ! Str::contains($path, '/')
            && preg_match(
                '/\A[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\.(?:png|jpe?g)\z/i',
                $path,
            ) === 1;
    }

    /**
     * Check that a temporary path belongs to the given user and has a generated filename.
     *
     * @param  string  $path  The relative path in the temporary directory.
     * @param  User  $user  The expected owner of the temporary file.
     * @return bool Whether the path is a generated temporary avatar path for the user.
     */
    private function isTemporaryAvatarPathForUser(string $path, User $user): bool
    {
        return ! Str::contains($path, '/')
            && preg_match(
                '/\A[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\.(?:png|jpe?g)\z/i',
                $path,
            ) === 1;
    }
}
