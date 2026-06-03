<?php

namespace App\Services\Admissions;

use App\Models\Tenant\Admissions\AdmissionNotificationOutboxModel;
use CodeIgniter\I18n\Time;

/** Records notification intent; delivery workers are outside Block 3 Phase 3. */
class AdmissionNotificationDispatcher
{
    public function queue(string $eventName, ?string $recipient, array $payload, string $channel = 'email'): int
    {
        return (int) (new AdmissionNotificationOutboxModel())->insert([
            'event_name' => $eventName,
            'channel' => $channel,
            'recipient' => $recipient,
            'payload_json' => json_encode($payload, JSON_UNESCAPED_SLASHES),
            'status' => 'pending',
            'available_at' => Time::now()->toDateTimeString(),
            'attempts' => 0,
        ], true);
    }
}
