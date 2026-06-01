<?php

namespace App\Services\Website;

use App\Models\Tenant\Website\MediaFileModel;
use App\Models\Tenant\Website\WebsiteSettingsModel;
use InvalidArgumentException;

/** Owns validated tenant website settings writes and their public-cache invalidation. */
class WebsiteSettingsManagementService
{
    /** @return array<string, mixed> */
    public function formData(): array
    {
        return ['settings' => (new WebsiteSettingsModel())->first() ?? []];
    }

    /** @param array<string, mixed> $payload */
    public function save(array $payload): void
    {
        $this->authority('website.settings.manage');
        foreach (['map_embed_url', 'portal_url', 'application_info_url', 'facebook_url', 'instagram_url', 'x_url', 'youtube_url'] as $field) {
            $this->assertSafeUrl($payload[$field] ?? null, $field);
        }
        if (! empty($payload['hero_media_id'])) {
            $media = (new MediaFileModel())->find((int) $payload['hero_media_id']);
            if ($media === null) {
                throw new InvalidArgumentException('Hero media must belong to the current tenant.');
            }
            if ((int) ($payload['is_public_enabled'] ?? 0) === 1 && $media['visibility'] !== 'public') {
                throw new InvalidArgumentException('A launched website hero image must have public visibility.');
            }
            $payload['hero_media_id'] = (int) $payload['hero_media_id'];
        } else {
            $payload['hero_media_id'] = null;
        }
        $payload['is_public_enabled'] = (int) ($payload['is_public_enabled'] ?? 0);
        $model = new WebsiteSettingsModel();
        $existing = $model->first();
        $existing === null ? $model->insert($payload) : $model->update((int) $existing['id'], $payload);
        service('auditLogger')->record('website.settings.updated', ['target_type' => 'website_settings', 'target_id' => $existing['id'] ?? $model->getInsertID(), 'summary' => 'Public website settings updated.']);
        service('publicWebsiteCache')->forgetMany(['settings', 'menu']);
    }

    private function assertSafeUrl(mixed $url, string $field): void
    {
        if ($url === null || $url === '') {
            return;
        }
        if (filter_var($url, FILTER_VALIDATE_URL) === false || ! in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true)) {
            throw new InvalidArgumentException($field . ' must use an http or https URL.');
        }
    }

    private function authority(string $code): void
    {
        if (! service('tenantAccess')->hasAuthority(service('tenantContextManager')->current(), $code)) {
            throw new InvalidArgumentException('Required website settings authority is missing.');
        }
    }
}
