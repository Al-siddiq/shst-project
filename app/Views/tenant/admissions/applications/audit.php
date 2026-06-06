<?= $this->extend('layouts/tenant_admin') ?><?= $this->section('content') ?>
<h1>Admissions audit foundation</h1><p>Recent tenant admissions audit events.</p>
<ul><?php foreach($auditEvents as $event): ?><li><?= esc($event['created_at']) ?> — <?= esc($event['action']) ?> — <?= esc($event['summary'] ?? '') ?></li><?php endforeach ?></ul>
<?= $this->endSection() ?>
