<?= $this->extend('layouts/public') ?>
<?= $this->section('content') ?>
<section class="page-header"><div class="container narrow"><p class="eyebrow">Applicant portal</p><h1>Start your application</h1><p class="lead">You are applying to <?= esc((string) $settings['site_title']) ?>.</p></div></section>
<section class="section"><div class="container narrow content-stack">
    <div class="notice"><strong>Admissions portal readiness</strong><p>The secure applicant registration and application form will open when this school's configured admission cycle is available.</p></div>
    <p>Applicant access will support an email address or Nigerian mobile phone number. Always begin from this school's website so your application stays within the correct institution.</p>
    <p><a class="button" href="<?= esc(public_site_url('admissions')) ?>">Return to admission information</a></p>
</div></section>
<?= $this->endSection() ?>
