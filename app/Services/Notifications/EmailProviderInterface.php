<?php
namespace App\Services\Notifications;
interface EmailProviderInterface { public function send(string $recipient, string $subject, string $html, array $metadata=[]): string; }
