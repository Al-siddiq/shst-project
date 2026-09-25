<?= $this->extend('layouts/platform_admin') ?>
<?= $this->section('content') ?>
<header class="page-head"><div><p class="eyebrow">SaaS operations</p><h1>Schools</h1><p>Create and manage isolated tenant institutions.</p></div></header>
<?= $this->include('platform/_messages') ?>
<div class="split-grid">
  <section class="panel"><h2>Registered schools</h2>
    <?php if ($tenants === []): ?><div class="empty">No schools have been provisioned.</div><?php else: ?>
    <div class="table-wrap"><table><thead><tr><th>School</th><th>Slug</th><th>Status</th><th></th></tr></thead><tbody>
    <?php foreach ($tenants as $tenant): ?><tr><td><strong><?= esc($tenant['school_name']) ?></strong><br><small><?= esc($tenant['official_email'] ?? '') ?></small></td><td><?= esc($tenant['slug']) ?></td><td><span class="badge badge-<?= esc($tenant['status']) ?>"><?= esc(str_replace('_', ' ', $tenant['status'])) ?></span></td><td><a class="button secondary" href="<?= site_url('platform/tenants/' . $tenant['id']) ?>">Manage</a></td></tr><?php endforeach ?>
    </tbody></table></div><?php endif ?>
  </section>
  <section class="panel"><h2>Create school</h2><form method="post" action="<?= site_url('platform/tenants') ?>"><?= csrf_field() ?>
    <label>School name<input name="school_name" value="<?= esc(old('school_name')) ?>" required maxlength="200"></label>
    <label>Short name<input name="short_name" value="<?= esc(old('short_name')) ?>" maxlength="120"></label>
    <label>Tenant slug<input name="slug" value="<?= esc(old('slug')) ?>" required pattern="[A-Za-z0-9_-]+" maxlength="150"><small>Used for controlled fallback URLs; cannot duplicate another school.</small></label>
    <label>Official email<input type="email" name="official_email" value="<?= esc(old('official_email')) ?>"></label>
    <label>Official phone<input name="official_phone" value="<?= esc(old('official_phone')) ?>"></label>
    <button type="submit">Create pending school</button>
  </form></section>
</div>
<?= $this->endSection() ?>
