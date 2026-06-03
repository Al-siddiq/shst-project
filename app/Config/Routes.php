<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// Phase 1 tenant public pages remain server-rendered and lightweight. The
// context filter runs first; PublicTenantFilter then enforces active launch state.
$routes->group('', ['filter' => 'tenantContext,publicTenant'], static function ($routes) {
    $routes->get('/', 'PublicSite\HomeController::index');
    $routes->get('about', 'PublicSite\AboutController::index');
    $routes->get('contact', 'PublicSite\ContactController::index');
    $routes->get('departments', 'PublicSite\DepartmentController::index');
    $routes->get('departments/(:segment)', 'PublicSite\DepartmentController::show/$1');
    $routes->get('programmes', 'PublicSite\ProgrammeController::index');
    $routes->get('programmes/(:segment)', 'PublicSite\ProgrammeController::show/$1');
    $routes->get('admissions', 'PublicSite\AdmissionController::index');
    $routes->get('admissions/programmes', 'PublicSite\AdmissionProgrammeController::index');
    $routes->get('admissions/programmes/(:num)', 'PublicSite\AdmissionProgrammeController::show/$1');
    $routes->get('apply', 'PublicSite\ApplicantStartController::index');
    $routes->get('news', 'PublicSite\ContentController::index/news');
    $routes->get('news/(:segment)', 'PublicSite\ContentController::show/news/$1');
    $routes->get('announcements', 'PublicSite\ContentController::index/announcements');
    $routes->get('announcements/(:segment)', 'PublicSite\ContentController::show/announcements/$1');
    $routes->get('calendar', 'PublicSite\ContentController::index/calendar');
    $routes->get('calendar/(:segment)', 'PublicSite\ContentController::show/calendar/$1');
    $routes->get('management', 'PublicSite\ManagementController::index');
    $routes->get('gallery', 'PublicSite\GalleryController::index');
    $routes->get('gallery/(:segment)', 'PublicSite\GalleryController::show/$1');
});

// Explicit slug routes support local development and deployments without a
// dedicated tenant hostname while preserving exactly the same public services.
$routes->group('t/(:segment)', ['filter' => 'tenantContext,publicTenant'], static function ($routes) {
    $routes->get('/', 'PublicSite\HomeController::index');
    $routes->get('about', 'PublicSite\AboutController::index');
    $routes->get('contact', 'PublicSite\ContactController::index');
    $routes->get('departments', 'PublicSite\DepartmentController::index');
    $routes->get('departments/(:segment)', 'PublicSite\DepartmentController::show/$1');
    $routes->get('programmes', 'PublicSite\ProgrammeController::index');
    $routes->get('programmes/(:segment)', 'PublicSite\ProgrammeController::show/$1');
    $routes->get('admissions', 'PublicSite\AdmissionController::index');
    $routes->get('admissions/programmes', 'PublicSite\AdmissionProgrammeController::index');
    $routes->get('admissions/programmes/(:num)', 'PublicSite\AdmissionProgrammeController::show/$1');
    $routes->get('apply', 'PublicSite\ApplicantStartController::index');
    $routes->get('news', 'PublicSite\ContentController::index/news');
    $routes->get('news/(:segment)', 'PublicSite\ContentController::show/news/$1');
    $routes->get('announcements', 'PublicSite\ContentController::index/announcements');
    $routes->get('announcements/(:segment)', 'PublicSite\ContentController::show/announcements/$1');
    $routes->get('calendar', 'PublicSite\ContentController::index/calendar');
    $routes->get('calendar/(:segment)', 'PublicSite\ContentController::show/calendar/$1');
    $routes->get('management', 'PublicSite\ManagementController::index');
    $routes->get('gallery', 'PublicSite\GalleryController::index');
    $routes->get('gallery/(:segment)', 'PublicSite\GalleryController::show/$1');
});

$routes->group('auth', static function ($routes) {
    // Shield owns registration, login, logout, verification, and recovery.
    service('auth')->routes($routes);
    $routes->get('identifier/(:segment)', 'AuthController::identifierPolicy/$1');
});

