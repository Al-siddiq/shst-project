<?php

namespace App\Services\Website;

use App\Models\Tenant\Website\MediaFileModel;
use CodeIgniter\HTTP\Files\UploadedFile;
use Config\WebsiteMedia;
use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Owns the complete lifecycle of tenant website media.
 *
 * Controllers never choose storage paths and models never manipulate files.
 * This boundary makes visibility checks, cleanup, derivatives, and audits easy
 * to inspect in one place and prevents a future CMS form from bypassing policy.
 */
class MediaService
{
    public function __construct(private readonly ?WebsiteMedia $config = null)
    {
    }

    /**
     * Validates, stores, catalogues, and audits one uploaded website image.
     *
     * @return array<string, mixed>
     */
    public function upload(UploadedFile $upload, string $category, string $visibility = 'private'): array
    {
        $config = $this->policy();
        $this->assertOption($category, $config->allowedCategories, 'Unsupported website media category.');
        $this->assertOption($visibility, $config->allowedVisibilities, 'Unsupported media visibility.');

        if (! $upload->isValid() || $upload->hasMoved()) {
            throw new InvalidArgumentException('The uploaded file is not valid.');
        }
        if ($upload->getSize() <= 0 || $upload->getSize() > $config->maxBytes) {
            throw new InvalidArgumentException('The uploaded image exceeds the permitted file size.');
        }

        // getMimeType() inspects the temporary file; client-provided MIME values
        // are intentionally ignored because request headers are not trustworthy.
        $mimeType = (string) $upload->getMimeType();
        $extension = $config->extensionsByMimeType[$mimeType] ?? null;
        if ($extension === null) {
            throw new InvalidArgumentException('Only JPEG, PNG, and WebP images are permitted.');
        }

        $dimensions = @getimagesize($upload->getTempName());
        if ($dimensions === false || ($dimensions['mime'] ?? null) !== $mimeType
            || $dimensions[0] > $config->maxWidth || $dimensions[1] > $config->maxHeight) {
            throw new InvalidArgumentException('The uploaded image dimensions are invalid or exceed the permitted limit.');
        }

        $context = service('tenantContextManager')->current();
        if (! $context->isResolved()) {
            throw new RuntimeException('Tenant context is required before storing tenant media.');
        }

        $directory = 'tenant/' . $context->tenantId . '/website/' . $category;
        $storedName = bin2hex(random_bytes(16)) . '.' . $extension;
        $relativePath = $directory . '/' . $storedName;
        $absoluteDirectory = $this->storageRoot() . '/' . $directory;
        $absolutePath = $this->storageRoot() . '/' . $relativePath;
        $derivatives = [];

        $this->ensureDirectory($absoluteDirectory);

        try {
            $upload->move($absoluteDirectory, $storedName);
            $derivatives = $this->createDerivatives($absolutePath, $directory, pathinfo($storedName, PATHINFO_FILENAME), $extension);

            $id = (new MediaFileModel())->insert([
                // The name is metadata only, but normalize Windows separators
                // before basename() so logs and CMS labels cannot retain a path.
                'original_name' => basename(str_replace('\\', '/', $upload->getClientName())),
                'stored_name' => $storedName,
                'storage_disk' => 'local',
                'storage_path' => $relativePath,
                'mime_type' => $mimeType,
                'extension' => $extension,
                'size_bytes' => filesize($absolutePath),
                'width' => $dimensions[0],
                'height' => $dimensions[1],
                'visibility' => $visibility,
                'category' => $category,
                'checksum_sha256' => hash_file('sha256', $absolutePath),
                'derivatives' => json_encode($derivatives, JSON_THROW_ON_ERROR),
            ], true);
        } catch (Throwable $exception) {
            $this->removePaths(array_merge([$relativePath], array_values($derivatives)));
            throw new RuntimeException('The image could not be stored safely.', 0, $exception);
        }

        service('auditLogger')->record('website.media.uploaded', [
            'target_type' => 'media_file',
            'target_id' => $id,
            'summary' => 'Website media uploaded.',
            'metadata' => ['category' => $category, 'visibility' => $visibility],
        ]);

        return $this->find((int) $id);
    }

    /** @return array<string, mixed> */
    public function find(int $id): array
    {
        $media = (new MediaFileModel())->find($id);
        if ($media === null) {
            throw new InvalidArgumentException('Media file was not found.');
        }

        return $media;
    }

