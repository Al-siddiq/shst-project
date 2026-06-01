<?= $this->extend('layouts/tenant_admin') ?>
<?= $this->section('content') ?>
<h1><?= esc(ucwords(str_replace('_', ' ', $contentType))) ?> editorial content</h1>
<p><a href="<?= site_url('tenant/website/editorial/' . $contentType . '/form') ?>">Create draft</a></p>
<?php if (session('message')): ?><p><?= esc(session('message')) ?></p><?php endif ?>
<?php foreach (session('errors') ?? [] as $error): ?><p><?= esc($error) ?></p><?php endforeach ?>
<ul>
<?php foreach ($items as $item): ?>
    <li>
        <a href="<?= site_url('tenant/website/editorial/' . $contentType . '/form') . '?item=' . esc($item['id']) ?>"><?= esc($item['title']) ?></a> — <?= esc($item['status']) ?>
        <form method="post" action="<?= site_url('tenant/website/editorial/' . $contentType . '/' . $item['id'] . '/draft') ?>" class="inline-form"><?= csrf_field() ?><button>Draft</button></form>
        <form method="post" action="<?= site_url('tenant/website/editorial/' . $contentType . '/' . $item['id'] . '/publish') ?>" class="inline-form"><?= csrf_field() ?><button>Publish</button></form>
        <form method="post" action="<?= site_url('tenant/website/editorial/' . $contentType . '/' . $item['id'] . '/archive') ?>" class="inline-form"><?= csrf_field() ?><button>Archive</button></form>
        <form method="post" action="<?= site_url('tenant/website/editorial/' . $contentType . '/' . $item['id'] . '/delete') ?>" class="inline-form"><?= csrf_field() ?><button>Delete</button></form>
        <form method="post" action="<?= site_url('tenant/website/editorial/' . $contentType . '/' . $item['id'] . '/schedule') ?>" class="inline-form"><?= csrf_field() ?><input type="datetime-local" name="scheduled_for" required><button>Schedule</button></form>
    </li>
<?php endforeach ?>
</ul>
<?= $this->endSection() ?>
