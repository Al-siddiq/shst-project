<?= $this->extend('layouts/public') ?>
<?= $this->section('content') ?>
<section class="page-header"><div class="container narrow"><p class="eyebrow">Applicant portal</p><h1>Start your application</h1><p class="lead">You are applying to <?= esc((string) $settings['site_title']) ?>.</p></div></section>
<section class="section"><div class="container narrow content-stack">
    <div class="notice"><strong>Secure applicant access</strong><p>Sign in or register with an email address or Nigerian mobile number, then continue to your tenant-owned applicant dashboard.</p></div>
    <p><a class="button" href="<?= esc($authLinks['login']) ?>">Sign in</a> <a class="button button-outline" href="<?= esc($authLinks['register']) ?>">Register</a> <a href="<?= esc($authLinks['dashboard']) ?>">Go to dashboard</a></p>
    <h2>Open programmes</h2>
    <?php if ($openProgrammes === []): ?><p>No programme is currently open for application.</p><?php else: ?><ul><?php foreach ($openProgrammes as $programme): ?><li><?= esc($programme['programme_name']) ?> — <a href="<?= esc(public_site_url('admission_programme', [$programme['id']])) ?>">view requirements</a></li><?php endforeach ?></ul><?php endif ?>
    <p>Always begin from this school's website so your application stays within the correct institution.</p>
</div></section>
<?= $this->endSection() ?>
