<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand; use CodeIgniter\CLI\CLI;
final class ApplicationMaintenance extends BaseCommand
{
    protected $group='Operations'; protected $name='application:maintain'; protected $description='Normalize scheduled content and expire admissions offers.';
    public function run(array $params){CLI::write(json_encode(['published'=>service('scheduledEditorial')->normalizeAll(),'expired_offers'=>service('admissionsMaintenance')->expireOffers(),'rejected_documents_cleaned'=>service('documentScanner')->cleanupRejected(),'website_media'=>service('websiteMediaMaintenance')->reconcileAll()],JSON_PRETTY_PRINT));}
}
