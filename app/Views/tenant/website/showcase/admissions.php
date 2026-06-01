<?= $this->extend('layouts/tenant_admin') ?>
<?= $this->section('content') ?>
<h1>Public admission information</h1>
<p>This page publishes guidance and an optional portal link only. It does not accept or process applications.</p>
<?php if (session('message')): ?><p><?= esc(session('message')) ?></p><?php endif ?>
<?php foreach (session('errors') ?? [] as $error): ?><p><?= esc($error) ?></p><?php endforeach ?>
<form method="post" action="<?= site_url('tenant/website/showcase/admissions') ?>">
    <?= csrf_field() ?>
    <label>Title <input name="title" value="<?= esc(old('title', $admissions['title'] ?? 'Admissions')) ?>" required></label><br>
    <label>Summary <textarea name="summary"><?= esc(old('summary', $admissions['summary'] ?? '')) ?></textarea></label><br>
    <label>Admission status <select name="admission_status"><?php foreach (['closed', 'open'] as $admissionStatus): ?><option<?= old('admission_status', $admissions['admission_status'] ?? 'closed') === $admissionStatus ? ' selected' : '' ?>><?= esc($admissionStatus) ?></option><?php endforeach ?></select></label><br>
    <label>Admission information <textarea name="body"><?= esc(old('body', $admissions['body'] ?? '')) ?></textarea></label><br>
    <label>General requirements <textarea name="requirements_body"><?= esc(old('requirements_body', $admissions['requirements_body'] ?? '')) ?></textarea></label><br>
    <label>Application fee note <textarea name="application_fee_note"><?= esc(old('application_fee_note', $admissions['application_fee_note'] ?? '')) ?></textarea></label><br>
    <label>Screening information <textarea name="screening_information"><?= esc(old('screening_information', $admissions['screening_information'] ?? '')) ?></textarea></label><br>
    <label>Required documents <textarea name="required_documents"><?= esc(old('required_documents', $admissions['required_documents'] ?? '')) ?></textarea></label><br>
    <label>Important dates <textarea name="important_dates"><?= esc(old('important_dates', $admissions['important_dates'] ?? '')) ?></textarea></label><br>
    <label>How to apply <textarea name="how_to_apply_body"><?= esc(old('how_to_apply_body', $admissions['how_to_apply_body'] ?? '')) ?></textarea></label><br>
    <label>Application link label <input name="application_link_label" value="<?= esc(old('application_link_label', $admissions['application_link_label'] ?? '')) ?>"></label><br>
    <label>Application URL <input name="application_url" value="<?= esc(old('application_url', $admissions['application_url'] ?? '')) ?>" type="url"></label><br>
    <label>SEO title <input name="seo_title" value="<?= esc(old('seo_title', $admissions['seo_title'] ?? '')) ?>"></label><br>
    <label>SEO description <textarea name="seo_description"><?= esc(old('seo_description', $admissions['seo_description'] ?? '')) ?></textarea></label><br>
    <label>Status <select name="status"><?php foreach (['draft', 'published', 'archived'] as $status): ?><option<?= old('status', $admissions['status'] ?? 'draft') === $status ? ' selected' : '' ?>><?= esc($status) ?></option><?php endforeach ?></select></label><br>
    <button type="submit">Save admission information</button>
</form>
<?= $this->endSection() ?>
