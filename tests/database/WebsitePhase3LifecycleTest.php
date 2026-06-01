<?php

use App\Entities\TenantContext;
use App\Models\MembershipAuthorityModel;
use App\Models\OperationalAuthorityModel;
use App\Models\Tenant\Website\WebsiteContentItemModel;
use App\Services\Website\EditorialManagementService;
use App\Services\Website\PublicEditorialService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/** Exercises publication-time, pagination, tenant-isolation, and authority behavior. @internal */
final class WebsitePhase3LifecycleTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = true;
    protected $namespace = 'App';

    protected function setUp(): void
    {
        parent::setUp();
        cache()->clean();
        service('session')->remove('user_id');
    }

    public function testPublicNewsPaginationExcludesDraftFutureAndOtherTenantRecords(): void
    {
        $tenantA = $this->insertTenant('editorial-a');
        $tenantB = $this->insertTenant('editorial-b');
        $this->setTenant($tenantA, 'editorial-a');
        foreach (range(1, 12) as $number) {
            $this->insertContent('news', 'published-' . $number, 'published');
        }
        $this->insertContent('news', 'draft-news', 'draft');
        $this->insertContent('news', 'future-news', 'scheduled', date('Y-m-d H:i:s', time() + 3600));
        $this->insertContent('news', 'due-news', 'scheduled', date('Y-m-d H:i:s', time() - 3600));

        $this->setTenant($tenantB, 'editorial-b');
        $this->insertContent('news', 'other-tenant-news', 'published');

        $this->setTenant($tenantA, 'editorial-a');
        $first = (new PublicEditorialService())->listing('news', 1, 10);
        $second = (new PublicEditorialService())->listing('news', 2, 10);
        $allSlugs = array_merge(array_column($first['items'], 'slug'), array_column($second['items'], 'slug'));

        $this->assertSame(13, $first['total']);
        $this->assertSame(2, $first['totalPages']);
        $this->assertCount(10, $first['items']);
        $this->assertCount(3, $second['items']);
        $this->assertContains('due-news', $allSlugs);
        $this->assertNotContains('future-news', $allSlugs);
        $this->assertNotContains('draft-news', $allSlugs);
        $this->assertNotContains('other-tenant-news', $allSlugs);
    }

    public function testEditorCanCreateDraftButCannotPublishWithoutPublisherAuthority(): void
    {
        $tenant = $this->insertTenant('authority-tenant');
        $this->setTenant($tenant, 'authority-tenant');
        $userId = 501;
        service('session')->set('user_id', $userId);
        $this->db->table('tenant_memberships')->insert(['tenant_id' => $tenant, 'user_id' => $userId, 'status' => 'active', 'is_active' => 1]);
        $this->grant($userId, 'website.content.create');

        $service = new EditorialManagementService();
        $id = $service->saveDraft('calendar_notice', $this->calendarDraft('Draft', 'draft'));
        $this->expectException(InvalidArgumentException::class);
        $service->publish($id, 'calendar_notice');
    }

    public function testPublisherCanPublishDraftAfterAuthorityGrant(): void
    {
        $tenant = $this->insertTenant('publisher-tenant');
        $this->setTenant($tenant, 'publisher-tenant');
        $userId = 502;
        service('session')->set('user_id', $userId);
        $this->db->table('tenant_memberships')->insert(['tenant_id' => $tenant, 'user_id' => $userId, 'status' => 'active', 'is_active' => 1]);
        $this->grant($userId, 'website.content.create');
        $this->grant($userId, 'website.content.publish');

        $service = new EditorialManagementService();
        $id = $service->saveDraft('calendar_notice', $this->calendarDraft('Publish me', 'publish-me'));
        $service->publish($id, 'calendar_notice');

        $this->assertSame('published', (new WebsiteContentItemModel())->find($id)['status']);
    }

    /** @return array<string, string> */
    private function calendarDraft(string $title, string $slug): array
    {
        return [
            'title' => $title,
            'slug' => $slug,
            'body' => $title . ' body',
            'event_start_at' => date('Y-m-d H:i:s', time() + 3600),
            'event_end_at' => date('Y-m-d H:i:s', time() + 7200),
            'visibility' => 'public',
        ];
    }

    private function insertTenant(string $slug): int
    {
        $this->db->table('tenants')->insert(['school_name' => $slug, 'slug' => $slug, 'status' => 'active']);

        return (int) $this->db->insertID();
    }

    private function setTenant(int $id, string $slug): void
    {
        service('tenantContextManager')->set(new TenantContext($id, $slug, 'route_slug'));
    }

    private function insertContent(string $type, string $slug, string $status, ?string $scheduledFor = null): void
    {
        (new WebsiteContentItemModel())->insert(['content_type' => $type, 'title' => $slug, 'slug' => $slug, 'body' => $slug, 'status' => $status, 'scheduled_for' => $scheduledFor]);
    }

    private function grant(int $userId, string $code): void
    {
        $authorityId = (int) (new OperationalAuthorityModel())->insert(['code' => $code, 'name' => $code, 'is_active' => 1], true);
        (new MembershipAuthorityModel())->insert(['user_id' => $userId, 'authority_id' => $authorityId]);
    }
}
