<?php
namespace App\Services\Admissions;

use App\Entities\TenantContext;
use App\Models\Tenant\Admissions\AdmissionOfferModel;
use App\Models\Tenant\Admissions\ApplicantApplicationModel;
use App\Models\TenantModel;
use CodeIgniter\I18n\Time;

final class AdmissionsMaintenanceService
{
    public function expireOffers(): int
    {
        $count=0;
        foreach((new TenantModel())->where('status','active')->findAll() as $tenant){
            service('tenantContextManager')->set(new TenantContext((int)$tenant['id'],(string)$tenant['slug'],'scheduler'));
            $offers=(new AdmissionOfferModel())->whereIn('offer_status',['issued','pending_acceptance'])->where('expires_at <',Time::now()->toDateTimeString())->findAll(1000);
            foreach($offers as $offer){
                service('transactional')->run(function()use($offer):array{
                    (new AdmissionOfferModel())->update($offer['id'],['offer_status'=>'expired']);
                    (new ApplicantApplicationModel())->update($offer['applicant_application_id'],['status'=>'offer_expired']);
                    service('auditLogger')->record('admissions.offer.expired',['target_type'=>'admission_offer','target_id'=>$offer['id'],'summary'=>'Expired admission offer normalized by scheduler.']); return [];
                }); $count++;
            }
        }
        service('tenantContextManager')->clear(); return $count;
    }
}
