<?php

namespace App\Services\Admissions;

use App\Models\Tenant\Admissions\AdmissionNotificationOutboxModel;
use CodeIgniter\I18n\Time;
use InvalidArgumentException;
use RuntimeException;

/** Records a transactional notification intent; provider delivery happens after commit. */
class AdmissionNotificationDispatcher
{
    public function queue(string $eventName, ?string $recipient, array $payload, string $channel = 'email', ?string $idempotencyKey = null): int
    {
        $eventName = trim($eventName);
        $channel = trim($channel);
        $recipient = $recipient === null ? null : trim($recipient);
        if ($eventName === '' || ! in_array($channel, ['email', 'in_app'], true)) {
            throw new InvalidArgumentException('A valid notification event and channel are required.');
        }
        if ($channel === 'email' && ($recipient === null || filter_var($recipient, FILTER_VALIDATE_EMAIL) === false)) {
            throw new InvalidArgumentException('Email notification intents require a valid recipient.');
        }

        $idempotencyKey ??= hash('sha256', $eventName . '|' . $channel . '|' . ($recipient ?? '') . '|' . json_encode($payload));
        $model = new AdmissionNotificationOutboxModel();
        $existing = $model->where('channel', $channel)->where('idempotency_key', $idempotencyKey)->first();
        if ($existing !== null) {
            return (int) $existing['id'];
        }

        $id = $model->insert([
            'event_name' => $eventName,
            'channel' => $channel,
            'recipient' => $recipient,
            'payload_json' => json_encode($payload, JSON_UNESCAPED_SLASHES),
            'idempotency_key' => $idempotencyKey,
            'status' => 'pending',
            'available_at' => Time::now()->toDateTimeString(),
            'attempts' => 0,
        ], true);

        if (! is_int($id) && ! ctype_digit((string) $id)) {
            throw new RuntimeException('Required notification intent could not be recorded.');
        }

        return (int) $id;
    }
}
