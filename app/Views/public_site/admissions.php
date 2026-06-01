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
</div></section>
<?= $this->endSection() ?>
