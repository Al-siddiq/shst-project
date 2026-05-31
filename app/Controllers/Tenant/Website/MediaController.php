<?php

namespace App\Controllers\Tenant\Website;

use App\Controllers\BaseController;
use App\Traits\ApiResponseTrait;
use InvalidArgumentException;
use RuntimeException;

/**
 * Authenticated media-library endpoints for future CMS screens.
 *
 * This controller stays intentionally thin: MediaService owns all storage and
 * tenant-sensitive decisions, leaving request validation and response formatting
 * easy to debug at the HTTP boundary.
 */
class MediaController extends BaseController
{
    use ApiResponseTrait;

    public function upload()
    {
        $upload = $this->request->getFile('file');
        if ($upload === null) {
            return $this->fail('Validation failed.', ['file' => 'An image file is required.'], 422);
        }

        try {
            $media = service('websiteMedia')->upload(
                $upload,
                (string) $this->request->getPost('category'),
                (string) ($this->request->getPost('visibility') ?: 'private'),
            );
        } catch (InvalidArgumentException $exception) {
            return $this->fail('Validation failed.', ['file' => $exception->getMessage()], 422);
        } catch (RuntimeException $exception) {
            log_message('error', 'Website media upload failed: {message}', ['message' => $exception->getMessage()]);

            return $this->fail('The image could not be stored safely.', [], 500);
        }

        return $this->ok('Website media uploaded.', ['media' => $media], 201);
    }

    public function updateVisibility(int $id)
    {
        $payload = $this->request->getJSON(true) ?? [];
        if (! isset($payload['visibility'])) {
            return $this->fail('Validation failed.', ['visibility' => 'Visibility is required.'], 422);
        }

        try {
            $media = service('websiteMedia')->changeVisibility($id, (string) $payload['visibility']);
        } catch (InvalidArgumentException $exception) {
            return $this->fail('Validation failed.', ['visibility' => $exception->getMessage()], 422);
        }

        return $this->ok('Website media visibility updated.', ['media' => $media]);
    }

    public function delete(int $id)
    {
        try {
            service('websiteMedia')->delete($id);
        } catch (InvalidArgumentException $exception) {
            return $this->fail('Media file was not found.', ['media' => $exception->getMessage()], 404);
        }

        return $this->ok('Website media deleted.');
    }

    public function showPrivate(int $id)
    {
        try {
            $file = service('websiteMedia')->privateOriginal($id);
        } catch (InvalidArgumentException | RuntimeException) {
            return $this->fail('Media file was not found.', [], 404);
        }

        // Private assets must not enter shared browser or proxy caches.
        return $this->response
            ->setHeader('Cache-Control', 'private, no-store')
            ->setContentType($file['media']['mime_type'])
            ->setBody(file_get_contents($file['path']));
    }
}
