<?php

namespace App\Services\Website;

use App\Models\Tenant\AcademicSessionModel;
use App\Models\Tenant\DepartmentModel;
use App\Models\Tenant\ProgrammeModel;
use App\Models\Tenant\SemesterModel;
use App\Models\Tenant\Website\MediaFileModel;
use App\Models\Tenant\Website\WebsiteContentItemModel;
use CodeIgniter\I18n\Time;
use InvalidArgumentException;

/**
 * Owns editorial draft mutations and explicit lifecycle commands.
 *
 * Editors can create and revise drafts. Publishers alone can schedule or
 * publish. Archive and delete remain separately authorized. Keeping commands
 * explicit prevents a browser from escalating privilege by posting a status.
 */
class EditorialManagementService
{
    /** @var list<string> */
    public const TYPES = ['news', 'announcement', 'calendar_notice'];

    /** @var array<string, list<string>> */
    private const TRANSITIONS = [
        'draft' => ['scheduled', 'published'],
        'scheduled' => ['draft', 'published', 'archived'],
        'published' => ['draft', 'archived'],
        'archived' => ['draft', 'published'],
    ];

    /** @return array{items: list<array<string, mixed>>, selectedItem: array<string, mixed>|null, departments: list<array<string, mixed>>, programmes: list<array<string, mixed>>, sessions: list<array<string, mixed>>, semesters: list<array<string, mixed>>} */
    public function formData(string $type, ?int $itemId = null): array
    {
        $this->assertType($type);
        $model = new WebsiteContentItemModel();

        return [
            'items' => $model->where('content_type', $type)->orderBy('updated_at', 'DESC')->findAll(),
            'selectedItem' => $itemId === null ? null : $model->where('content_type', $type)->find($itemId),
            'departments' => (new DepartmentModel())->orderBy('name')->findAll(),
            'programmes' => (new ProgrammeModel())->orderBy('name')->findAll(),
            'sessions' => (new AcademicSessionModel())->orderBy('name', 'DESC')->findAll(),
            'semesters' => (new SemesterModel())->orderBy('name')->findAll(),
        ];
    }

    /** @param array<string, mixed> $payload */
    public function saveDraft(string $type, array $payload, ?int $id = null): int
    {
        $this->assertType($type);
        $payload['content_type'] = $type;
        $payload['status'] = 'draft';
        $payload['scheduled_for'] = null;
        $payload['published_at'] = null;
        $payload['archived_at'] = null;
        $payload['published_by'] = null;
        $this->normalizeEmptyValues($payload);
        $this->assertRequiredFields($type, $payload);
        $this->assertRelations($payload);
        $this->assertMedia($payload['featured_media_id'] ?? null, false);
        $this->assertDates($payload);

        $model = new WebsiteContentItemModel();
        $existing = $id === null ? null : $this->item($id, $type);
        $this->assertUniqueSlug($model, $type, (string) $payload['slug'], $id);
        if ($existing === null) {
            $this->assertAuthority('website.content.create', 'Creating editorial content requires website.content.create authority.');
            $id = (int) $model->insert($payload, true);
            $this->audit('website.content.created', $id, $payload);
        } else {
            $this->assertAuthority('website.content.edit', 'Editing editorial content requires website.content.edit authority.');
            if ($existing['status'] !== 'draft') {
                throw new InvalidArgumentException('Move editorial content to draft before editing it.');
            }
            $model->update($id, $payload);
            $this->audit('website.content.updated', $id, $payload);
        }

        $this->invalidate($type, $existing['slug'] ?? null, $payload['slug']);

        return $id;
    }

    public function moveToDraft(int $id, string $type): void
    {
        $this->assertAuthority('website.content.edit', 'Moving editorial content to draft requires website.content.edit authority.');
        $item = $this->item($id, $type);
        $this->assertTransition($item['status'], 'draft');
        $this->transition($item, ['status' => 'draft', 'scheduled_for' => null, 'published_at' => null, 'archived_at' => null, 'published_by' => null], 'website.content.draft');
    }

    public function schedule(int $id, string $type, string $scheduledFor): void
    {
        $this->assertAuthority('website.content.publish', 'Scheduling editorial content requires website.content.publish authority.');
        $item = $this->item($id, $type);
        if (strtotime($scheduledFor) === false || strtotime($scheduledFor) <= time()) {
            throw new InvalidArgumentException('Scheduled publication time must be in the future.');
        }
        $this->assertTransition($item['status'], 'scheduled');
        $scheduledFor = date('Y-m-d H:i:s', strtotime($scheduledFor));
        $this->assertMedia($item['featured_media_id'] ?? null, true);
        $this->transition($item, ['status' => 'scheduled', 'scheduled_for' => $scheduledFor, 'published_at' => null, 'archived_at' => null, 'published_by' => service('tenantAccess')->currentUserId()], 'website.content.scheduled');
    }

