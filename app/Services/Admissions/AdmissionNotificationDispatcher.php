<?php

namespace App\Services\Admissions;

use App\Models\Tenant\Admissions\AdmissionNotificationOutboxModel;
use CodeIgniter\I18n\Time;
use InvalidArgumentException;
use RuntimeException;

/** Records a transactional notification intent; provider delivery happens after commit. */
class AdmissionNotificationDispatcher
{
    /** Queue both required admissions channels for one tenant applicant. */
    public function queueApplicant(string $eventName, int $applicationId, array $payload): array
    {
        $db = db_connect();
        $profile = $db->table('applicant_applications a')->select('p.user_id,COALESCE(p.email,b.email) AS email',false)
            ->join('applicant_profiles p', 'p.id=a.applicant_profile_id AND p.tenant_id=a.tenant_id')
            ->join('application_biodata_drafts b','b.applicant_application_id=a.id AND b.tenant_id=a.tenant_id','left')
            ->where('a.id', $applicationId)->get()->getRowArray();
        if ($profile === null || empty($profile['user_id'])) throw new InvalidArgumentException('Notification recipient could not be resolved for the tenant applicant.');
        $payload = array_merge($payload, ['application_id'=>$applicationId, 'user_id'=>(int)$profile['user_id']]);
        $ids=['in_app'=>$this->queue($eventName, null, $payload, 'in_app', $eventName . ':in-app:' . $applicationId . ':' . ($payload['submission_version'] ?? $payload['offer_id'] ?? 'current'))];
        if (! empty($profile['email'])) $ids['email']=$this->queue($eventName, (string)$profile['email'], $payload, 'email', $eventName . ':email:' . $applicationId . ':' . ($payload['submission_version'] ?? $payload['offer_id'] ?? 'current'));
        return $ids;
    }
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
