<?= $this->extend('layouts/public') ?>
<?= $this->section('content') ?>
<section class="page-header"><div class="container"><p class="eyebrow">Updates</p><h1><?= esc($listing['section']['label']) ?></h1></div></section>
<section class="section"><div class="container">
    <div class="card-grid"><?= view('public_site/_content_cards', ['items' => $listing['items']]) ?></div>
    <?php if ($listing['items'] === []): ?><p>Published information will be available soon.</p><?php endif ?>
    <?php if ($listing['totalPages'] > 1): ?>
        <nav class="pagination" aria-label="Pagination">
            <?php for ($page = 1; $page <= $listing['totalPages']; $page++): ?><a<?= $page === $listing['page'] ? ' aria-current="page"' : '' ?> href="?page=<?= esc($page) ?>"><?= esc($page) ?></a><?php endfor ?>
        </nav>
    <?php endif ?>
</div></section>
<?= $this->endSection() ?>
