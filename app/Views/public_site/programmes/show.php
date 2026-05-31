<?= $this->extend('layouts/public') ?>
<?= $this->section('content') ?>
<section class="page-header department-header" style="--department-color: <?= esc($programme['department_color'], 'css') ?>"><div class="container narrow"><p class="eyebrow"><?= esc($programme['department']['name']) ?></p><h1><?= esc($programme['name']) ?></h1><?php if (! empty($programme['summary'])): ?><p class="lead"><?= esc($programme['summary']) ?></p><?php endif ?></div></section>
<section class="section"><div class="container narrow content-stack">
    <?php if (! empty($programme['featured_media_id'])): ?><img class="detail-image" src="<?= esc(public_site_path('media/' . $programme['featured_media_id'] . '/large')) ?>" alt="<?= esc($programme['name']) ?>"><?php endif ?>
    <?php if (! empty($programme['body'])): ?><div><h2>Overview</h2><p><?= nl2br(esc($programme['body'])) ?></p></div><?php endif ?>
    <?php if (! empty($programme['duration_explanation']) || ! empty($programme['duration_years'])): ?><div><h2>Duration</h2><p><?= esc($programme['duration_explanation'] ?: $programme['duration_years'] . ' year(s)') ?></p></div><?php endif ?>
    <?php if (! empty($programme['award_type'])): ?><div><h2>Award type</h2><p><?= esc($programme['award_type']) ?></p></div><?php endif ?>
    <?php if (! empty($programme['entry_requirements'])): ?><div><h2>Entry requirements</h2><p><?= nl2br(esc($programme['entry_requirements'])) ?></p></div><?php endif ?>
    <?php if (! empty($programme['career_opportunities'])): ?><div><h2>Career opportunities</h2><p><?= nl2br(esc($programme['career_opportunities'])) ?></p></div><?php endif ?>
    <div><h2>Admission availability</h2><p><?= esc(ucwords(str_replace('_', ' ', $programme['admission_status']))) ?></p></div>
</div></section>
<?= $this->endSection() ?>
