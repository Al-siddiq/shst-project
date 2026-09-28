<?= $this->extend('layouts/platform_admin') ?>
<?= $this->section('content') ?>
<section class="platform-panel">
    <h1>Controlled tenant support access</h1>
    <?php if (session('message')): ?><p class="notice"><?= esc(session('message')) ?></p><?php endif ?>
    <?php foreach (session('errors') ?? [] as $error): ?><p class="error"><?= esc($error) ?></p><?php endforeach ?>
    <?php if ($supportContext !== null && $tenant !== null): ?>
        <div class="support-warning" role="status">
            <strong>Read-only support context active</strong>
            <p>Tenant: <?= esc($tenant['school_name']) ?></p>
            <p>Reason: <?= esc($supportContext['reason']) ?></p>
            <p>Expires: <?= esc($supportContext['expires_at']) ?></p>
            <p>This is not impersonation and grants no tenant write permission.</p>
        </div>
        <form method="post" action="<?= site_url('platform/support/end') ?>">
            <?= csrf_field() ?>
            <button type="submit">End support context</button>
        </form>
    <?php else: ?>
        <form method="post" action="<?= site_url('platform/support/start') ?>">
            <?= csrf_field() ?>
            <label>Tenant
                <select name="tenant_id" required>
                    <option value="">Select tenant</option>
                    <?php foreach ($tenants as $item): ?>
                        <option value="<?= esc((string) $item['id']) ?>"><?= esc($item['school_name']) ?> (<?= esc($item['status']) ?>)</option>
                    <?php endforeach ?>
                </select>
            </label>
            <label>Support reason
                <textarea name="reason" minlength="10" maxlength="500" required><?= esc(old('reason')) ?></textarea>
            </label>
            <button type="submit">Enter read-only support context</button>
        </form>
    <?php endif ?>
</section>
<?= $this->endSection() ?>
