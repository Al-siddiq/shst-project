<?= $this->extend('layouts/public') ?>
<?= $this->section('content') ?>
<?php $heroMediaId = isset($settings['hero_media_id']) ? (int) $settings['hero_media_id'] : null; ?>
<section class="hero">
    <div class="container hero-grid">
        <div>
            <?php if (! empty($settings['tagline'])): ?><p class="eyebrow"><?= esc($settings['tagline']) ?></p><?php endif ?>
            <h1><?= esc((string) ($settings['hero_title'] ?: $settings['site_title'])) ?></h1>
            <?php if (! empty($settings['hero_summary'])): ?><p class="lead"><?= esc($settings['hero_summary']) ?></p><?php endif ?>
            <div class="action-row">
                <a class="button" href="<?= esc(public_site_url('about')) ?>">Learn about us</a>
                <a class="button button-outline" href="<?= esc(public_site_url('contact')) ?>">Contact us</a>
            </div>
        </div>
        <?php if ($heroMediaId): ?>
            <img class="hero-image" src="<?= esc(public_site_path('media/' . $heroMediaId . '/large')) ?>" alt="<?= esc((string) $settings['site_title']) ?>">
        <?php endif ?>
    </div>
</section>
<section class="section">
    <div class="container narrow">
        <p class="eyebrow">Institutional profile</p>
        <h2>Welcome</h2>
        <?php if (! empty($settings['about_summary'])): ?>
            <p class="lead"><?= esc($settings['about_summary']) ?></p>
        <?php else: ?>
            <p>Institutional information will be available soon.</p>
        <?php endif ?>
        <a class="text-link" href="<?= esc(public_site_url('about')) ?>">Read more about us →</a>
    </div>
</section>
<?= view('public_site/_homepage_editorial', ['editorial' => $editorial]) ?>
<?= view('public_site/_homepage_gallery', ['albums' => $galleryAlbums]) ?>
<?= $this->endSection() ?>