    public function publish(int $id, string $type): void
    {
        $this->assertAuthority('website.content.publish', 'Publishing editorial content requires website.content.publish authority.');
        $item = $this->item($id, $type);
        $this->assertTransition($item['status'], 'published');
        $this->assertMedia($item['featured_media_id'] ?? null, true);
        $this->transition($item, ['status' => 'published', 'scheduled_for' => null, 'published_at' => Time::now()->toDateTimeString(), 'archived_at' => null, 'published_by' => service('tenantAccess')->currentUserId()], 'website.content.published');
    }

    public function archive(int $id, string $type): void
    {
        $this->assertAuthority('website.content.archive', 'Archiving editorial content requires website.content.archive authority.');
        $item = $this->item($id, $type);
        $this->assertTransition($item['status'], 'archived');
        $this->transition($item, ['status' => 'archived', 'scheduled_for' => null, 'archived_at' => Time::now()->toDateTimeString()], 'website.content.archived');
    }

    public function delete(int $id, string $type): void
    {
        $this->assertAuthority('website.content.delete', 'Deleting editorial content requires website.content.delete authority.');
        $item = $this->item($id, $type);
        if ($item['status'] !== 'draft') {
            throw new InvalidArgumentException('Only draft editorial content may be deleted.');
        }
        (new WebsiteContentItemModel())->delete($id);
        $this->audit('website.content.deleted', $id, $item);
        $this->invalidate($type, $item['slug']);
    }

    private function assertTransition(string $from, string $to): void
    {
        if (! in_array($to, self::TRANSITIONS[$from] ?? [], true)) {
            throw new InvalidArgumentException('Invalid editorial lifecycle transition.');
        }
    }

    /** @param array<string, mixed> $item @param array<string, mixed> $changes */
    private function transition(array $item, array $changes, string $event): void
    {
        (new WebsiteContentItemModel())->update((int) $item['id'], $changes);
        $this->audit($event, (int) $item['id'], array_merge($item, $changes), ['from' => $item['status'], 'to' => $changes['status']]);
        $this->invalidate($item['content_type'], $item['slug']);
    }

    /** @return array<string, mixed> */
    private function item(int $id, string $type): array
    {
        $this->assertType($type);
        $item = (new WebsiteContentItemModel())->where('content_type', $type)->find($id);
        if ($item === null) {
            throw new InvalidArgumentException('Editorial content was not found for this tenant.');
        }

        return $item;
    }

    /**
     * Enforces content-type invariants inside the service boundary. Controller
     * validation improves form feedback, but future API or CLI callers must
     * not be able to persist incomplete editorial drafts by skipping HTTP.
     *
     * @param array<string, mixed> $payload
     */
    private function assertRequiredFields(string $type, array $payload): void
    {
        $required = match ($type) {
            'news' => ['summary', 'featured_media_id', 'category', 'author_display_name'],
            'announcement' => ['announcement_type', 'priority', 'audience', 'event_start_at', 'event_end_at'],
            'calendar_notice' => ['event_start_at', 'event_end_at', 'visibility'],
        };
        foreach (array_merge(['title', 'slug', 'body'], $required) as $field) {
            if (($payload[$field] ?? null) === null || trim((string) $payload[$field]) === '') {
                throw new InvalidArgumentException($field . ' is required for ' . $type . ' content.');
            }
        }

        foreach ([
            'priority' => ['low', 'normal', 'high', 'urgent'],
            'audience' => ['public', 'applicants', 'students', 'staff', 'department', 'programme'],
            'visibility' => ['public', 'private'],
        ] as $field => $allowed) {
            if (isset($payload[$field]) && ! in_array($payload[$field], $allowed, true)) {
                throw new InvalidArgumentException($field . ' contains an unsupported value.');
            }
        }
        if (! preg_match('/^[a-z0-9-]+$/', (string) $payload['slug'])) {
            throw new InvalidArgumentException('Slug may contain lowercase letters, numbers, and hyphens only.');
        }

        if (($payload['audience'] ?? null) === 'department' && empty($payload['related_department_id'])) {
            throw new InvalidArgumentException('Department announcements require a related department.');
        }
        if (($payload['audience'] ?? null) === 'programme' && empty($payload['related_programme_id'])) {
            throw new InvalidArgumentException('Programme announcements require a related programme.');
        }
    }

