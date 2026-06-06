<?php

namespace Config;

use App\Services\AuditLogger;
use App\Services\Admissions\AdmissionAcceptanceService;
use App\Services\Admissions\AdmissionAuthorityProvisioner;
use App\Services\Admissions\AdmissionConfigurationService;
use App\Services\Admissions\AdmissionCorrectionWindowService;
use App\Services\Admissions\AdmissionDashboardService;
use App\Services\Admissions\AdmissionListPublicationService;
use App\Services\Admissions\AdmissionOperationsService;
use App\Services\Admissions\AdmissionReportService;
use App\Services\Admissions\AdmissionDecisionService;
use App\Services\Admissions\PublicAdmissionService;
use App\Services\Admissions\AdmissionReferenceGenerator;
use App\Services\Admissions\AdmissionReviewService;
use App\Services\Admissions\ApplicantAccessPolicy;
use App\Services\Admissions\ApplicantAuthRedirectService;
use App\Services\Admissions\ApplicantProfileService;
use App\Services\Admissions\ApplicationDraftService;
use App\Services\Admissions\ApplicantDocumentStorage;
use App\Services\Admissions\ApplicantIdentityNormalizer;
use App\Services\Admissions\AdmissionNotificationDispatcher;
use App\Services\Admissions\ApplicantDocumentService;
use App\Services\Admissions\ApplicationCompletionService;
use App\Services\Admissions\ApplicationSubmissionService;
use App\Services\Admissions\OlevelApplicationService;
use App\Services\LayoutResolver;
use App\Services\NavigationResolver;
use App\Services\ThemeResolver;
use App\Services\Tenancy\TenantContextManager;
use App\Services\Tenancy\TenantResolver;
use App\Services\TenantAccessService;
use App\Services\Website\MediaService;
use App\Services\Website\InstitutionalShowcaseManagementService;
use App\Services\Website\PublicInstitutionalShowcaseService;
use App\Services\Website\EditorialManagementService;
use App\Services\Website\PublicTenantGuard;
use App\Services\Website\PublicShowcaseService;
use App\Services\Website\PublicEditorialService;
use App\Services\Website\PublicWebsiteCache;
use App\Services\Website\PublicWebsiteService;
use App\Services\Website\PublicWebsiteUrlGenerator;
use App\Services\Website\WebsiteAuthorityProvisioner;
use App\Services\Website\WebsiteMenuService;
use App\Services\Website\WebsiteSettingsService;
use App\Services\Website\WebsiteSettingsManagementService;
use App\Services\Website\WebsiteMenuManagementService;
use App\Services\Website\WebsiteAuditService;
use App\Services\Website\WebsiteDashboardService;
use App\Services\Website\ShowcaseManagementService;
use CodeIgniter\Config\BaseService;

class Services extends BaseService
{
    public static function tenantResolver(bool $getShared = true): TenantResolver
    {
        if ($getShared) {
            return static::getSharedInstance('tenantResolver');
        }

        return new TenantResolver();
    }

    public static function tenantContextManager(bool $getShared = true): TenantContextManager
    {
        if ($getShared) {
            return static::getSharedInstance('tenantContextManager');
        }

        return new TenantContextManager();
    }

    public static function tenantAccess(bool $getShared = true): TenantAccessService
    {
        if ($getShared) {
            return static::getSharedInstance('tenantAccess');
        }

        return new TenantAccessService();
    }

    public static function navigationResolver(bool $getShared = true): NavigationResolver
    {
        if ($getShared) {
            return static::getSharedInstance('navigationResolver');
        }

        return new NavigationResolver();
    }

    public static function themeResolver(bool $getShared = true): ThemeResolver
    {
        if ($getShared) {
            return static::getSharedInstance('themeResolver');
        }

        return new ThemeResolver();
    }

    public static function layoutResolver(bool $getShared = true): LayoutResolver
    {
        if ($getShared) {
            return static::getSharedInstance('layoutResolver');
        }

        return new LayoutResolver();
    }

    public static function auditLogger(bool $getShared = true): AuditLogger
    {
        if ($getShared) {
            return static::getSharedInstance('auditLogger');
        }

        return new AuditLogger();
    }

