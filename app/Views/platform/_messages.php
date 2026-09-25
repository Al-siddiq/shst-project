<?php if (session('message')): ?><div class="notice" role="status"><?= esc(session('message')) ?></div><?php endif ?>
<?php foreach (session('errors') ?? [] as $error): ?><div class="error" role="alert"><?= esc($error) ?></div><?php endforeach ?>