    /** @param array<string, mixed> $payload */
    private function assertRelations(array $payload): void
    {
        foreach ([
            'related_department_id' => DepartmentModel::class,
            'related_programme_id' => ProgrammeModel::class,
            'academic_session_id' => AcademicSessionModel::class,
            'semester_id' => SemesterModel::class,
        ] as $field => $modelClass) {
            if (($payload[$field] ?? null) !== null && (new $modelClass())->find((int) $payload[$field]) === null) {
                throw new InvalidArgumentException('Related records must belong to the current tenant.');
            }
        }

        if (($payload['related_department_id'] ?? null) !== null && ($payload['related_programme_id'] ?? null) !== null) {
            $programme = (new ProgrammeModel())->find((int) $payload['related_programme_id']);
            if ($programme === null || (int) $programme['department_id'] !== (int) $payload['related_department_id']) {
                throw new InvalidArgumentException('Related programme must belong to the selected department.');
            }
        }
    }

    private function assertMedia(mixed $mediaId, bool $requirePublic): void
    {
        if ($mediaId === null || $mediaId === '') {
            return;
        }
        $media = (new MediaFileModel())->find((int) $mediaId);
        if ($media === null) {
            throw new InvalidArgumentException('Featured media must belong to the current tenant.');
        }
        if ($requirePublic && $media['visibility'] !== 'public') {
            throw new InvalidArgumentException('Published editorial media must use public visibility.');
        }
    }

    /** @param array<string, mixed> $payload */
    private function assertDates(array $payload): void
    {
        if (! empty($payload['event_start_at']) && ! empty($payload['event_end_at']) && strtotime($payload['event_end_at']) < strtotime($payload['event_start_at'])) {
            throw new InvalidArgumentException('End date must not occur before start date.');
        }
    }

    private function assertUniqueSlug(WebsiteContentItemModel $model, string $type, string $slug, ?int $id): void
    {
        $match = $model->where('content_type', $type)->where('slug', $slug)->first();
        if ($match !== null && (int) $match['id'] !== (int) $id) {
            throw new InvalidArgumentException('Slug is already in use for this editorial section.');
        }
    }

    /** @param array<string, mixed> $payload */
    private function normalizeEmptyValues(array &$payload): void
    {
        foreach (['featured_media_id', 'related_department_id', 'related_programme_id', 'academic_session_id', 'semester_id', 'event_start_at', 'event_end_at'] as $field) {
            if (($payload[$field] ?? null) === '') {
                $payload[$field] = null;
            }
        }
        foreach (['event_start_at', 'event_end_at'] as $field) {
            if (! empty($payload[$field])) {
                $timestamp = strtotime((string) $payload[$field]);
                if ($timestamp === false) {
                    throw new InvalidArgumentException($field . ' must contain a valid date and time.');
                }
                $payload[$field] = date('Y-m-d H:i:s', $timestamp);
            }
        }
        $payload['audience'] ??= 'public';
        $payload['visibility'] ??= 'public';
        $payload['priority'] ??= 'normal';
    }

    private function assertType(string $type): void
    {
        if (! in_array($type, self::TYPES, true)) {
            throw new InvalidArgumentException('Unsupported editorial content type.');
        }
    }

    private function assertAuthority(string $authority, string $message): void
    {
        if (! service('tenantAccess')->hasAuthority(service('tenantContextManager')->current(), $authority)) {
            throw new InvalidArgumentException($message);
        }
    }

    /** @param array<string, mixed> $payload @param array<string, mixed> $metadata */
    private function audit(string $event, int $id, array $payload, array $metadata = []): void
    {
        service('auditLogger')->record($event, [
            'target_type' => 'website_content_item',
            'target_id' => $id,
            'summary' => 'Editorial content lifecycle changed.',
            'metadata' => array_merge(['content_type' => $payload['content_type'], 'status' => $payload['status'], 'slug' => $payload['slug']], $metadata),
        ]);
    }

    private function invalidate(string $type, ?string ...$slugs): void
    {
        // One revision bump invalidates homepage, detail, and every paginated
        // listing key for the current tenant without relying on cache tags.
        service('publicWebsiteCache')->bump('editorial.revision');
    }
}