    /**
     * Shared guard keeps anonymous tenant eligibility consistent for public
     * pages and media routes without coupling that decision to controllers.
     */
    public static function publicTenantGuard(bool $getShared = true): PublicTenantGuard
    {
        if ($getShared) {
            return static::getSharedInstance('publicTenantGuard');
        }

        return new PublicTenantGuard();
    }

    /**
     * Shared media service centralizes safe storage, derivatives, and audits.
     */
    public static function websiteMedia(bool $getShared = true): MediaService
    {
        if ($getShared) {
            return static::getSharedInstance('websiteMedia');
        }

        return new MediaService();
    }

    /** Tenant-aware public URL rules belong outside views and controllers. */
    public static function publicWebsiteUrl(bool $getShared = true): PublicWebsiteUrlGenerator
    {
        if ($getShared) {
            return static::getSharedInstance('publicWebsiteUrl');
        }

        return new PublicWebsiteUrlGenerator();
    }

    /** Tenant namespaces are mandatory for every public-site cache entry. */
    public static function publicWebsiteCache(bool $getShared = true): PublicWebsiteCache
    {
        if ($getShared) {
            return static::getSharedInstance('publicWebsiteCache');
        }

        return new PublicWebsiteCache();
    }

    /** Builds tenant-admin website completion metrics and operational counts. */
    public static function websiteDashboard(bool $getShared = true): WebsiteDashboardService
    {
        if ($getShared) { return static::getSharedInstance('websiteDashboard'); }
        return new WebsiteDashboardService();
    }

    /** Owns validated tenant website settings writes. */
    public static function websiteSettingsManagement(bool $getShared = true): WebsiteSettingsManagementService
    {
        if ($getShared) { return static::getSharedInstance('websiteSettingsManagement'); }
        return new WebsiteSettingsManagementService();
    }

    /** Owns tenant-safe public navigation writes and ordering. */
    public static function websiteMenuManagement(bool $getShared = true): WebsiteMenuManagementService
    {
        if ($getShared) { return static::getSharedInstance('websiteMenuManagement'); }
        return new WebsiteMenuManagementService();
    }

    /** Provides website-only tenant audit filtering. */
    public static function websiteAudit(bool $getShared = true): WebsiteAuditService
    {
        if ($getShared) { return static::getSharedInstance('websiteAudit'); }
        return new WebsiteAuditService();
    }

    /** Resolves tenant profile fallbacks and CMS website settings. */
    public static function websiteSettings(bool $getShared = true): WebsiteSettingsService
    {
        if ($getShared) {
            return static::getSharedInstance('websiteSettings');
        }

        return new WebsiteSettingsService();
    }

    /** Resolves visible, ordered, tenant-owned public menu records. */
    public static function websiteMenu(bool $getShared = true): WebsiteMenuService
    {
        if ($getShared) {
            return static::getSharedInstance('websiteMenu');
        }

        return new WebsiteMenuService();
    }

    /** Builds shared view data for the lightweight server-rendered public site. */
    public static function publicWebsite(bool $getShared = true): PublicWebsiteService
    {
        if ($getShared) {
            return static::getSharedInstance('publicWebsite');
        }

        return new PublicWebsiteService();
    }

    /** Resolves published and due-scheduled editorial content for visitors. */
    public static function publicEditorial(bool $getShared = true): PublicEditorialService
    {
        if ($getShared) {
            return static::getSharedInstance('publicEditorial');
        }

        return new PublicEditorialService();
    }

    /** Owns editorial drafts, lifecycle commands, audits, and cache invalidation. */
    public static function editorialManagement(bool $getShared = true): EditorialManagementService
    {
        if ($getShared) {
            return static::getSharedInstance('editorialManagement');
        }

        return new EditorialManagementService();
    }

    /** Resolves published management profiles and galleries for visitors. */
    public static function publicInstitutionalShowcase(bool $getShared = true): PublicInstitutionalShowcaseService
    {
        if ($getShared) { return static::getSharedInstance('publicInstitutionalShowcase'); }
        return new PublicInstitutionalShowcaseService();
    }

