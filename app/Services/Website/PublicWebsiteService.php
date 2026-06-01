<?php

namespace App\Services\Website;

/**
 * Builds common view data for all public pages.
 *
 * Views receive a prepared tenant-scoped model and never perform database
 * queries, preserving a debuggable service-controller-view boundary.
 */
class PublicWebsiteService
{
    /** @return array<string, mixed> */
    public function page(string $routeName, string $pageTitle = '', array $routeParameters = []): array
    {
        $settings = service('websiteSettings')->publicSettings();

        $data = [
            'settings' => $settings,
            'theme' => service('themeResolver')->tenantTheme(),
            'menuItems' => service('websiteMenu')->publicItems(),
            'pageTitle' => $pageTitle,
            'metaTitle' => $pageTitle !== '' ? $pageTitle . ' | ' . $settings['site_title'] : ($settings['seo_title'] ?? $settings['site_title']),
            'metaDescription' => $settings['seo_description'] ?? $settings['about_summary'] ?? '',
            'canonicalUrl' => service('publicWebsiteUrl')->canonical($routeName, $routeParameters),
        ];

        if ($routeName === 'home') {
            // Homepage aggregation is optional and tenant-scoped. Empty sections
            // are omitted by the view instead of rendering filler content.
            $data['editorial'] = service('publicEditorial')->homepage();
            $data['galleryAlbums'] = service('publicInstitutionalShowcase')->homepage();
        }

        return $data;
    }
}
