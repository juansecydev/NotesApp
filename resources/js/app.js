"use strict";

import { Toast } from 'bootstrap';
import * as FilePond from 'filepond';
import FilePondPluginImageCrop from 'filepond-plugin-image-crop';
import FilePondPluginImageExifOrientation from 'filepond-plugin-image-exif-orientation';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import FilePondPluginImageResize from 'filepond-plugin-image-resize';
import FilePondPluginImageTransform from 'filepond-plugin-image-transform';
import FilePondPluginImageValidateSize from 'filepond-plugin-image-validate-size';
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';

FilePond.registerPlugin(
    FilePondPluginImageCrop,
    FilePondPluginImageExifOrientation,
    FilePondPluginImagePreview,
    FilePondPluginImageResize,
    FilePondPluginImageTransform,
    FilePondPluginImageValidateSize,
    FilePondPluginFileValidateType
);

const noteColorSelect = document.querySelector('#note_color');
const noteColorPreviewTitle = document.querySelector('[data-note-color-preview-title]');
const noteColorLabel = document.querySelector('[data-note-color-label]');
const supportedNoteColors = ['primary', 'secondary', 'success', 'danger', 'warning', 'info'];

const toastElement = document.querySelector('#app-toast');
const toastTitle = toastElement?.querySelector('[data-toast-title]');
const toastMessage = toastElement?.querySelector('[data-toast-message]');

/**
 * Display an accessible Bootstrap toast using text-only content.
 *
 * @param {string} title The short toast heading.
 * @param {string} message The user-facing toast message.
 * @param {'success'|'danger'|'warning'|'info'} type The Bootstrap toast color.
 * @returns {void}
 */
const showToast = (title, message, type = 'success') => {
    if (!(toastElement instanceof HTMLElement) || !(toastTitle instanceof HTMLElement) || !(toastMessage instanceof HTMLElement)) {
        return;
    }

    toastElement.classList.remove('text-bg-success', 'text-bg-danger', 'text-bg-warning', 'text-bg-info');
    toastElement.classList.add(`text-bg-${type}`);
    toastTitle.textContent = title;
    toastMessage.textContent = message;
    Toast.getOrCreateInstance(toastElement, { autohide: true, delay: 4500 }).show();
};

/**
 * Extract a user-facing message from a FilePond or JSON server error.
 *
 * @param {unknown} error The error value supplied by FilePond.
 * @returns {string} The first server validation message or a safe fallback.
 */
const getUploadErrorMessage = (error) => {
    let responseText = '';

    if (typeof error === 'string') {
        responseText = error;
    } else if (error !== null && typeof error === 'object') {
        if ('body' in error && typeof error.body === 'string') {
            responseText = error.body;
        } else if ('main' in error && typeof error.main === 'string') {
            responseText = error.main;
        }
    }

    if (responseText === '') {
        return 'The avatar could not be uploaded. Please try again.';
    }

    try {
        const payload = JSON.parse(responseText);

        if (payload !== null && typeof payload === 'object') {
            if (typeof payload.message === 'string') {
                return payload.message;
            }

            if (payload.errors !== null && typeof payload.errors === 'object') {
                const firstError = Object.values(payload.errors).flat()[0];

                if (typeof firstError === 'string') {
                    return firstError;
                }
            }
        }
    } catch {
        return responseText;
    }

    return responseText;
};

document.querySelectorAll('[data-fallback-src]').forEach((image) => {
    if (!(image instanceof HTMLImageElement)) {
        return;
    }

    image.addEventListener('error', () => {
        const fallbackSource = image.dataset.fallbackSrc;

        if (fallbackSource && image.src !== new URL(fallbackSource, document.baseURI).href) {
            image.src = fallbackSource;
        }
    });
});

const initialToastMessage = toastElement instanceof HTMLElement
    ? toastElement.dataset.toastMessage
    : '';

if (initialToastMessage) {
    showToast(
        toastElement.dataset.toastTitle ?? 'Notice',
        initialToastMessage,
        toastElement.dataset.toastType ?? 'success',
    );
}

if (
    noteColorSelect instanceof HTMLSelectElement
    && noteColorPreviewTitle instanceof HTMLElement
    && noteColorLabel instanceof HTMLElement
) {
    /**
     * Synchronizes the preview styles and label with the selected supported color.
     *
     * @returns {void}
     */
    const updateNoteColorPreview = () => {
        const selectedColor = supportedNoteColors.includes(noteColorSelect.value)
            ? noteColorSelect.value
            : 'primary';

        for (const color of supportedNoteColors) {
            noteColorPreviewTitle.classList.remove(`bg-${color}`);
        }

        noteColorPreviewTitle.classList.remove('text-dark', 'text-white');
        noteColorPreviewTitle.classList.add(`bg-${selectedColor}`);
        noteColorPreviewTitle.classList.add(
            selectedColor === 'warning' || selectedColor === 'info' ? 'text-dark' : 'text-white',
        );
        noteColorLabel.textContent = `${selectedColor.charAt(0).toUpperCase()}${selectedColor.slice(1)} note`;
    };

    noteColorSelect.addEventListener('change', updateNoteColorPreview);
    updateNoteColorPreview();
}

