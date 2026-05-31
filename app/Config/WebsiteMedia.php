<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Central media policy for the public website foundation.
 *
 * Keeping upload rules in configuration avoids scattering security-sensitive
 * limits across controllers and makes future per-environment tuning explicit.
 */
class WebsiteMedia extends BaseConfig
{
    /** @var list<string> */
    public array $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];

    /** @var array<string, string> */
    public array $extensionsByMimeType = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    /** @var list<string> */
    public array $allowedVisibilities = ['public', 'private'];

    /** @var list<string> */
    public array $allowedCategories = [
        'tenant_logo',
        'homepage_hero',
        'content_featured_image',
        'department_image',
        'programme_image',
        'management_photo',
        'gallery_image',
    ];

    /** Maximum source upload size: 5 MiB. */
    public int $maxBytes = 5 * 1024 * 1024;

    /** Images larger than this are rejected before expensive processing. */
    public int $maxWidth = 6000;
    public int $maxHeight = 6000;

    /**
     * Public derivatives keep the public site lightweight on mobile networks.
     * Keys are persisted with metadata so delivery never guesses a filename.
     *
     * @var array<string, array{width: int, height: int}>
     */
    public array $derivatives = [
        'thumbnail' => ['width' => 480, 'height' => 320],
        'medium' => ['width' => 960, 'height' => 640],
        'large' => ['width' => 1600, 'height' => 1067],
    ];

    /** JPEG/WebP output quality used by CI4 image handlers. */
    public int $quality = 82;
}