// Private applicant documents are never public media. The controller repeats
// ownership-or-reviewer authorization after these coarse HTTP boundaries.
$routes->get('applicant/documents/(:segment)', 'Applicant\DocumentController::download/$1', [
    'filter' => 'protectedAuth,tenantContext:required',
]);
$routes->get('applicant/readiness', 'Applicant\DashboardController::readiness', [
    'filter' => 'protectedAuth,tenantContext:required,applicantAccess',
]);

// Phase 2 applicant access routes are protected by Shield and tenant context.
// Profile creation is allowed before applicantAccess; draft routes require an
// existing owned applicant profile and the services repeat ownership checks.
$routes->get('applicant/profile', 'Applicant\ProfileController::edit', [
    'filter' => 'protectedAuth,tenantContext:required',
]);
$routes->post('applicant/profile', 'Applicant\ProfileController::save', [
    'filter' => 'csrf,protectedAuth,tenantContext:required',
]);
$routes->get('applicant', 'Applicant\DashboardController::index', [
    'filter' => 'protectedAuth,tenantContext:required,applicantAccess',
]);
$routes->post('applicant/applications', 'Applicant\ApplicationController::start', [
    'filter' => 'csrf,protectedAuth,tenantContext:required,applicantAccess',
]);
$routes->get('applicant/applications/(:segment)', 'Applicant\ApplicationController::show/$1', [
    'filter' => 'protectedAuth,tenantContext:required,applicantAccess',
]);
$routes->match(['post', 'put'], 'applicant/applications/(:segment)/biodata', 'Applicant\ApplicationController::saveBiodata/$1', [
    'filter' => 'csrf,protectedAuth,tenantContext:required,applicantAccess',
]);
$routes->match(['post', 'put'], 'applicant/applications/(:segment)/olevel', 'Applicant\ApplicationController::saveOlevel/$1', [
    'filter' => 'csrf,protectedAuth,tenantContext:required,applicantAccess',
]);
$routes->post('applicant/applications/(:segment)/documents', 'Applicant\DocumentController::upload/$1', [
    'filter' => 'csrf,protectedAuth,tenantContext:required,applicantAccess',
]);
$routes->get('applicant/applications/(:segment)/preview', 'Applicant\ApplicationController::preview/$1', [
    'filter' => 'protectedAuth,tenantContext:required,applicantAccess',
]);
$routes->post('applicant/applications/(:segment)/submit', 'Applicant\ApplicationController::submit/$1', [
    'filter' => 'csrf,protectedAuth,tenantContext:required,applicantAccess',
]);


// Phase 4 admissions review workspace. Route filters establish coarse tenant
// and authority boundaries; AdmissionReviewService repeats all sensitive checks.
$routes->group('tenant/admissions/applications', [
    'filter' => 'protectedAuth,tenantContext:required,tenantAccess:authority:admissions.applications.view',
], static function ($routes) {
    $routes->get('/', 'Tenant\Admissions\ApplicationReviewController::index');
    $routes->get('audit', 'Tenant\Admissions\ApplicationReviewController::audit', ['filter' => 'tenantAccess:authority:admissions.audit.view']);
    $routes->get('(:num)', 'Tenant\Admissions\ApplicationReviewController::show/$1');
});
$routes->post('tenant/admissions/applications/(:num)/review', 'Tenant\Admissions\ApplicationReviewController::review/$1', [
    'filter' => 'csrf,protectedAuth,tenantContext:required,tenantAccess:authority:admissions.applications.review',
]);
$routes->post('tenant/admissions/documents/(:num)/review', 'Tenant\Admissions\ApplicationReviewController::reviewDocument/$1', [
    'filter' => 'csrf,protectedAuth,tenantContext:required,tenantAccess:authority:admissions.documents.review',
]);
$routes->post('tenant/admissions/applications/(:num)/screening', 'Tenant\Admissions\ApplicationReviewController::screening/$1', [
    'filter' => 'csrf,protectedAuth,tenantContext:required,tenantAccess:authority:admissions.screening.manage',
]);