const avatarInput = document.querySelector('#avatar');
const avatarUploadIdInput = document.querySelector('#avatar_upload_id');
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

if (
    avatarInput instanceof HTMLInputElement
    && avatarUploadIdInput instanceof HTMLInputElement
    && csrfToken
) {
    const pond = FilePond.create(avatarInput, {
        name: 'avatar',
        // stylePanelLayout: 'compact circle',
        // styleLoadIndicatorPosition: 'center bottom',
        // styleProgressIndicatorPosition: 'right bottom',
        // styleButtonRemoveItemPosition: 'left bottom',
        // styleButtonProcessItemPosition: 'right bottom',
        allowMultiple: false,
        allowImagePreview: true,
        allowImageCrop: true,
        imageCropAspectRatio: '1:1',
        imagePreviewHeight: 170,
        allowImageResize: true,
        imageResizeTargetWidth: 400,
        imageResizeTargetHeight: 400,
        allowImageExifOrientation: true,
        allowImageCrop: true,
        //imageTransformOutputMimeType: 'image/jpeg',
        imageTransformOutputQuality: 95,
        instantUpload: false,
        maxFiles: 1,
        acceptedFileTypes: ['image/jpeg', 'image/png'],
        maxFileSize: '2MB',
        beforeAddFile: (fileItem) => {
            const file = fileItem.file;
            const allowedMimeTypes = ['image/jpeg', 'image/png'];

            if (file.size > 2 * 1024 * 1024) {
                showToast('Avatar too large', 'Choose an image no larger than 2 MB.', 'danger');
                return false;
            }

            if (file.type !== '' && !allowedMimeTypes.includes(file.type)) {
                showToast('Unsupported image', 'Choose a JPEG or PNG image.', 'danger');

                return false;
            }

            return true;

        },
        server: {
            process: {
                url: avatarInput.dataset.uploadUrl,
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                onload: (response) => {
                    console.log(response);
                    try {
                        const payload = JSON.parse(response);

                        if (typeof payload.id !== 'string' || payload.id === '') {
                            showToast('Upload failed', 'The server did not return an avatar ID.', 'danger');

                            return '';
                        }

                        avatarUploadIdInput.value = payload.id;

                        return payload.id;
                    } catch {
                        showToast('Upload failed', 'The server returned an invalid avatar response.', 'danger');

                        return '';
                    }
                },
                onerror: (response) => console.error(response),
            },
            revert: {
                url: avatarInput.dataset.revertUrl,
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                onload: () => '',
                onerror: (response) => response,
            },
        },
    });

    const saveProfileButton = avatarInput.form?.querySelector('button[type="submit"]');

    pond.on('processfilestart', () => {
        if (saveProfileButton instanceof HTMLButtonElement) {
            saveProfileButton.disabled = true;
        }

        showToast('Uploading avatar', 'Wait for the upload to finish before saving your profile.', 'info');
    });

    pond.on('addfile', (error) => {

        if (error) {
            showToast('Error uploading the image by: ', error.main || 'Error uploading image.', 'danger');
            return;
        }

        const isInitialProfilePicture = profilePictureUrl
            && (file.source === profilePictureUrl || file.origin === FilePond.FileOrigin.LOCAL);

        if (isInitialProfilePicture) {
            return;
        }


        showToast(
            'Ready image',
            `Previous image and ready to upload.`,
            'success'
        );

    });

    pond.on('processfile', (error, file) => {
        if (saveProfileButton instanceof HTMLButtonElement) {
            saveProfileButton.disabled = false;
        }

        if (error) {
            avatarUploadIdInput.value = 'false';
            showToast('Upload failed', getUploadErrorMessage(error), 'danger');

            return;
        }

        if (typeof file.serverId !== 'string' || file.serverId === '') {
            avatarUploadIdInput.value = 'false';
            showToast('Upload failed', 'The server did not confirm the avatar upload.', 'danger');

            return;
        }

        avatarUploadIdInput.value = file.serverId;
        showToast('Avatar uploaded', 'Save your profile to apply the new picture.', 'success');
    });

    pond.on('removefile', (error, file) => {
        
        avatarUploadIdInput.value = 'false';

        

        if (error) {
            showToast('Could not remove avatar', getUploadErrorMessage(error), 'danger');
            return;
        }
    });
}
