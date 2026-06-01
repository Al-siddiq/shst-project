<?php
/**
 * Recursively renders tenant-owned menu records prepared by WebsiteMenuService.
 * Keeping recursion in one partial makes nested menus visible without adding
 * database access or routing decisions to the public layout.
 */
?>
<?php foreach ($items as $item): ?>
    <li>
        <a href="<?= esc($item['href']) ?>" target="<?= esc($item['target']) ?>"<?= $item['target'] === '_blank' ? ' rel="noopener noreferrer"' : '' ?>><?= esc($item['label']) ?></a>
        <?php if ($item['children'] !== []): ?>
            <ul><?= view('public_site/_menu_items', ['items' => $item['children']]) ?></ul>
        <?php endif ?>
    </li>
<?php endforeach ?>