$routes->group('platform', static function ($routes) {
    // Phase 2 platform tenant onboarding skeleton endpoints.
    $routes->get('tenants', 'Platform\\TenantController::index');
    $routes->post('tenants', 'Platform\\TenantController::create');
});

$routes->group('internal', ['filter' => 'protectedAuth'], static function ($routes) {
    $routes->get('dashboard', 'Internal\\PortalController::dashboard');

    // Tenant context must resolve before accessing tenant-sensitive internals.
    $routes->get('tenant-context', 'Internal\\TenantContextController::show', ['filter' => 'tenantContext:required']);
});


$routes->group('tenant/config', ['filter' => 'protectedAuth,tenantContext:required'], static function ($routes) {
    // Phase 3 tenant configuration foundation endpoints.
    $routes->post('profile', 'Tenant\ConfigurationController::profileUpsert');
    $routes->post('academic-sessions', 'Tenant\ConfigurationController::createAcademicSession');
    $routes->post('semesters', 'Tenant\ConfigurationController::createSemester');
    $routes->post('levels', 'Tenant\ConfigurationController::createLevel');
    $routes->post('departments', 'Tenant\ConfigurationController::createDepartment');
    $routes->post('programmes', 'Tenant\ConfigurationController::createProgramme');
    $routes->post('courses', 'Tenant\ConfigurationController::createCourse');
    $routes->post('programme-courses', 'Tenant\ConfigurationController::mapProgrammeCourse');
});


$routes->group('tenant/access', ['filter' => 'protectedAuth,tenantContext:required,tenantAccess:authority:tenant.access.manage'], static function ($routes) {
    // Phase 4 access-control foundation endpoints.
    $routes->post('memberships', 'Tenant\AccessController::createMembership');
    $routes->post('groups', 'Tenant\AccessController::assignGroup');
    $routes->post('authorities', 'Tenant\AccessController::createAuthority');
    $routes->post('authority-grants', 'Tenant\AccessController::grantAuthority');
});

$routes->get('tenant/navigation', 'Tenant\AccessController::navigation', [
    'filter' => 'protectedAuth,tenantContext:required,tenantAccess',
]);


$routes->group('tenant/theme', ['filter' => 'protectedAuth,tenantContext:required,tenantAccess:authority:school.configuration.manage'], static function ($routes) {
    // Phase 5 tenant theme and department identity endpoints.
    $routes->get('/', 'Tenant\ThemeController::show');
    $routes->post('/', 'Tenant\ThemeController::upsertTheme');
    $routes->post('department-identities', 'Tenant\ThemeController::upsertDepartmentIdentity');
});

$routes->get('tenant/audit-logs', 'Tenant\AuditController::index', [
    'filter' => 'protectedAuth,tenantContext:required,tenantAccess:authority:tenant.access.manage',
]);

$routes->get('internal/layout/(:segment)', 'Internal\LayoutController::show/$1', [
    'filter' => 'protectedAuth,tenantContext:required,tenantAccess',
]);


// Phase 0 Block 2 media foundation. Public delivery resolves and validates the
// tenant before the controller performs a tenant-scoped visibility lookup.
$routes->get('media/(:num)/(:segment)', 'PublicSite\\MediaController::show/$1/$2', [
    'filter' => 'tenantContext,publicTenant',
]);
$routes->get('t/(:segment)/media/(:num)/(:segment)', 'PublicSite\\MediaController::show/$2/$3', [
    'filter' => 'tenantContext,publicTenant',
]);

