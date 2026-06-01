<?php

namespace App\Services\Website;

use App\Models\Tenant\Website\WebsiteContentItemModel;
use CodeIgniter\I18n\Time;
use InvalidArgumentException;

/**
 * Resolves public editorial content with publication-time enforcement.
 *
 * Scheduled records become readable when their due time arrives without a cron
 * dependency. A later worker may persist the state transition for reporting,
 * but anonymous delivery remains correct even if that worker is delayed.
 */
class PublicEditorialService
{
    /** @var array<string, array{type: string, listingRoute: string, detailRoute: string, label: string}> */
    private const SECTIONS = [
        'news' => ['type' => 'news', 'listingRoute' => 'news', 'detailRoute' => 'news_item', 'label' => 'News'],
        'announcements' => ['type' => 'announcement', 'listingRoute' => 'announcements', 'detailRoute' => 'announcement', 'label' => 'Announcements'],
        'calendar' => ['type' => 'calendar_notice', 'listingRoute' => 'calendar', 'detailRoute' => 'calendar_notice', 'label' => 'Calendar notices'],
    ];

    /** @return array{items: list<array<string, mixed>>, page: int, perPage: int, total: int, totalPages: int, section: array<string, string>} */
    public function listing(string $section, int $page = 1, int $perPage = 10): array
    {
        $definition = $this->section($section);
        $page = max(1, $page);
        $perPage = max(1, min(30, $perPage));
        $cacheKey = $this->cachePrefix() . '.' . $section . '.page-' . $page . '.per-' . $perPage;

        return service('publicWebsiteCache')->remember($cacheKey, function () use ($definition, $page, $perPage): array {
            $total = $this->publicQuery($definition['type'])->countAllResults();
            $records = $this->publicQuery($definition['type'])
                ->orderBy('COALESCE(published_at, scheduled_for, event_start_at, created_at)', 'DESC', false)
                ->findAll($perPage, ($page - 1) * $perPage);

            return [
                'items' => array_map(fn (array $record): array => $this->viewModel($record, $definition), $records),
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'totalPages' => max(1, (int) ceil($total / $perPage)),
                'section' => $definition,
            ];
        }, $this->publicCacheTtl());
    }

    /** @return array<string, mixed> */
    public function item(string $section, string $slug): array
    {
        $definition = $this->section($section);

        return service('publicWebsiteCache')->remember($this->cachePrefix() . '.' . $section . '.item.' . $slug, function () use ($definition, $slug): array {
            $record = $this->publicQuery($definition['type'])->where('slug', $slug)->first();
            if ($record === null) {
                throw new InvalidArgumentException('Published editorial content was not found.');
            }

            return $this->viewModel($record, $definition);
        }, $this->publicCacheTtl());
    }

    /** @return array{news: list<array<string, mixed>>, announcements: list<array<string, mixed>>, calendar: list<array<string, mixed>>} */
    public function homepage(): array
    {
        return service('publicWebsiteCache')->remember($this->cachePrefix() . '.homepage', function (): array {
            return [
                'news' => $this->homepageItems('news', 3),
                'announcements' => $this->homepageItems('announcements', 3),
                'calendar' => $this->homepageItems('calendar', 3),
            ];
        }, $this->publicCacheTtl());
    }

    /** @return list<array<string, mixed>> */
    private function homepageItems(string $section, int $limit): array
    {
        $definition = $this->section($section);
        $records = $this->publicQuery($definition['type'])
            ->orderBy('COALESCE(published_at, scheduled_for, event_start_at, created_at)', 'DESC', false)
            ->findAll($limit);

        return array_map(fn (array $record): array => $this->viewModel($record, $definition), $records);
    }

    private function publicQuery(string $type): WebsiteContentItemModel
    {
        $now = Time::now()->toDateTimeString();
        $model = (new WebsiteContentItemModel())
            ->where('content_type', $type)
            ->groupStart()
                ->where('status', 'published')
                ->orGroupStart()
                    ->where('status', 'scheduled')
                    ->where('scheduled_for <=', $now)
                ->groupEnd()
            ->groupEnd();

        if ($type === 'announcement') {
            $model->where('audience', 'public')
                ->groupStart()
                    ->where('event_start_at', null)
                    ->orWhere('event_start_at <=', $now)
                ->groupEnd()
                ->groupStart()
                    ->where('event_end_at', null)
                    ->orWhere('event_end_at >=', $now)
                ->groupEnd();
        }
        if ($type === 'calendar_notice') {
            $model->where('visibility', 'public');
        }

        return $model;
    }

    /** @param array<string, mixed> $record @param array<string, string> $definition @return array<string, mixed> */
    private function viewModel(array $record, array $definition): array
    {
        $record['href'] = service('publicWebsiteUrl')->route($definition['detailRoute'], [$record['slug']]);
        $record['section_label'] = $definition['label'];
        $record['effective_published_at'] = $record['published_at'] ?? $record['scheduled_for'];

        return $record;
    }

    /**
     * Prevents a cached listing, detail, or homepage from hiding a scheduled item after
     * its due time. The normal cache window is shortened to the nearest future
     * publication boundary, so request-time scheduling remains cron-free.
     */
    private function publicCacheTtl(): int
    {
        $now = Time::now()->toDateTimeString();
        $boundaries = [];
        foreach ([
            ['scheduled_for', 'status', 'scheduled'],
            ['event_start_at', 'content_type', 'announcement'],
            ['event_end_at', 'content_type', 'announcement'],
        ] as [$field, $constraint, $value]) {
            $next = (new WebsiteContentItemModel())
                ->selectMin($field, 'boundary')
                ->where($constraint, $value)
                ->where($field . ' >', $now)
                ->first();
            if (! empty($next['boundary'])) {
                $boundaries[] = strtotime($next['boundary']);
            }
        }
        if ($boundaries === []) {
            return 300;
        }

        return max(1, min(300, min($boundaries) - time()));
    }

    /** Cache generations invalidate all pages without handler-specific tags. */
    private function cachePrefix(): string
    {
        $revision = service('publicWebsiteCache')->remember('editorial.revision', static fn (): int => 1, 86400);

        return 'editorial.v' . $revision;
    }

    /** @return array{type: string, listingRoute: string, detailRoute: string, label: string} */
    private function section(string $section): array
    {
        if (! isset(self::SECTIONS[$section])) {
            throw new InvalidArgumentException('Unsupported editorial section.');
        }

        return self::SECTIONS[$section];
    }
}
