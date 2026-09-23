<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Platform Admin</title><style>body{font-family:system-ui,sans-serif;margin:0;color:#172033}.platform-nav{display:flex;gap:1rem;padding:1rem;background:#111827}.platform-nav a{color:#fff}.platform-main{max-width:70rem;margin:auto;padding:1rem}label{display:block;margin:1rem 0}input,select,textarea,button{max-width:100%;padding:.65rem}textarea{width:100%;min-height:7rem}.support-warning{border:3px solid #b45309;background:#fffbeb;padding:1rem;margin:1rem 0}.notice{background:#ecfdf5;padding:.75rem}.error{background:#fef2f2;color:#991b1b;padding:.75rem}</style></head>
<body data-layout="platform-admin">
    <nav class="platform-nav" aria-label="Platform administration"><a href="<?= site_url('platform/support') ?>">Support access</a></nav>
    <main class="platform-main"><?= $this->renderSection('content') ?></main>
</body>
</html>
