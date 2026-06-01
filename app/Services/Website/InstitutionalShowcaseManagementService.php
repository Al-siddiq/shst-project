<?php

namespace App\Services\Website;

use App\Models\Tenant\Website\GalleryAlbumModel;
use App\Models\Tenant\Website\GalleryItemModel;
use App\Models\Tenant\Website\ManagementProfileModel;
use App\Models\Tenant\Website\MediaFileModel;
use CodeIgniter\I18n\Time;
use InvalidArgumentException;

/** Owns Phase 4 CMS mutations so tenant, media, status, audit, and ordering policy cannot be bypassed. */
class InstitutionalShowcaseManagementService
{
    /** @return array<string, mixed> */
    public function managementData(?int $id = null): array
    {
        return ['profiles' => (new ManagementProfileModel())->orderBy('sort_order')->findAll(), 'selected' => $id ? (new ManagementProfileModel())->find($id) : null, 'media' => $this->media('management_photo')];
    }

    /** @return array<string, mixed> */
    public function galleryData(?int $id = null): array
    {
        $album = $id ? (new GalleryAlbumModel())->find($id) : null;

        return ['albums' => (new GalleryAlbumModel())->orderBy('sort_order')->findAll(), 'selected' => $album, 'items' => $album ? (new GalleryItemModel())->where('gallery_album_id', $id)->orderBy('sort_order')->findAll() : [], 'media' => $this->media('gallery_image')];
    }

    /** @param array<string, mixed> $data */
    public function saveManagement(array $data): int
    {
        $this->authority('website.content.edit');
        $data = $this->normalize($data);
        $this->mediaId($data['photo_media_id'], $data['status'] === 'published');

        return $this->save(new ManagementProfileModel(), $data, 'website.management_profile', isset($data['id']) ? (int) $data['id'] : null);
    }

    public function archiveManagement(int $id): void
    {
        $this->authority('website.content.archive');
        $this->archive(new ManagementProfileModel(), $id, 'website.management_profile.archived', 'management_profile');
    }

    /** @param array<string, mixed> $data */
    public function saveAlbum(array $data): int
    {
        $this->authority('website.media.manage');
        $data = $this->normalize($data);
        $this->mediaId($data['cover_media_id'], $data['status'] === 'published');

        return $this->save(new GalleryAlbumModel(), $data, 'website.gallery_album', isset($data['id']) ? (int) $data['id'] : null);
    }

    public function archiveAlbum(int $id): void
    {
        $this->authority('website.media.manage');
        $this->archive(new GalleryAlbumModel(), $id, 'website.gallery_album.archived', 'gallery_album');
    }

    /** @param array<string, mixed> $data */
    public function addItem(array $data): int
    {
        $this->authority('website.media.manage');
        $album = $this->album((int) $data['gallery_album_id']);
        $this->mediaId((int) $data['media_file_id'], $album['status'] === 'published' || $data['status'] === 'published');
        $id = (int) (new GalleryItemModel())->insert($data, true);
        $this->audit('website.gallery_item.added', 'gallery_item', $id, ['gallery_album_id' => (int) $data['gallery_album_id'], 'media_file_id' => (int) $data['media_file_id']]);
        $this->invalidate();

        return $id;
    }

    /** @param array<int|string, int|string> $orderedIds */
    public function reorderItems(int $albumId, array $orderedIds): void
    {
        $this->authority('website.media.manage');
        $this->album($albumId);
        $model = new GalleryItemModel();
        foreach (array_values($orderedIds) as $order => $id) {
            $item = $model->where('gallery_album_id', $albumId)->find((int) $id);
            if ($item === null) {
                throw new InvalidArgumentException('Gallery item must belong to the selected tenant album.');
            }
            $model->update((int) $id, ['sort_order' => $order]);
        }
        $this->audit('website.gallery_items.reordered', 'gallery_album', $albumId, ['ordered_item_ids' => array_values($orderedIds)]);
        $this->invalidate();
    }

