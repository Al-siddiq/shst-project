<?= $this->extend('layouts/tenant_admin') ?>
<?= $this->section('content') ?>
<?php $selectedItem ??= null; ?>
<h1><?= $selectedItem === null ? 'Create' : 'Edit' ?> <?= esc(str_replace('_', ' ', $contentType)) ?> draft</h1>
<p>Saving always produces a draft. Use the explicit lifecycle actions on the listing page to schedule, publish, archive, or delete content.</p>
<?php foreach (session('errors') ?? [] as $error): ?><p><?= esc($error) ?></p><?php endforeach ?>
<form method="post" action="<?= site_url('tenant/website/editorial/' . $contentType . '/draft') ?>">
    <?= csrf_field() ?>
    <?php if ($selectedItem !== null): ?><input type="hidden" name="id" value="<?= esc($selectedItem['id']) ?>"><?php endif ?>
    <label>Title <input name="title" value="<?= esc(old('title', $selectedItem['title'] ?? '')) ?>" required></label><br>
    <label>Slug <input name="slug" value="<?= esc(old('slug', $selectedItem['slug'] ?? '')) ?>" pattern="[a-z0-9-]+" required></label><br>
    <label>Summary <textarea name="summary"><?= esc(old('summary', $selectedItem['summary'] ?? '')) ?></textarea></label><br>
    <label>Body <textarea name="body" required><?= esc(old('body', $selectedItem['body'] ?? '')) ?></textarea></label><br>
    <label>Featured media ID <input name="featured_media_id" value="<?= esc(old('featured_media_id', $selectedItem['featured_media_id'] ?? '')) ?>" inputmode="numeric"></label><br>
    <label>Category <input name="category" value="<?= esc(old('category', $selectedItem['category'] ?? '')) ?>"></label><br>
    <label>Author display name <input name="author_display_name" value="<?= esc(old('author_display_name', $selectedItem['author_display_name'] ?? '')) ?>"></label><br>
    <label>Related department <select name="related_department_id"><option value="">None</option><?php foreach ($departments as $department): ?><option value="<?= esc($department['id']) ?>"<?= (int) old('related_department_id', $selectedItem['related_department_id'] ?? 0) === (int) $department['id'] ? ' selected' : '' ?>><?= esc($department['name']) ?></option><?php endforeach ?></select></label><br>
    <label>Related programme <select name="related_programme_id"><option value="">None</option><?php foreach ($programmes as $programme): ?><option value="<?= esc($programme['id']) ?>"<?= (int) old('related_programme_id', $selectedItem['related_programme_id'] ?? 0) === (int) $programme['id'] ? ' selected' : '' ?>><?= esc($programme['name']) ?></option><?php endforeach ?></select></label><br>
    <label>Academic session <select name="academic_session_id"><option value="">None</option><?php foreach ($sessions as $session): ?><option value="<?= esc($session['id']) ?>"<?= (int) old('academic_session_id', $selectedItem['academic_session_id'] ?? 0) === (int) $session['id'] ? ' selected' : '' ?>><?= esc($session['name']) ?></option><?php endforeach ?></select></label><br>
    <label>Semester <select name="semester_id"><option value="">None</option><?php foreach ($semesters as $semester): ?><option value="<?= esc($semester['id']) ?>"<?= (int) old('semester_id', $selectedItem['semester_id'] ?? 0) === (int) $semester['id'] ? ' selected' : '' ?>><?= esc($semester['name']) ?></option><?php endforeach ?></select></label><br>
    <label>Announcement type <input name="announcement_type" value="<?= esc(old('announcement_type', $selectedItem['announcement_type'] ?? '')) ?>"></label><br>
    <label>Priority <select name="priority"><?php foreach (['low', 'normal', 'high', 'urgent'] as $priority): ?><option<?= old('priority', $selectedItem['priority'] ?? 'normal') === $priority ? ' selected' : '' ?>><?= esc($priority) ?></option><?php endforeach ?></select></label><br>
    <label>Audience <select name="audience"><?php foreach (['public', 'applicants', 'students', 'staff', 'department', 'programme'] as $audience): ?><option<?= old('audience', $selectedItem['audience'] ?? 'public') === $audience ? ' selected' : '' ?>><?= esc($audience) ?></option><?php endforeach ?></select></label><br>
    <label>Start date <input type="datetime-local" name="event_start_at" value="<?= esc(old('event_start_at', isset($selectedItem['event_start_at']) ? str_replace(' ', 'T', $selectedItem['event_start_at']) : '')) ?>"></label><br>
    <label>End date <input type="datetime-local" name="event_end_at" value="<?= esc(old('event_end_at', isset($selectedItem['event_end_at']) ? str_replace(' ', 'T', $selectedItem['event_end_at']) : '')) ?>"></label><br>
    <label>Visibility <select name="visibility"><?php foreach (['public', 'private'] as $visibility): ?><option<?= old('visibility', $selectedItem['visibility'] ?? 'public') === $visibility ? ' selected' : '' ?>><?= esc($visibility) ?></option><?php endforeach ?></select></label><br>
    <label>SEO title <input name="seo_title" value="<?= esc(old('seo_title', $selectedItem['seo_title'] ?? '')) ?>"></label><br>
    <label>SEO description <textarea name="seo_description"><?= esc(old('seo_description', $selectedItem['seo_description'] ?? '')) ?></textarea></label><br>
    <button type="submit">Save draft</button>
</form>
<?= $this->endSection() ?>
