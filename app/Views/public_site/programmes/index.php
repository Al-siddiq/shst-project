<?= $this->extend('layouts/public') ?>
<?= $this->section('content') ?>
<section class="page-header"><div class="container"><p class="eyebrow">Academic opportunities</p><h1>Programmes</h1></div></section>
<section class="section"><div class="container">
    <nav class="filter-row" aria-label="Programme department filters"><a href="<?= esc(public_site_url('programmes')) ?>">All</a><?php foreach ($departments as $department): ?><a href="<?= esc(public_site_url('programmes')) . '?department=' . rawurlencode($department['slug']) ?>"><?= esc($department['name']) ?></a><?php endforeach ?></nav>
    <div class="card-grid">
    <?php foreach ($programmes as $programme): ?>
        <article class="showcase-card" style="--department-color: <?= esc($programme['department_color'], 'css') ?>"><div><p class="eyebrow"><?= esc($programme['department']['name']) ?></p><h2><a href="<?= esc($programme['href']) ?>"><?= esc($programme['name']) ?></a></h2><?php if (! empty($programme['summary'])): ?><p><?= esc($programme['summary']) ?></p><?php endif ?></div></article>
    <?php endforeach ?>
    <?php if ($programmes === []): ?><p>Published programme information will be available soon.</p><?php endif ?>
    </div>
</div></section>
<?= $this->endSection() ?>
