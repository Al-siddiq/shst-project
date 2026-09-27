<?php
namespace App\Services\Notifications;
use RuntimeException;
final class CodeIgniterEmailProvider implements EmailProviderInterface
{
    public function send(string $recipient, string $subject, string $html, array $metadata=[]): string
    {
        $email=service('email'); $email->setTo($recipient); $email->setSubject($subject); $email->setMessage($html);
        if (! $email->send()) throw new RuntimeException('Email provider rejected the message.');
        return 'ci-email-' . bin2hex(random_bytes(8));
    }
}