    public function removeItem(int $id): void
    {
        $this->authority('website.media.manage');
        $item = (new GalleryItemModel())->find($id);
        if ($item === null) {
            throw new InvalidArgumentException('Gallery item was not found.');
        }
        (new GalleryItemModel())->delete($id);
        $this->audit('website.gallery_item.removed', 'gallery_item', $id, ['gallery_album_id' => (int) $item['gallery_album_id'], 'media_file_id' => (int) $item['media_file_id']]);
        $this->invalidate();
    }

    /** @return list<array<string, mixed>> */
    private function media(string $category): array
    {
        return (new MediaFileModel())->where('category', $category)->orderBy('created_at', 'DESC')->findAll();
    }

    /** Normalizes trusted lifecycle metadata rather than accepting actor IDs from forms. @param array<string, mixed> $data @return array<string, mixed> */
    private function normalize(array $data): array
    {
        foreach (['id', 'photo_media_id', 'cover_media_id', 'sort_order'] as $field) {
            if (isset($data[$field])) {
                $data[$field] = (int) $data[$field];
            }
        }
        if (($data['status'] ?? 'draft') === 'published') {
            $data['published_at'] = Time::now()->toDateTimeString();
            $data['published_by'] = service('tenantAccess')->currentUserId();
        }
        if (($data['status'] ?? '') === 'archived') {
            $data['archived_at'] = Time::now()->toDateTimeString();
        }

        return $data;
    }

    /** Tenant-scoped lookup prevents ID guessing; published records require anonymous-safe media. */
    private function mediaId(int $id, bool $public): void
    {
        $media = (new MediaFileModel())->find($id);
        if ($media === null) {
            throw new InvalidArgumentException('Selected media must belong to this tenant.');
        }
        if ($public && $media['visibility'] !== 'public') {
            throw new InvalidArgumentException('Published showcase records require public media.');
        }
    }

    /** @param ManagementProfileModel|GalleryAlbumModel $model @param array<string, mixed> $data */
    private function save($model, array $data, string $prefix, ?int $id): int
    {
        unset($data['id']);
        $existing = $id === null ? null : $model->find($id);
        if ($id === null) {
            $id = (int) $model->insert($data, true);
            $event = $prefix . '.created';
        } else {
            if ($existing === null) {
                throw new InvalidArgumentException('Showcase record was not found.');
            }
            $model->update($id, $data);
            $event = $prefix . '.updated';
        }
        $this->audit($event, $prefix, $id, ['from_status' => $existing['status'] ?? null, 'to_status' => $data['status'] ?? null]);
        $this->invalidate();

        return $id;
    }

    /** @param ManagementProfileModel|GalleryAlbumModel $model */
    private function archive($model, int $id, string $event, string $targetType): void
    {
        $existing = $model->find($id);
        if ($existing === null) {
            throw new InvalidArgumentException('Showcase record was not found.');
        }
        $model->update($id, ['status' => 'archived', 'archived_at' => Time::now()->toDateTimeString()]);
        $this->audit($event, $targetType, $id, ['from_status' => $existing['status'], 'to_status' => 'archived']);
        $this->invalidate();
    }

    /** @return array<string, mixed> */
    private function album(int $id): array
    {
        $album = (new GalleryAlbumModel())->find($id);
        if ($album === null) {
            throw new InvalidArgumentException('Gallery album was not found.');
        }

        return $album;
    }

    private function authority(string $code): void
    {
        if (! service('tenantAccess')->hasAuthority(service('tenantContextManager')->current(), $code)) {
            throw new InvalidArgumentException('Required website authority is missing.');
        }
    }

    /** @param array<string, mixed> $metadata */
    private function audit(string $event, string $type, int $id, array $metadata = []): void
    {
        service('auditLogger')->record($event, ['target_type' => $type, 'target_id' => $id, 'summary' => 'Institutional showcase changed.', 'metadata' => $metadata]);
    }

    private function invalidate(): void
    {
        service('publicWebsiteCache')->bump('institutional.revision');
    }
}
