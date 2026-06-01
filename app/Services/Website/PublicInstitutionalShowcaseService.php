<?php

namespace App\Services\Website;

use App\Models\Tenant\Website\GalleryAlbumModel;
use App\Models\Tenant\Website\GalleryItemModel;
use App\Models\Tenant\Website\ManagementProfileModel;
use App\Models\Tenant\Website\MediaFileModel;
use InvalidArgumentException;

/** Resolves published leadership and gallery records for anonymous tenant visitors. */
class PublicInstitutionalShowcaseService
{
    /** @return list<array<string, mixed>> */
    public function management(): array
    {
        return service('publicWebsiteCache')->remember($this->key('management'), function (): array {
            return array_values(array_filter(
                (new ManagementProfileModel())->where('status', 'published')->orderBy('sort_order')->findAll(),
                fn (array $profile): bool => $this->publicMedia((int) $profile['photo_media_id']),
            ));
        });
    }

    /** @return array{albums: list<array<string, mixed>>, page: int, total: int, totalPages: int} */
    public function gallery(int $page = 1, int $perPage = 12): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(30, $perPage));

        return service('publicWebsiteCache')->remember($this->key("gallery.$page.$perPage"), function () use ($page, $perPage): array {
            // Filter before pagination so private covers do not create empty
            // public pages or links that the media controller will reject.
            $visible = array_values(array_filter(
                (new GalleryAlbumModel())->where('status', 'published')->orderBy('sort_order')->findAll(),
                fn (array $album): bool => $this->publicMedia((int) $album['cover_media_id']),
            ));
            $total = count($visible);

            return [
                'albums' => array_slice($visible, ($page - 1) * $perPage, $perPage),
                'page' => $page,
                'total' => $total,
                'totalPages' => max(1, (int) ceil($total / $perPage)),
            ];
        });
    }

    /** @return array{album: array<string, mixed>, items: list<array<string, mixed>>} */
    public function album(string $slug): array
    {
        return service('publicWebsiteCache')->remember($this->key('album.' . $slug), function () use ($slug): array {
            $album = (new GalleryAlbumModel())->where('slug', $slug)->where('status', 'published')->first();
            if ($album === null || ! $this->publicMedia((int) $album['cover_media_id'])) {
                throw new InvalidArgumentException('Published gallery album was not found.');
            }
            $items = array_values(array_filter(
                (new GalleryItemModel())->where('gallery_album_id', $album['id'])->where('status', 'published')->orderBy('sort_order')->findAll(),
                fn (array $item): bool => $this->publicMedia((int) $item['media_file_id']),
            ));

            return ['album' => $album, 'items' => $items];
        });
    }

    /** @return list<array<string, mixed>> */
    public function homepage(): array
    {
        return service('publicWebsiteCache')->remember($this->key('homepage'), function (): array {
            $visible = array_values(array_filter(
                (new GalleryAlbumModel())->where('status', 'published')->orderBy('sort_order')->findAll(),
                fn (array $album): bool => $this->publicMedia((int) $album['cover_media_id']),
            ));

            return array_slice($visible, 0, 3);
        });
    }

    /** Public pages must not render links that the media controller will reject. */
    private function publicMedia(int $id): bool
    {
        $media = (new MediaFileModel())->find($id);

        return $media !== null && $media['visibility'] === 'public';
    }

    /** Tenant-scoped generations invalidate every public showcase cache entry together. */
    private function key(string $suffix): string
    {
        $revision = service('publicWebsiteCache')->remember('institutional.revision', static fn (): int => 1, 86400);

        return 'institutional.v' . $revision . '.' . $suffix;
    }
}
