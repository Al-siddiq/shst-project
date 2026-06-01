<?= $this->extend('layouts/public') ?>
<?= $this->section('content') ?>
<section class="page-header department-header" style="--department-color: <?= esc($department['color_hex'], 'css') ?>"><div class="container narrow"><p class="eyebrow">Department</p><h1><?= esc($department['name']) ?></h1><?php if (! empty($department['summary'])): ?><p class="lead"><?= esc($department['summary']) ?></p><?php endif ?></div></section>
<section class="section"><div class="container narrow content-stack">
    <?php if (! empty($department['featured_media_id'])): ?><img class="detail-image" src="<?= esc(public_site_path('media/' . $department['featured_media_id'] . '/large')) ?>" alt="<?= esc($department['name']) ?>"><?php endif ?>
    <?php if (! empty($department['body'])): ?><p><?= nl2br(esc($department['body'])) ?></p><?php endif ?>
    <div><h2>Programmes</h2>
        <?php foreach ($department['programmes'] as $programme): ?><p><a class="text-link" href="<?= esc($programme['href']) ?>"><?= esc($programme['name']) ?> →</a></p><?php endforeach ?>
        <?php if ($department['programmes'] === []): ?><p>Published programme information will be available soon.</p><?php endif ?>
    </div>
</div></section>
<?= $this->endSection() ?>
