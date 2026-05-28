<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

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
