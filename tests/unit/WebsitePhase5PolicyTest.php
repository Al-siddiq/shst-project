<?php
use CodeIgniter\Test\CIUnitTestCase;
/** Protects the Phase 5 operational CMS contract. @internal */
final class WebsitePhase5PolicyTest extends CIUnitTestCase
{
 public function testMenuManagerValidatesNestingRoutesAndAudit():void{$s=file_get_contents(APPPATH.'Services/Website/WebsiteMenuManagementService.php');foreach(['Menu nesting is limited','Unsupported public website route','website.menu.reordered',"forget('menu')"] as $value){$this->assertStringContainsString($value,$s);}}
 public function testSettingsManagerValidatesUrlsAndInvalidatesCache():void{$s=file_get_contents(APPPATH.'Services/Website/WebsiteSettingsManagementService.php');$this->assertStringContainsString('FILTER_VALIDATE_URL',$s);$this->assertStringContainsString("forgetMany(['settings', 'menu'])",$s);}
 public function testVueIslandUsesCompositionApiWithRetryMessage():void{$s=file_get_contents(ROOTPATH.'resources/js/website-menu-manager.js');$this->assertStringContainsString("from 'vue'",$s);$this->assertStringContainsString('ref(', $s);$this->assertStringContainsString('Retry shortly', $s);}
 public function testTenantAdminLayoutProvidesMobileViewportAndSkipLink():void{$layout=file_get_contents(APPPATH.'Views/layouts/tenant_admin.php');$this->assertStringContainsString('name="viewport"',$layout);$this->assertStringContainsString('Skip to content',$layout);}
 public function testCanonicalHostIsValidated():void{$s=file_get_contents(APPPATH.'Services/Website/PublicWebsiteUrlGenerator.php');$this->assertStringContainsString('FILTER_VALIDATE_DOMAIN',$s);$this->assertStringContainsString('FILTER_FLAG_HOSTNAME',$s);}
}
