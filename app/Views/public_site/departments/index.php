<?= $this->extend('layouts/public') ?>
<?= $this->section('content') ?>
<section class="page-header"><div class="container"><p class="eyebrow">Academic structure</p><h1>Departments</h1></div></section>
<section class="section"><div class="container card-grid">
    <?php foreach ($departments as $department): ?>
        <article class="showcase-card" style="--department-color: <?= esc($department['color_hex'], 'css') ?>">
            <?php if (! empty($department['featured_media_id'])): ?><img src="<?= esc(public_site_path('media/' . $department['featured_media_id'] . '/thumbnail')) ?>" alt="<?= esc($department['name']) ?>"><?php endif ?>
            <div><h2><a href="<?= esc($department['href']) ?>"><?= esc($department['name']) ?></a></h2><?php if (! empty($department['summary'])): ?><p><?= esc($department['summary']) ?></p><?php endif ?></div>
        </article>
    <?php endforeach ?>
    <?php if ($departments === []): ?><p>Published department information will be available soon.</p><?php endif ?>
</div></section>
<?= $this->endSection() ?>
