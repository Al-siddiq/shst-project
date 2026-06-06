<?= $this->extend('layouts/public') ?>
<?= $this->section('content') ?>
<?php $applicationUrl = public_external_url($admissions['application_url'] ?? $settings['application_info_url'] ?? null); ?>
<section class="page-header"><div class="container narrow"><p class="eyebrow">Prospective applicants</p><h1><?= esc($admissions['title'] ?? 'Admissions') ?></h1><?php if (! empty($admissions['summary'])): ?><p class="lead"><?= esc($admissions['summary']) ?></p><?php endif ?></div></section>
<section class="section"><div class="container narrow content-stack">
    <?php if ($admissions === null): ?><p>Admission information will be available soon.</p><?php else: ?>
        <div><h2>Admission status</h2><p><?= esc(ucfirst($admissions['admission_status'])) ?></p></div>
        <?php foreach (['body' => 'Admission information', 'requirements_body' => 'General requirements', 'application_fee_note' => 'Application fee note', 'screening_information' => 'Screening information', 'required_documents' => 'Required documents', 'important_dates' => 'Important dates', 'how_to_apply_body' => 'How to apply'] as $field => $heading): ?>
            <?php if (! empty($admissions[$field])): ?><div><h2><?= esc($heading) ?></h2><p><?= nl2br(esc($admissions[$field])) ?></p></div><?php endif ?>
        <?php endforeach ?>
        <?php if ($applicationUrl !== null): ?><p><a class="button" href="<?= esc($applicationUrl) ?>"><?= esc($admissions['application_link_label'] ?: 'Open application portal') ?></a></p><?php else: ?><p class="notice">Applications portal link will be available when configured.</p><?php endif ?>
    <?php endif ?>
    <?php if ($activeCycle !== null): ?><div class="notice"><h2><?= esc($activeCycle['title']) ?></h2><p>Applications are open from <?= esc($activeCycle['opens_at']) ?> until <?= esc($activeCycle['closes_at']) ?>.</p><?php if (! empty($activeCycle['instructions'])): ?><p><?= nl2br(esc($activeCycle['instructions'])) ?></p><?php endif ?><p><a class="button" href="<?= esc(public_site_url('admission_programmes')) ?>">View open programmes</a></p></div><?php endif ?>
    <?php if ($openProgrammes !== []): ?><div><h2>Currently open programmes</h2><ul><?php foreach ($openProgrammes as $programme): ?><li><a href="<?= esc(public_site_url('admission_programme', [$programme['id']])) ?>"><?= esc($programme['programme_name']) ?></a></li><?php endforeach ?></ul></div><?php endif ?>
    <?php if (($publishedLists ?? []) !== []): ?><div><h2>Published admission lists</h2><ul><?php foreach ($publishedLists as $list): ?><li><a href="<?= esc(public_site_url('admission_list', [$list['public_token']])) ?>"><?= esc($list['title']) ?></a></li><?php endforeach ?></ul><p><a href="<?= esc(public_site_url('admission_lists')) ?>">View all admission lists</a></p></div><?php endif ?>
</div></section>
<?= $this->endSection() ?>
