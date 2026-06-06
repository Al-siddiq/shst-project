<?= $this->extend('layouts/tenant_admin') ?><?= $this->section('content') ?>
<h1>Admissions dashboard</h1><p>Phase 1 setup summary for the active tenant.</p>
<section class="admin-grid"><article class="admin-card"><strong><?= esc($cycleCount) ?></strong><br>Admission cycles</article><article class="admin-card"><strong><?= esc($openProgrammeCount) ?></strong><br>Open programmes</article><article class="admin-card"><strong><?= esc($requirementCount + $subjectRequirementCount + $documentRequirementCount) ?></strong><br>Configured requirements</article></section>
<h2>Active public cycle</h2><p><?= $activeCycle === null ? 'No active public admission cycle.' : esc($activeCycle['title']) ?></p>
<h2>Setup gaps</h2><?php if ($setupGaps === []): ?><p>Admission setup is ready for public discovery.</p><?php else: ?><ul><?php foreach ($setupGaps as $gap): ?><li><?= esc($gap) ?></li><?php endforeach ?></ul><?php endif ?>
<?= $this->endSection() ?>
