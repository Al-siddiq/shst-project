<?php

namespace Tests\Unit;

use App\Services\Admissions\AdmissionNotificationDispatcher;
use CodeIgniter\Test\CIUnitTestCase;
use InvalidArgumentException;

final class Phase2MutationPolicyTest extends CIUnitTestCase
{
    public function testEmailIntentRequiresRecipient(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new AdmissionNotificationDispatcher())->queue('admissions.test', null, [], 'email');
    }

    public function testEmailIntentRejectsMalformedRecipient(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new AdmissionNotificationDispatcher())->queue('admissions.test', 'not-an-email', [], 'email');
    }
}