    /**
     * Returns an authorized public derivative. Originals stay private to reduce
     * bandwidth and to avoid accidentally serving a needlessly large upload.
     *
     * @return array{media: array<string, mixed>, path: string}
     */
    public function publicDerivative(int $id, string $variant = 'medium'): array
    {
        $media = $this->find($id);
        if ($media['visibility'] !== 'public') {
            throw new InvalidArgumentException('Media file is not publicly available.');
        }

        return ['media' => $media, 'path' => $this->derivativePath($media, $variant)];
    }

    /** @return array{media: array<string, mixed>, path: string} */
    public function privateOriginal(int $id): array
    {
        $media = $this->find($id);

        return ['media' => $media, 'path' => $this->absolutePath($media['storage_path'])];
    }

    /** @return array<string, mixed> */
    public function changeVisibility(int $id, string $visibility): array
    {
        $this->assertOption($visibility, $this->policy()->allowedVisibilities, 'Unsupported media visibility.');
        $media = $this->find($id);
        (new MediaFileModel())->update($id, ['visibility' => $visibility]);

        service('auditLogger')->record('website.media.visibility_changed', [
            'target_type' => 'media_file',
            'target_id' => $id,
            'summary' => 'Website media visibility changed.',
            'metadata' => ['from' => $media['visibility'], 'to' => $visibility],
        ]);

        return $this->find($id);
    }

    public function delete(int $id): void
    {
        $media = $this->find($id);
        $derivatives = $this->decodeDerivatives($media);
        (new MediaFileModel())->delete($id);
        $this->removePaths(array_merge([$media['storage_path']], array_values($derivatives)));

        service('auditLogger')->record('website.media.deleted', [
            'target_type' => 'media_file',
            'target_id' => $id,
            'summary' => 'Website media deleted.',
            'metadata' => ['category' => $media['category'], 'visibility' => $media['visibility']],
        ]);
    }

    /** @return array<string, string> */
    private function createDerivatives(string $source, string $directory, string $baseName, string $extension): array
    {
        $derivatives = [];
        foreach ($this->policy()->derivatives as $variant => $size) {
            $relativePath = $directory . '/' . $baseName . '-' . $variant . '.' . $extension;
            $absolutePath = $this->storageRoot() . '/' . $relativePath;

            // CI4 delegates to the configured image handler. Failing closed is
            // deliberate: a media record is not created unless mobile-safe
            // derivatives are available for later public delivery.
            service('image')
                ->withFile($source)
                ->fit($size['width'], $size['height'], 'center')
                ->save($absolutePath, $this->policy()->quality);
            $derivatives[$variant] = $relativePath;
        }

        return $derivatives;
    }

    /** @param array<string, mixed> $media */
    private function derivativePath(array $media, string $variant): string
    {
        $derivatives = $this->decodeDerivatives($media);
        if (! isset($derivatives[$variant])) {
            throw new InvalidArgumentException('Requested media derivative is not available.');
        }

        return $this->absolutePath($derivatives[$variant]);
    }

    /** @param array<string, mixed> $media @return array<string, string> */
    private function decodeDerivatives(array $media): array
    {
        $derivatives = json_decode((string) ($media['derivatives'] ?? ''), true);

        return is_array($derivatives) ? $derivatives : [];
    }

    /**
     * Resolves generated relative keys beneath WRITEPATH and rejects traversal
     * defensively even though callers should only use server-generated paths.
     */
    private function absolutePath(string $relativePath): string
    {
        if ($relativePath === '' || str_contains($relativePath, '..') || str_starts_with($relativePath, '/')) {
            throw new RuntimeException('Unsafe media storage path detected.');
        }

        $path = $this->storageRoot() . '/' . $relativePath;
        if (! is_file($path)) {
            throw new InvalidArgumentException('Media file is unavailable.');
        }

        return $path;
    }

    /** @param list<string> $paths */
    private function removePaths(array $paths): void
    {
        foreach ($paths as $relativePath) {
            if ($relativePath === '' || str_contains($relativePath, '..') || str_starts_with($relativePath, '/')) {
                continue;
            }

            $path = $this->storageRoot() . '/' . $relativePath;
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    private function storageRoot(): string
    {
        return rtrim(WRITEPATH, '/\\') . '/uploads/website';
    }

    private function ensureDirectory(string $directory): void
    {
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new RuntimeException('Website media storage directory could not be created.');
        }
    }

    /** @param list<string> $allowed */
    private function assertOption(string $value, array $allowed, string $message): void
    {
        if (! in_array($value, $allowed, true)) {
            throw new InvalidArgumentException($message);
        }
    }

    private function policy(): WebsiteMedia
    {
        return $this->config ?? config(WebsiteMedia::class);
    }
}
