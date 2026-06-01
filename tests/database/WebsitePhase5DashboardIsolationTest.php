<?php
use App\Entities\TenantContext;
use App\Models\MembershipAuthorityModel;
use App\Models\OperationalAuthorityModel;
use App\Models\Tenant\Website\WebsiteContentItemModel;
use App\Services\Website\WebsiteDashboardService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
/** Confirms dashboard editorial metrics remain tenant-scoped. @internal */
final class WebsitePhase5DashboardIsolationTest extends CIUnitTestCase { use DatabaseTestTrait;protected $migrate=true;protected $namespace='App';public function testDashboardCountsOnlyCurrentTenantContent():void{$a=$this->tenant('dash-a');$b=$this->tenant('dash-b');$this->context($a,'dash-a');(new WebsiteContentItemModel())->insert(['content_type'=>'news','title'=>'A','slug'=>'a','body'=>'A','status'=>'published']);$this->context($b,'dash-b');(new WebsiteContentItemModel())->insert(['content_type'=>'news','title'=>'B','slug'=>'b','body'=>'B','status'=>'published']);$this->context($a,'dash-a');$user=702;service('session')->set('user_id',$user);$this->db->table('tenant_memberships')->insert(['tenant_id'=>$a,'user_id'=>$user,'status'=>'active','is_active'=>1]);$authority=(int)(new OperationalAuthorityModel())->insert(['code'=>'website.dashboard.view','name'=>'View dashboard','is_active'=>1],true);(new MembershipAuthorityModel())->insert(['user_id'=>$user,'authority_id'=>$authority]);$this->assertSame(1,(new WebsiteDashboardService())->summary()['contentCounts']['published']);}private function tenant(string $slug):int{$this->db->table('tenants')->insert(['school_name'=>$slug,'slug'=>$slug,'status'=>'active']);return (int)$this->db->insertID();}private function context(int $id,string $slug):void{service('tenantContextManager')->set(new TenantContext($id,$slug,'route_slug'));}}
