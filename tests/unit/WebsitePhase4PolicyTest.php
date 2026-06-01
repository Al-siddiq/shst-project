<?php
use CodeIgniter\Test\CIUnitTestCase;
/** Protects the Phase 4 institutional showcase contract. @internal */
final class WebsitePhase4PolicyTest extends CIUnitTestCase
{
 public function testMigrationDefinesStandaloneLeadershipAndGalleryTables():void{$m=file_get_contents(APPPATH.'Database/Migrations/2026-05-31-180000_CreateInstitutionalShowcaseTables.php');foreach(['management_profiles','gallery_albums','gallery_items','cover_media_id','photo_media_id','media_file_id'] as $value){$this->assertStringContainsString($value,$m);}}
 public function testPublicReadsRequirePublishedRecords():void{$s=file_get_contents(APPPATH.'Services/Website/PublicInstitutionalShowcaseService.php');$this->assertStringContainsString("where('status', 'published')",$s);$this->assertStringContainsString("where('gallery_album_id',",$s);}
 public function testMediaControllerDelegatesPublicVisibilityEnforcement():void{$controller=file_get_contents(APPPATH.'Controllers/PublicSite/MediaController.php');$this->assertStringContainsString('publicDerivative',$controller);$this->assertStringContainsString('setStatusCode(404)',$controller);}
 public function testManagementServiceAuditsGalleryMutationsAndPublicMedia():void{$s=file_get_contents(APPPATH.'Services/Website/InstitutionalShowcaseManagementService.php');foreach(['website.gallery_item.added','website.gallery_item.removed','website.gallery_items.reordered','Published showcase records require public media'] as $value){$this->assertStringContainsString($value,$s);}}
}
