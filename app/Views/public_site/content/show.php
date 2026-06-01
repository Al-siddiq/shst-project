<?= $this->extend('layouts/public') ?>
<?= $this->section('content') ?>
<section class="page-header"><div class="container narrow"><p class="eyebrow"><?= esc($item['section_label']) ?></p><h1><?= esc($item['title']) ?></h1><?php if (! empty($item['summary'])): ?><p class="lead"><?= esc($item['summary']) ?></p><?php endif ?></div></section>
<section class="section"><article class="container narrow content-stack">
    <?php if (! empty($item['featured_media_id'])): ?><img class="detail-image" src="<?= esc(public_site_path('media/' . $item['featured_media_id'] . '/large')) ?>" alt="<?= esc($item['title']) ?>"><?php endif ?>
    <?php if (! empty($item['effective_published_at'])): ?><p><small>Published <?= esc(date('j M Y', strtotime($item['effective_published_at']))) ?></small></p><?php endif ?>
    <?php if (! empty($item['event_start_at'])): ?><p><strong>Starts:</strong> <?= esc(date('j M Y, g:i a', strtotime($item['event_start_at']))) ?></p><?php endif ?>
    <?php if (! empty($item['event_end_at'])): ?><p><strong>Ends:</strong> <?= esc(date('j M Y, g:i a', strtotime($item['event_end_at']))) ?></p><?php endif ?>
    <p><?= nl2br(esc($item['body'])) ?></p>
</article></section>
<?= $this->endSection() ?>
