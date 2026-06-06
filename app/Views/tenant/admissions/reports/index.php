<?= $this->extend('layouts/tenant_admin') ?><?= $this->section('content') ?>
<h1>Admissions reports</h1>
<p>Phase 8 exports are tenant-scoped and limited to safe operational fields. Private notes, private document paths, payment internals, and student-conversion records are not exported.</p>
<section class="admin-grid">
    <article class="admin-card"><strong><?= esc(array_sum($pipeline)) ?></strong><br>Total tracked applications</article>
    <article class="admin-card"><strong><?= esc($decisions['pending_approval']) ?></strong><br>Decisions pending approval</article>
    <article class="admin-card"><strong><?= esc($acceptance['accepted']) ?></strong><br>Accepted offers</article>
    <article class="admin-card"><strong><?= esc($publications['published_lists']) ?></strong><br>Published admission lists</article>
</section>
<h2>CSV exports</h2>
<ul><?php foreach ($reports as $key => $label): ?><li><a href="<?= site_url('tenant/admissions/reports/export/' . $key) ?>"><?= esc($label) ?> CSV</a></li><?php endforeach ?></ul>
<h2>Pipeline status</h2>
<ul><?php foreach ($pipeline as $status => $count): ?><li><?= esc($status) ?>: <?= esc($count) ?></li><?php endforeach ?></ul>
<h2>Decision summary</h2>
<ul><?php foreach ($decisions as $status => $count): ?><li><?= esc($status) ?>: <?= esc($count) ?></li><?php endforeach ?></ul>
<h2>Acceptance and clearance summary</h2>
<ul><?php foreach ($acceptance as $status => $count): ?><li><?= esc($status) ?>: <?= esc($count) ?></li><?php endforeach ?></ul>
<?= $this->endSection() ?>
