<?php
/**
 * Lightweight public shell shared by every tenant website.
 *
 * All displayed school values arrive from tenant-scoped services. The template
 * intentionally contains neutral structure only and performs no database work.
 */
$portalUrl = public_external_url($settings['portal_url'] ?? null);
$applicationUrl = public_external_url($settings['application_info_url'] ?? null);
$heroMediaId = isset($settings['hero_media_id']) ? (int) $settings['hero_media_id'] : null;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= esc((string) $metaDescription) ?>">
    <link rel="canonical" href="<?= esc($canonicalUrl) ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/public-site.css') ?>">
    <title><?= esc((string) $metaTitle) ?></title>
    <style>
        :root {
            --tenant-primary: <?= esc($theme['primary_color'], 'css') ?>;
            --tenant-secondary: <?= esc($theme['secondary_color'], 'css') ?>;
            --tenant-accent: <?= esc($theme['accent_color'], 'css') ?>;
        }
    </style>
</head>
<body>
    <a class="skip-link" href="#main-content">Skip to content</a>
    <header class="site-header">
        <div class="container header-row">
            <a class="brand" href="<?= esc(public_site_url('home')) ?>">
                <span class="brand-mark" aria-hidden="true"><?= esc(mb_substr((string) ($settings['short_name'] ?: $settings['site_title']), 0, 1)) ?></span>
                <span>
                    <strong><?= esc((string) $settings['site_title']) ?></strong>
                    <?php if (! empty($settings['tagline'])): ?><small><?= esc($settings['tagline']) ?></small><?php endif ?>
                </span>
            </a>
            <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="public-navigation" data-menu-toggle>
                <span aria-hidden="true">☰</span> Menu
            </button>
            <nav id="public-navigation" class="site-nav" aria-label="Main navigation" data-menu>
                <ul><?= view('public_site/_menu_items', ['items' => $menuItems]) ?></ul>
                <?php if ($portalUrl !== null): ?><a class="button button-outline" href="<?= esc($portalUrl) ?>">Portal</a><?php endif ?>
            </nav>
        </div>
    </header>

    <main id="main-content">
        <?= $this->renderSection('content') ?>
    </main>

    <footer class="site-footer">
        <div class="container footer-grid">
            <div>
                <strong><?= esc((string) $settings['site_title']) ?></strong>
                <?php if (! empty($settings['motto'])): ?><p><?= esc($settings['motto']) ?></p><?php endif ?>
            </div>
            <div>
                <strong>Contact</strong>
                <?php if (! empty($settings['address'])): ?><p><?= esc($settings['address']) ?></p><?php endif ?>
                <?php if (! empty($settings['contact_phone'])): ?><p><a href="tel:<?= esc($settings['contact_phone']) ?>"><?= esc($settings['contact_phone']) ?></a></p><?php endif ?>
                <?php if (! empty($settings['contact_email'])): ?><p><a href="mailto:<?= esc($settings['contact_email']) ?>"><?= esc($settings['contact_email']) ?></a></p><?php endif ?>
            </div>
            <div>
                <strong>Quick links</strong>
                <p><a href="<?= esc(public_site_url('about')) ?>">About us</a></p>
                <p><a href="<?= esc(public_site_url('contact')) ?>">Contact us</a></p>
                <p><a href="<?= esc(public_site_url('admissions')) ?>">Admissions</a></p>
                <?php if ($applicationUrl !== null): ?><p><a href="<?= esc($applicationUrl) ?>"><?= esc($settings['application_cta_label']) ?></a></p><?php endif ?>
            </div>
        </div>
    </footer>
    <script src="<?= base_url('assets/js/public-site.js') ?>" defer></script>
</body>
</html>
