<?= $this->extend('layouts/public') ?><?= $this->section('content') ?>
<section class="page-header"><div class="container narrow"><p class="eyebrow">Admissions</p><h1>Published admission lists</h1><p class="lead">Only officially published admission lists from this school are shown.</p></div></section>
<section class="section"><div class="container narrow content-stack"><?php if($publications === []): ?><p>No admission list has been published yet.</p><?php else: ?><ul><?php foreach($publications as $publication): ?><li><a href="<?= public_site_url('admission_list', [$publication['public_token']]) ?>"><?= esc($publication['title']) ?></a> — version <?= esc((string) $publication['version_number']) ?></li><?php endforeach ?></ul><?php endif ?></div></section>
<?= $this->endSection() ?>
