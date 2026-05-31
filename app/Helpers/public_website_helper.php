<?php

if (! function_exists('public_site_url')) {
    /**
     * Generates a tenant-aware public URL from views without embedding routing
     * rules in templates. The service remains the single source of truth.
     */
    function public_site_url(string $routeName, array $parameters = []): string
    {
        return service('publicWebsiteUrl')->route($routeName, $parameters);
    }
}

if (! function_exists('public_external_url')) {
    /**
     * Allows only HTTP(S) links from database configuration. Returning null is
     * safer than rendering an unsafe protocol into a public page.
     */
    function public_external_url(?string $url): ?string
    {
        if ($url === null || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        return in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true) ? $url : null;
    }
}


if (! function_exists('public_site_path')) {
    /** Builds a tenant-aware path for assets such as public media derivatives. */
    function public_site_path(string $path): string
    {
        return service('publicWebsiteUrl')->path($path);
    }
}
