<?php

namespace App\Services\Website;

use App\Models\Tenant\Website\WebsiteMenuItemModel;

/**
 * Loads and resolves visible tenant navigation records for public rendering.
 */
class WebsiteMenuService
{
    /** @return list<array<string, mixed>> */
    public function publicItems(): array
    {
        return service('publicWebsiteCache')->remember('menu', function (): array {
            $records = (new WebsiteMenuItemModel())
                ->where('status', 'active')
                ->where('is_visible', 1)
                ->orderBy('sort_order', 'ASC')
                ->orderBy('id', 'ASC')
                ->findAll();

            $childrenByParent = [];
            foreach ($records as $record) {
                $childrenByParent[(int) ($record['parent_id'] ?? 0)][] = $record;
            }

            return $this->branch($childrenByParent);
        });
    }

    /**
     * Builds nested records recursively so future menu management may use one
     * level or several levels without changing public rendering services.
     *
     * @param array<int, list<array<string, mixed>>> $childrenByParent
     * @return list<array<string, mixed>>
     */
    private function branch(array $childrenByParent, int $parentId = 0): array
    {
        $items = [];
        foreach ($childrenByParent[$parentId] ?? [] as $record) {
            $record['href'] = $this->href($record);
            $record['children'] = $this->branch($childrenByParent, (int) $record['id']);
            $items[] = $record;
        }

        return $items;
    }

    /** @param array<string, mixed> $item */
    private function href(array $item): string
    {
        if ($item['link_type'] === 'external') {
            $url = filter_var($item['url'] ?? null, FILTER_VALIDATE_URL);

            return is_string($url) && in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true) ? $url : '#';
        }

        try {
            return service('publicWebsiteUrl')->route((string) $item['route_name']);
        } catch (\InvalidArgumentException) {
            // A malformed record should not break the entire public navigation.
            // The later menu manager will validate writes before they reach here.
            return '#';
        }
    }
}
