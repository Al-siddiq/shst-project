<?= $this->extend('layouts/public') ?>
<?= $this->section('content') ?>
<section class="page-header"><div class="container narrow"><p class="eyebrow">Institutional profile</p><h1>About us</h1></div></section>
<section class="section">
    <div class="container narrow content-stack">
        <?php if (! empty($settings['about_body'])): ?><div><h2>Our institution</h2><p><?= nl2br(esc($settings['about_body'])) ?></p></div><?php endif ?>
        <?php if (! empty($settings['mission'])): ?><div><h2>Mission</h2><p><?= nl2br(esc($settings['mission'])) ?></p></div><?php endif ?>
        <?php if (! empty($settings['vision'])): ?><div><h2>Vision</h2><p><?= nl2br(esc($settings['vision'])) ?></p></div><?php endif ?>
        <?php if (! empty($settings['history'])): ?><div><h2>History</h2><p><?= nl2br(esc($settings['history'])) ?></p></div><?php endif ?>
        <?php if (empty($settings['about_body']) && empty($settings['mission']) && empty($settings['vision']) && empty($settings['history'])): ?>
            <p>Institutional profile information will be available soon.</p>
        <?php endif ?>
    </div>
</section>
<?= $this->endSection() ?>