    /** Owns tenant-safe management-profile and gallery CMS mutations. */
    public static function institutionalShowcaseManagement(bool $getShared = true): InstitutionalShowcaseManagementService
    {
        if ($getShared) { return static::getSharedInstance('institutionalShowcaseManagement'); }
        return new InstitutionalShowcaseManagementService();
    }

    /** Resolves published academic showcase records for anonymous visitors. */
    public static function publicShowcase(bool $getShared = true): PublicShowcaseService
    {
        if ($getShared) {
            return static::getSharedInstance('publicShowcase');
        }

        return new PublicShowcaseService();
    }

    /** Owns tenant-admin showcase writes, publication checks, audits, and cache invalidation. */
    public static function showcaseManagement(bool $getShared = true): ShowcaseManagementService
    {
        if ($getShared) {
            return static::getSharedInstance('showcaseManagement');
        }

        return new ShowcaseManagementService();
    }

    /**
     * Provisioning remains a service so onboarding and access-management flows
     * cannot diverge when granting tenant-super-admin website capabilities.
     */
    public static function websiteAuthorityProvisioner(bool $getShared = true): WebsiteAuthorityProvisioner
    {
        if ($getShared) {
            return static::getSharedInstance('websiteAuthorityProvisioner');
        }

        return new WebsiteAuthorityProvisioner();
    }

    /** Provisions the stable Block 3 authority catalogue into one tenant. */
    public static function admissionAuthorityProvisioner(bool $getShared = true): AdmissionAuthorityProvisioner
    {
        if ($getShared) {
            return static::getSharedInstance('admissionAuthorityProvisioner');
        }

        return new AdmissionAuthorityProvisioner();
    }

    /** Normalizes email and Nigerian mobile identifiers before Shield lookup. */
    public static function applicantIdentityNormalizer(bool $getShared = true): ApplicantIdentityNormalizer
    {
        if ($getShared) {
            return static::getSharedInstance('applicantIdentityNormalizer');
        }

        return new ApplicantIdentityNormalizer();
    }

    /** Enforces applicant profile and private-document ownership. */
    public static function applicantAccessPolicy(bool $getShared = true): ApplicantAccessPolicy
    {
        if ($getShared) {
            return static::getSharedInstance('applicantAccessPolicy');
        }

        return new ApplicantAccessPolicy();
    }

    /** Resolves private applicant files beneath the admission storage root. */
    public static function applicantDocumentStorage(bool $getShared = true): ApplicantDocumentStorage
    {
        if ($getShared) {
            return static::getSharedInstance('applicantDocumentStorage');
        }

        return new ApplicantDocumentStorage();
    }

    /** Allocates tenant-aware application reference skeleton values. */
    public static function admissionReferenceGenerator(bool $getShared = true): AdmissionReferenceGenerator
    {
        if ($getShared) {
            return static::getSharedInstance('admissionReferenceGenerator');
        }

        return new AdmissionReferenceGenerator();
    }
    /** Owns tenant-safe Phase 1 admission setup mutations. */
    public static function admissionConfiguration(bool $getShared = true): AdmissionConfigurationService
    {
        if ($getShared) { return static::getSharedInstance('admissionConfiguration'); }
        return new AdmissionConfigurationService();
    }

    /** Exposes sanitized active-cycle and open-programme public discovery. */
    public static function publicAdmissions(bool $getShared = true): PublicAdmissionService
    {
        if ($getShared) { return static::getSharedInstance('publicAdmissions'); }
        return new PublicAdmissionService();
    }

    /** Builds tenant-scoped admission setup gaps for the staff dashboard. */
    public static function admissionDashboard(bool $getShared = true): AdmissionDashboardService
    {
        if ($getShared) { return static::getSharedInstance('admissionDashboard'); }
        return new AdmissionDashboardService();
    }

    /** Creates or updates the tenant applicant profile owned by the Shield user. */
    public static function applicantProfile(bool $getShared = true): ApplicantProfileService
    {
        if ($getShared) { return static::getSharedInstance('applicantProfile'); }
        return new ApplicantProfileService();
    }

