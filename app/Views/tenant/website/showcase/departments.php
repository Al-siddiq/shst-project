<?= $this->extend('layouts/tenant_admin') ?>
<?= $this->section('content') ?>
<?php $selectedProfile ??= null; ?>
<h1>Department showcase</h1>
<p>Edit public department copy. Publishing requires the separate <code>website.content.publish</code> authority.</p>
<?php if (session('message')): ?><p><?= esc(session('message')) ?></p><?php endif ?>
<?php foreach (session('errors') ?? [] as $error): ?><p><?= esc($error) ?></p><?php endforeach ?>
<form method="post" action="<?= site_url('tenant/website/showcase/departments') ?>">
    <?= csrf_field() ?>
    <label>Department <select name="department_id" required><?php foreach ($departments as $department): ?><option value="<?= esc($department['id']) ?>"<?= (int) old('department_id', $selectedProfile['department_id'] ?? 0) === (int) $department['id'] ? ' selected' : '' ?>><?= esc($department['name']) ?></option><?php endforeach ?></select></label><br>
    <label>Public slug <input name="slug" value="<?= esc(old('slug', $selectedProfile['slug'] ?? '')) ?>" required pattern="[a-z0-9-]+"></label><br>
    <label>Summary <textarea name="summary"><?= esc(old('summary', $selectedProfile['summary'] ?? '')) ?></textarea></label><br>
    <label>Description <textarea name="body"><?= esc(old('body', $selectedProfile['body'] ?? '')) ?></textarea></label><br>
    <label>Public featured media ID <input name="featured_media_id" value="<?= esc(old('featured_media_id', $selectedProfile['featured_media_id'] ?? '')) ?>" inputmode="numeric"></label><br>
    <label>SEO title <input name="seo_title" value="<?= esc(old('seo_title', $selectedProfile['seo_title'] ?? '')) ?>"></label><br>
    <label>SEO description <textarea name="seo_description"><?= esc(old('seo_description', $selectedProfile['seo_description'] ?? '')) ?></textarea></label><br>
    <label>Status <select name="status"><?php foreach (['draft', 'published', 'archived'] as $status): ?><option<?= old('status', $selectedProfile['status'] ?? 'draft') === $status ? ' selected' : '' ?>><?= esc($status) ?></option><?php endforeach ?></select></label><br>
    <button type="submit">Save department profile</button>
</form>
<h2>Existing public profiles</h2>
<ul><?php foreach ($profiles as $profile): ?><li><a href="<?= site_url('tenant/website/showcase/departments') . '?profile=' . esc($profile['id']) ?>"><?= esc($profile['slug']) ?></a> — <?= esc($profile['status']) ?></li><?php endforeach ?></ul>
<?= $this->endSection() ?>
