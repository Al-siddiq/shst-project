<?php

namespace App\Services\Admissions;

use InvalidArgumentException;

/** Normalizes applicant login identifiers before Shield identity lookup. */
class ApplicantIdentityNormalizer
{
    /** @return array{type: 'email'|'phone', value: string} */
    public function normalize(string $identifier): array
    {
        $identifier = trim($identifier);
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false) {
            return ['type' => 'email', 'value' => mb_strtolower($identifier)];
        }

        return ['type' => 'phone', 'value' => $this->normalizeNigerianPhone($identifier)];
    }

    public function normalizeNigerianPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', trim($phone)) ?? '';
        if (str_starts_with($digits, '234')) {
            $digits = substr($digits, 3);
        } elseif (str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        // Nigerian mobile numbers are stored once as E.164 for safe lookup.
        if (! preg_match('/^[789][01]\d{8}$/', $digits)) {
            throw new InvalidArgumentException('Enter a valid Nigerian mobile phone number.');
        }

        return '+234' . $digits;
    }
}
