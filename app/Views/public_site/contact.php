<?= $this->extend('layouts/public') ?>
<?= $this->section('content') ?>
<section class="page-header"><div class="container narrow"><p class="eyebrow">Get in touch</p><h1>Contact us</h1></div></section>
<section class="section">
    <div class="container narrow contact-card">
        <?php if (! empty($settings['address'])): ?><div><strong>Address</strong><p><?= esc($settings['address']) ?></p></div><?php endif ?>
        <?php if (! empty($settings['contact_phone'])): ?><div><strong>Phone</strong><p><a href="tel:<?= esc($settings['contact_phone']) ?>"><?= esc($settings['contact_phone']) ?></a></p></div><?php endif ?>
        <?php if (! empty($settings['contact_phone_alt'])): ?><div><strong>Alternative phone</strong><p><a href="tel:<?= esc($settings['contact_phone_alt']) ?>"><?= esc($settings['contact_phone_alt']) ?></a></p></div><?php endif ?>
        <?php if (! empty($settings['contact_email'])): ?><div><strong>Email</strong><p><a href="mailto:<?= esc($settings['contact_email']) ?>"><?= esc($settings['contact_email']) ?></a></p></div><?php endif ?>
        <?php if (empty($settings['address']) && empty($settings['contact_phone']) && empty($settings['contact_email'])): ?><p>Contact information will be available soon.</p><?php endif ?>
    </div>
</section>
<?= $this->endSection() ?>
