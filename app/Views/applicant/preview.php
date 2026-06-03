<?= $this->extend('layouts/public') ?><?= $this->section('content') ?>
<section class="page-header"><div class="container narrow"><p class="eyebrow">Application preview</p><h1>Review before submission</h1><p class="lead">Final submission locks this application for admissions review.</p></div></section>
<section class="section"><div class="container narrow content-stack"><?php if (session('message')): ?><p class="notice"><?= esc(session('message')) ?></p><?php endif ?>
<p><strong>Status:</strong> <?= esc($application['status']) ?><?php if (! empty($application['application_number'])): ?> — <?= esc($application['application_number']) ?><?php endif ?></p>
<h2>Completion</h2><?php if ($complete): ?><p class="notice">Complete and ready for submission.</p><?php else: ?><ul><?php foreach ($missing as $item): ?><li><?= esc($item) ?></li><?php endforeach ?></ul><?php endif ?>
<h2>Biodata</h2><p><?= esc(($biodata['surname'] ?? '').' '.($biodata['first_name'] ?? '')) ?> · <?= esc($biodata['email'] ?? '') ?> · <?= esc($biodata['phone_e164'] ?? '') ?></p>
<h2>O'Level</h2><p><?= esc((string) ($counts['sittings'] ?? 0)) ?> sitting(s), <?= esc((string) ($counts['results'] ?? 0)) ?> result row(s).</p>
<h2>Documents</h2><p><?= esc((string) ($counts['documents'] ?? 0)) ?> uploaded document(s).</p>
<?php if ($application['status'] === 'draft'): ?><form method="post" action="<?= site_url('applicant/applications/'.$application['public_token'].'/submit') ?>"><?= csrf_field() ?><button <?= $complete ? '' : 'disabled' ?>>Submit application</button></form><?php endif ?>
<p><a href="<?= site_url('applicant/applications/'.$application['public_token']) ?>">Return to edit</a></p></div></section>
<?= $this->endSection() ?>
