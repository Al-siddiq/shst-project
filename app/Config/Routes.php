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
});

// Explicit slug routes support local development and deployments without a
// dedicated tenant hostname while preserving exactly the same public services.
$routes->group('t/(:segment)', ['filter' => 'tenantContext,publicTenant'], static function ($routes) {
    $routes->get('/', 'PublicSite\HomeController::index');
    $routes->get('about', 'PublicSite\AboutController::index');
    $routes->get('contact', 'PublicSite\ContactController::index');
});

$routes->group('auth', static function ($routes) {
    $routes->get('login', 'AuthController::login');
    $routes->get('identifier/(:segment)', 'AuthController::identifierPolicy/$1');
});

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
    $routes->post('upload', 'Tenant\Website\MediaController::upload');
    $routes->patch('(:num)/visibility', 'Tenant\Website\MediaController::updateVisibility/$1');
    $routes->delete('(:num)', 'Tenant\Website\MediaController::delete/$1');
    $routes->get('(:num)/private', 'Tenant\Website\MediaController::showPrivate/$1');
});
