<?php

namespace App\Services\Website;

use App\Models\Tenant\Website\WebsiteMenuItemModel;
use InvalidArgumentException;

/** Owns public-menu writes, safe link validation, bounded nesting, auditing, and cache resets. */
class WebsiteMenuManagementService
{
    private const ROUTES = ['home', 'about', 'contact', 'departments', 'programmes', 'admissions', 'news', 'announcements', 'calendar', 'management', 'gallery'];

    /** @return array{items: list<array<string, mixed>>, selected: array<string, mixed>|null} */
    public function formData(?int $id = null): array
    {
        return ['items' => (new WebsiteMenuItemModel())->orderBy('sort_order')->orderBy('id')->findAll(), 'selected' => $id ? (new WebsiteMenuItemModel())->find($id) : null];
    }

    /** @param array<string, mixed> $payload */
    public function save(array $payload): int
    {
        $this->authority();
        $id = empty($payload['id']) ? null : (int) $payload['id'];
        $payload = $this->normalize($payload, $id);
        $model = new WebsiteMenuItemModel();
        if ($id === null) {
            $id = (int) $model->insert($payload, true);
            $action = 'website.menu.created';
        } else {
            if ($model->find($id) === null) {
                throw new InvalidArgumentException('Menu item was not found for this tenant.');
            }
            $model->update($id, $payload);
            $action = 'website.menu.updated';
        }
        $this->audit($action, $id);

        return $id;
    }

    /** @param array<int|string, int|string> $orderedIds */
    public function reorder(array $orderedIds): void
    {
        $this->authority();
        $model = new WebsiteMenuItemModel();
        if (count($orderedIds) !== count(array_unique(array_map('intval', $orderedIds)))) {
            throw new InvalidArgumentException('Menu reorder IDs must be unique.');
        }
        foreach (array_values($orderedIds) as $order => $id) {
            if ($model->find((int) $id) === null) {
                throw new InvalidArgumentException('Every reordered menu item must belong to this tenant.');
            }
            $model->update((int) $id, ['sort_order' => $order]);
        }
        service('auditLogger')->record('website.menu.reordered', ['target_type' => 'website_menu', 'summary' => 'Public website menu reordered.', 'metadata' => ['ordered_ids' => array_values($orderedIds)]]);
        $this->invalidate();
    }

    public function archive(int $id): void
    {
        $this->authority();
        if ((new WebsiteMenuItemModel())->find($id) === null) {
            throw new InvalidArgumentException('Menu item was not found for this tenant.');
        }
        (new WebsiteMenuItemModel())->update($id, ['status' => 'archived', 'is_visible' => 0]);
        $this->audit('website.menu.archived', $id);
    }

    /** @param array<string, mixed> $payload @return array<string, mixed> */
    private function normalize(array $payload, ?int $id): array
    {
        unset($payload['id']);
        $payload['parent_id'] = empty($payload['parent_id']) ? null : (int) $payload['parent_id'];
        $payload['sort_order'] = (int) ($payload['sort_order'] ?? 0);
        $payload['is_visible'] = (int) ($payload['is_visible'] ?? 0);
        if ($payload['parent_id'] !== null) {
            $parent = (new WebsiteMenuItemModel())->find($payload['parent_id']);
            $hasChildren = $id !== null && (new WebsiteMenuItemModel())->where('parent_id', $id)->first() !== null;
            if ($parent === null || (int) $payload['parent_id'] === $id || $parent['parent_id'] !== null || $hasChildren) {
                throw new InvalidArgumentException('Menu nesting is limited to one tenant-owned parent level.');
            }
        }
        if ($payload['link_type'] === 'route') {
            if (! in_array($payload['route_name'] ?? null, self::ROUTES, true)) {
                throw new InvalidArgumentException('Unsupported public website route.');
            }
            $payload['url'] = null;
        } elseif ($payload['link_type'] === 'external') {
            if (filter_var($payload['url'] ?? null, FILTER_VALIDATE_URL) === false || ! in_array(parse_url($payload['url'], PHP_URL_SCHEME), ['http', 'https'], true)) {
                throw new InvalidArgumentException('External menu links must use http or https.');
            }
            $payload['route_name'] = null;
        } else {
            throw new InvalidArgumentException('Unsupported menu link type.');
        }

        return $payload;
    }

    private function audit(string $action, int $id): void
    {
        service('auditLogger')->record($action, ['target_type' => 'website_menu_item', 'target_id' => $id, 'summary' => 'Public website menu changed.']);
        $this->invalidate();
    }

    private function invalidate(): void
    {
        service('publicWebsiteCache')->forget('menu');
    }

    private function authority(): void
    {
        if (! service('tenantAccess')->hasAuthority(service('tenantContextManager')->current(), 'website.menu.manage')) {
            throw new InvalidArgumentException('Required website menu authority is missing.');
        }
    }
}
