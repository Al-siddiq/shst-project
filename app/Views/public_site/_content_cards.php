<?php
/** Shared editorial cards for listings and homepage aggregation. */
?>
<?php foreach ($items as $item): ?>
    <article class="editorial-card">
        <?php if (! empty($item['featured_media_id'])): ?><img src="<?= esc(public_site_path('media/' . $item['featured_media_id'] . '/thumbnail')) ?>" alt="<?= esc($item['title']) ?>"><?php endif ?>
        <div>
            <p class="eyebrow"><?= esc($item['section_label']) ?></p>
            <h3><a href="<?= esc($item['href']) ?>"><?= esc($item['title']) ?></a></h3>
            <?php if (! empty($item['summary'])): ?><p><?= esc($item['summary']) ?></p><?php endif ?>
            <?php if (! empty($item['event_start_at'])): ?><p><small><?= esc(date('j M Y, g:i a', strtotime($item['event_start_at']))) ?></small></p><?php endif ?>
        </div>
    </article>
<?php endforeach ?>