    /** Owns Phase 2 draft start, resume, and biodata autosave. */
    public static function applicationDraft(bool $getShared = true): ApplicationDraftService
    {
        if ($getShared) { return static::getSharedInstance('applicationDraft'); }
        return new ApplicationDraftService();
    }

    /** Centralizes tenant-aware Shield redirect links for applicant surfaces. */
    public static function applicantAuthRedirect(bool $getShared = true): ApplicantAuthRedirectService
    {
        if ($getShared) { return static::getSharedInstance('applicantAuthRedirect'); }
        return new ApplicantAuthRedirectService();
    }

    /** Validates temporary applicant correction access granted by reviewers. */
    public static function admissionCorrectionWindow(bool $getShared = true): AdmissionCorrectionWindowService
    {
        if ($getShared) { return static::getSharedInstance('admissionCorrectionWindow'); }
        return new AdmissionCorrectionWindowService();
    }

    /** Owns Phase 7 offer acceptance, clearance placeholder, and handoff marker. */
    public static function admissionAcceptance(bool $getShared = true): AdmissionAcceptanceService
    {
        if ($getShared) { return static::getSharedInstance('admissionAcceptance'); }
        return new AdmissionAcceptanceService();
    }

    /** Owns Phase 6 admission-list drafts, publication, and public reads. */
    public static function admissionListPublication(bool $getShared = true): AdmissionListPublicationService
    {
        if ($getShared) { return static::getSharedInstance('admissionListPublication'); }
        return new AdmissionListPublicationService();
    }

    /** Owns Phase 5 shortlisting, decisions, approval, and offer issuance. */
    public static function admissionDecision(bool $getShared = true): AdmissionDecisionService
    {
        if ($getShared) { return static::getSharedInstance('admissionDecision'); }
        return new AdmissionDecisionService();
    }

    /** Owns Phase 4 staff review queue, document review, and screening actions. */
    public static function admissionReview(bool $getShared = true): AdmissionReviewService
    {
        if ($getShared) { return static::getSharedInstance('admissionReview'); }
        return new AdmissionReviewService();
    }

    /** Owns applicant O'Level sittings and subject-grade rows. */
    public static function olevelApplication(bool $getShared = true): OlevelApplicationService
    {
        if ($getShared) { return static::getSharedInstance('olevelApplication'); }
        return new OlevelApplicationService();
    }

    /** Owns private applicant document upload and replacement metadata. */
    public static function applicantDocument(bool $getShared = true): ApplicantDocumentService
    {
        if ($getShared) { return static::getSharedInstance('applicantDocument'); }
        return new ApplicantDocumentService();
    }

    /** Evaluates draft completion before preview or final submission. */
    public static function applicationCompletion(bool $getShared = true): ApplicationCompletionService
    {
        if ($getShared) { return static::getSharedInstance('applicationCompletion'); }
        return new ApplicationCompletionService();
    }

    /** Performs idempotent Phase 3 application preview and final submission. */
    public static function applicationSubmission(bool $getShared = true): ApplicationSubmissionService
    {
        if ($getShared) { return static::getSharedInstance('applicationSubmission'); }
        return new ApplicationSubmissionService();
    }

    /** Queues admission notification intent without implementing delivery. */
    public static function admissionNotificationDispatcher(bool $getShared = true): AdmissionNotificationDispatcher
    {
        if ($getShared) { return static::getSharedInstance('admissionNotificationDispatcher'); }
        return new AdmissionNotificationDispatcher();
    }

    /** Builds Phase 8 tenant-scoped admissions reports and CSV exports. */
    public static function admissionReport(bool $getShared = true): AdmissionReportService
    {
        if ($getShared) { return static::getSharedInstance('admissionReport'); }
        return new AdmissionReportService();
    }

    /** Exposes Phase 8 notification outbox retries and tenant audit visibility. */
    public static function admissionOperations(bool $getShared = true): AdmissionOperationsService
    {
        if ($getShared) { return static::getSharedInstance('admissionOperations'); }
        return new AdmissionOperationsService();
    }

}
