<?= $this->extend('layouts/tenant_admin') ?><?= $this->section('content') ?>
<header class="page-head"><p class="eyebrow">Block 1 administration</p><h1><?= esc($profile['institution_name'] ?? 'School administration') ?></h1><p>Institution configuration, access, branding, website, and admissions operations.</p></header>
<?= $this->include('tenant/admin/_messages') ?>
<div class="metric-grid"><?php foreach ($metrics as $label => $value): ?><article class="metric"><strong><?= esc((string) $value) ?></strong><span><?= esc(ucwords($label)) ?></span></article><?php endforeach ?></div>
<section class="panel"><h2>Available administration</h2><div class="action-grid"><?php foreach ($navigation as $item): ?><a class="action-card" href="<?= site_url(ltrim($item['route'],'/')) ?>"><strong><?= esc($item['label']) ?></strong><span>Open module →</span></a><?php endforeach ?></div></section>
<?= $this->endSection() ?>
