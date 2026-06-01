<?php
use App\Entities\TenantContext;
use App\Models\MembershipAuthorityModel;
use App\Models\OperationalAuthorityModel;
use App\Models\Tenant\Website\WebsiteMenuItemModel;
use App\Services\Website\WebsiteMenuManagementService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
/** Confirms parent menu references cannot cross tenant boundaries. @internal */
final class WebsitePhase5MenuIsolationTest extends CIUnitTestCase { use DatabaseTestTrait; protected $migrate=true;protected $namespace='App';public function testCrossTenantParentIsRejected():void{$a=$this->tenant('menu-a');$b=$this->tenant('menu-b');$this->context($b,'menu-b');$foreign=(int)(new WebsiteMenuItemModel())->insert(['label'=>'Foreign','link_type'=>'route','route_name'=>'home'],true);$this->context($a,'menu-a');$user=701;service('session')->set('user_id',$user);$this->db->table('tenant_memberships')->insert(['tenant_id'=>$a,'user_id'=>$user,'status'=>'active','is_active'=>1]);$authority=(int)(new OperationalAuthorityModel())->insert(['code'=>'website.menu.manage','name'=>'Manage menu','is_active'=>1],true);(new MembershipAuthorityModel())->insert(['user_id'=>$user,'authority_id'=>$authority]);$this->expectException(\InvalidArgumentException::class);(new WebsiteMenuManagementService())->save(['label'=>'Child','parent_id'=>$foreign,'link_type'=>'route','route_name'=>'about','target'=>'_self','sort_order'=>0,'status'=>'active','is_visible'=>1]);}private function tenant(string $slug):int{$this->db->table('tenants')->insert(['school_name'=>$slug,'slug'=>$slug,'status'=>'active']);return (int)$this->db->insertID();}private function context(int $id,string $slug):void{service('tenantContextManager')->set(new TenantContext($id,$slug,'route_slug'));}}
