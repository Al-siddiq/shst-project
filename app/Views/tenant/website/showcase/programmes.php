<?= $this->extend('layouts/tenant_admin') ?>
<?= $this->section('content') ?>
<?php $selectedProfile ??= null; ?>
<h1>Programme showcase</h1>
<p>Edit public programme copy without changing the Block 1 academic programme record.</p>
<?php if (session('message')): ?><p><?= esc(session('message')) ?></p><?php endif ?>
<?php foreach (session('errors') ?? [] as $error): ?><p><?= esc($error) ?></p><?php endforeach ?>
<form method="post" action="<?= site_url('tenant/website/showcase/programmes') ?>">
    <?= csrf_field() ?>
    <label>Programme <select name="programme_id" required><?php foreach ($programmes as $programme): ?><option value="<?= esc($programme['id']) ?>"<?= (int) old('programme_id', $selectedProfile['programme_id'] ?? 0) === (int) $programme['id'] ? ' selected' : '' ?>><?= esc($programme['name']) ?></option><?php endforeach ?></select></label><br>
    <label>Public slug <input name="slug" value="<?= esc(old('slug', $selectedProfile['slug'] ?? '')) ?>" required pattern="[a-z0-9-]+"></label><br>
    <label>Summary <textarea name="summary"><?= esc(old('summary', $selectedProfile['summary'] ?? '')) ?></textarea></label><br>
    <label>Description <textarea name="body"><?= esc(old('body', $selectedProfile['body'] ?? '')) ?></textarea></label><br>
    <label>Entry requirements <textarea name="entry_requirements"><?= esc(old('entry_requirements', $selectedProfile['entry_requirements'] ?? '')) ?></textarea></label><br>
    <label>Career opportunities <textarea name="career_opportunities"><?= esc(old('career_opportunities', $selectedProfile['career_opportunities'] ?? '')) ?></textarea></label><br>
    <label>Duration explanation <input name="duration_explanation" value="<?= esc(old('duration_explanation', $selectedProfile['duration_explanation'] ?? '')) ?>"></label><br>
    <label>Award type <input name="award_type" value="<?= esc(old('award_type', $selectedProfile['award_type'] ?? '')) ?>"></label><br>
    <label>Admission availability <select name="admission_status"><?php foreach (['not_specified', 'open', 'closed'] as $admissionStatus): ?><option<?= old('admission_status', $selectedProfile['admission_status'] ?? 'not_specified') === $admissionStatus ? ' selected' : '' ?>><?= esc($admissionStatus) ?></option><?php endforeach ?></select></label><br>
    <label>Public featured media ID <input name="featured_media_id" value="<?= esc(old('featured_media_id', $selectedProfile['featured_media_id'] ?? '')) ?>" inputmode="numeric"></label><br>
    <label>SEO title <input name="seo_title" value="<?= esc(old('seo_title', $selectedProfile['seo_title'] ?? '')) ?>"></label><br>
    <label>SEO description <textarea name="seo_description"><?= esc(old('seo_description', $selectedProfile['seo_description'] ?? '')) ?></textarea></label><br>
    <label>Status <select name="status"><?php foreach (['draft', 'published', 'archived'] as $status): ?><option<?= old('status', $selectedProfile['status'] ?? 'draft') === $status ? ' selected' : '' ?>><?= esc($status) ?></option><?php endforeach ?></select></label><br>
    <button type="submit">Save programme profile</button>
</form>
<h2>Existing public profiles</h2>
<ul><?php foreach ($profiles as $profile): ?><li><a href="<?= site_url('tenant/website/showcase/programmes') . '?profile=' . esc($profile['id']) ?>"><?= esc($profile['slug']) ?></a> — <?= esc($profile['status']) ?></li><?php endforeach ?></ul>
<?= $this->endSection() ?>
