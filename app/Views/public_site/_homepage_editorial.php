<?php
/** Homepage aggregation remains optional: empty tenant sections are omitted cleanly. */
$sections = [
    'announcements' => ['title' => 'Announcements', 'route' => 'announcements'],
    'news' => ['title' => 'Latest news', 'route' => 'news'],
    'calendar' => ['title' => 'Calendar notices', 'route' => 'calendar'],
];
?>
<?php foreach ($sections as $key => $section): ?>
    <?php if ($editorial[$key] !== []): ?>
        <section class="section section-muted"><div class="container">
            <div class="section-heading"><h2><?= esc($section['title']) ?></h2><a class="text-link" href="<?= esc(public_site_url($section['route'])) ?>">View all →</a></div>
            <div class="card-grid"><?= view('public_site/_content_cards', ['items' => $editorial[$key]]) ?></div>
        </div></section>
    <?php endif ?>
<?php endforeach ?>