$routes->group('tenant/website/media', [
    'filter' => 'csrf,protectedAuth,tenantContext:required,tenantAccess:authority:website.media.manage',
], static function ($routes) {
    // Physical file handling remains behind authorized endpoints because
    // browsers must never submit or infer filesystem paths.
    $routes->get('/', 'Tenant\Website\MediaController::index');
    $routes->post('upload', 'Tenant\Website\MediaController::upload');
    $routes->patch('(:num)/visibility', 'Tenant\Website\MediaController::updateVisibility/$1');
    $routes->delete('(:num)', 'Tenant\Website\MediaController::delete/$1');
    $routes->get('(:num)/private', 'Tenant\Website\MediaController::showPrivate/$1');
});


// Phase 2 tenant-admin forms edit public extensions only. Operational academic
// records remain managed by Block 1 configuration endpoints.
$routes->group('tenant/website/showcase', [
    'filter' => 'csrf,protectedAuth,tenantContext:required,tenantAccess:authority:website.content.edit',
], static function ($routes) {
    $routes->get('departments', 'Tenant\Website\ShowcaseController::departments');
    $routes->post('departments', 'Tenant\Website\ShowcaseController::saveDepartment');
    $routes->get('programmes', 'Tenant\Website\ShowcaseController::programmes');
    $routes->post('programmes', 'Tenant\Website\ShowcaseController::saveProgramme');
    $routes->get('admissions', 'Tenant\Website\ShowcaseController::admissions');
    $routes->post('admissions', 'Tenant\Website\ShowcaseController::saveAdmissions');
});


// Phase 3 editorial routes keep draft editing and lifecycle commands separate.
// Filters provide an early HTTP boundary; services repeat authority checks so
// future CLI or API callers cannot bypass the same policy.
$routes->group('tenant/website/editorial', [
    'filter' => 'protectedAuth,tenantContext:required,tenantAccess:authority:website.content.view',
], static function ($routes) {
    $routes->get('(:segment)', 'Tenant\Website\EditorialController::index/$1');
    $routes->get('(:segment)/form', 'Tenant\Website\EditorialController::form/$1');
});
$routes->group('tenant/website/editorial', [
    'filter' => 'csrf,protectedAuth,tenantContext:required,tenantAccess:authority:website.content.create|website.content.edit',
], static function ($routes) {
    $routes->post('(:segment)/draft', 'Tenant\Website\EditorialController::saveDraft/$1');
    $routes->post('(:segment)/(:num)/draft', 'Tenant\Website\EditorialController::moveToDraft/$1/$2');
});
$routes->group('tenant/website/editorial', [
    'filter' => 'csrf,protectedAuth,tenantContext:required,tenantAccess:authority:website.content.publish',
], static function ($routes) {
    $routes->post('(:segment)/(:num)/schedule', 'Tenant\Website\EditorialController::schedule/$1/$2');
    $routes->post('(:segment)/(:num)/publish', 'Tenant\Website\EditorialController::publish/$1/$2');
});
$routes->post('tenant/website/editorial/(:segment)/(:num)/archive', 'Tenant\Website\EditorialController::archive/$1/$2', [
    'filter' => 'csrf,protectedAuth,tenantContext:required,tenantAccess:authority:website.content.archive',
]);
$routes->post('tenant/website/editorial/(:segment)/(:num)/delete', 'Tenant\Website\EditorialController::delete/$1/$2', [
    'filter' => 'csrf,protectedAuth,tenantContext:required,tenantAccess:authority:website.content.delete',
]);


