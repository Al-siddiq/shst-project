<?= $this->extend('layouts/tenant_admin') ?><?= $this->section('content') ?>
<h1>Admissions operations</h1>
<p>Notification retries only re-queue tenant-scoped outbox intents. Actual delivery, payment reconciliation, and student conversion remain outside Block 3.</p>
<section class="admin-grid"><?php foreach ($outboxStatusCounts as $status => $count): ?><article class="admin-card"><strong><?= esc($count) ?></strong><br><?= esc($status) ?> outbox</article><?php endforeach ?></section>
<h2>Recent notification outbox</h2>
<?php if ($recentOutbox === []): ?><p>No notification intents are currently queued.</p><?php else: ?><table><thead><tr><th>Event</th><th>Channel</th><th>Recipient</th><th>Status</th><th>Attempts</th><th>Action</th></tr></thead><tbody><?php foreach ($recentOutbox as $row): ?><tr><td><?= esc($row['event_name']) ?></td><td><?= esc($row['channel']) ?></td><td><?= esc($row['recipient']) ?></td><td><?= esc($row['status']) ?></td><td><?= esc($row['attempts'] ?? 0) ?></td><td><?php if (($row['status'] ?? '') !== 'sent'): ?><form method="post" action="<?= site_url('tenant/admissions/operations/outbox/' . $row['id'] . '/retry') ?>"><?= csrf_field() ?><button type="submit">Retry</button></form><?php else: ?>Sent<?php endif ?></td></tr><?php endforeach ?></tbody></table><?php endif ?>
<h2>Recent admissions audit</h2>
<?php if ($recentAuditLogs === []): ?><p>No admissions audit events found for this tenant.</p><?php else: ?><ul><?php foreach ($recentAuditLogs as $log): ?><li><strong><?= esc($log['action']) ?></strong> — <?= esc($log['summary'] ?? '') ?> <small><?= esc($log['created_at'] ?? '') ?></small></li><?php endforeach ?></ul><?php endif ?>
<?= $this->endSection() ?>