// Phase 4 institutional showcase routes remain separated by capability: content
// editors manage leadership copy while media managers own gallery reuse/order.
$routes->group('tenant/website/institutional', ['filter' => 'csrf,protectedAuth,tenantContext:required,tenantAccess:authority:website.content.edit'], static function ($routes) {
    $routes->get('management', 'Tenant\Website\InstitutionalShowcaseController::management');
    $routes->post('management', 'Tenant\Website\InstitutionalShowcaseController::saveManagement');
});
$routes->post('tenant/website/institutional/management/(:num)/archive', 'Tenant\Website\InstitutionalShowcaseController::archiveManagement/$1', [
    'filter' => 'csrf,protectedAuth,tenantContext:required,tenantAccess:authority:website.content.archive',
]);
$routes->group('tenant/website/institutional/gallery', ['filter' => 'csrf,protectedAuth,tenantContext:required,tenantAccess:authority:website.media.manage'], static function ($routes) {
    $routes->get('/', 'Tenant\Website\InstitutionalShowcaseController::gallery');
    $routes->post('albums', 'Tenant\Website\InstitutionalShowcaseController::saveAlbum');
    $routes->post('albums/(:num)/archive', 'Tenant\Website\InstitutionalShowcaseController::archiveAlbum/$1');
    $routes->post('items', 'Tenant\Website\InstitutionalShowcaseController::addItem');
    $routes->post('albums/(:num)/reorder', 'Tenant\Website\InstitutionalShowcaseController::reorderItems/$1');
    $routes->post('items/(:num)/remove', 'Tenant\Website\InstitutionalShowcaseController::removeItem/$1');
});

// Phase 5 operational CMS routes expose tenant-only maintenance surfaces. Each
// service repeats authorization so future JSON or CLI callers cannot bypass it.
$routes->get('tenant/website', 'Tenant\Website\DashboardController::index', [
    'filter' => 'protectedAuth,tenantContext:required,tenantAccess:authority:website.dashboard.view',
]);
$routes->group('tenant/website/settings', ['filter' => 'csrf,protectedAuth,tenantContext:required,tenantAccess:authority:website.settings.manage'], static function ($routes) {
    $routes->get('/', 'Tenant\Website\SettingsController::index');
    $routes->post('/', 'Tenant\Website\SettingsController::save');
});
$routes->group('tenant/website/menu', ['filter' => 'csrf,protectedAuth,tenantContext:required,tenantAccess:authority:website.menu.manage'], static function ($routes) {
    $routes->get('/', 'Tenant\Website\MenuController::index');
    $routes->post('/', 'Tenant\Website\MenuController::save');
    $routes->post('reorder', 'Tenant\Website\MenuController::reorder');
    $routes->post('(:num)/archive', 'Tenant\Website\MenuController::archive/$1');
});
$routes->get('tenant/website/audit', 'Tenant\Website\AuditController::index', [
    'filter' => 'protectedAuth,tenantContext:required,tenantAccess:authority:website.audit.view',
]);


// Phase 1 admissions configuration routes remain narrow by authority. Services
// repeat decisive checks and related-record tenant validation for non-HTTP use.
$routes->get('tenant/admissions', 'Tenant\Admissions\DashboardController::index', [
    'filter' => 'protectedAuth,tenantContext:required,tenantAccess:authority:admissions.dashboard.view',
]);
$routes->group('tenant/admissions/cycles', ['filter' => 'csrf,protectedAuth,tenantContext:required,tenantAccess:authority:admissions.cycles.manage'], static function ($routes) {
    $routes->get('/', 'Tenant\Admissions\CycleController::index');
    $routes->post('/', 'Tenant\Admissions\CycleController::save');
    $routes->post('(:num)/transition/(:segment)', 'Tenant\Admissions\CycleController::transition/$1/$2');
});
$routes->group('tenant/admissions/programmes', ['filter' => 'csrf,protectedAuth,tenantContext:required,tenantAccess:authority:admissions.programmes.manage'], static function ($routes) {
    $routes->get('/', 'Tenant\Admissions\ProgrammeOpeningController::index');
    $routes->post('/', 'Tenant\Admissions\ProgrammeOpeningController::save');
});
$routes->group('tenant/admissions/requirements', ['filter' => 'csrf,protectedAuth,tenantContext:required,tenantAccess:authority:admissions.requirements.manage'], static function ($routes) {
    $routes->get('/', 'Tenant\Admissions\RequirementController::index');
    $routes->post('definitions', 'Tenant\Admissions\RequirementController::saveDefinition');
    $routes->post('subjects', 'Tenant\Admissions\RequirementController::saveSubject');
    $routes->post('documents', 'Tenant\Admissions\RequirementController::saveDocument');
});
