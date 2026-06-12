<?php

declare(strict_types=1);

namespace KycAi\Laravel\Tests;

use KycAi\Laravel\Data\ExternalVerificationRequest;
use KycAi\Laravel\KycManager;
use KycAi\Laravel\Verifiers\NullExternalVerifier;

final class NullExternalVerifierTest extends TestCase
{
    public function test_null_verifier_does_not_pass(): void
    {
        $result = (new NullExternalVerifier)->verify(
            new ExternalVerificationRequest('sa', '1001244084'),
        );

        $this->assertFalse($result->passed());
        $this->assertSame('none', $result->provider());
        $this->assertFalse((new NullExternalVerifier)->sendsDataExternally());
    }

    public function test_manager_resolves_null_external_driver(): void
    {
        config([
            'kyc.external_verification.enabled' => true,
            'kyc.external_verification.default' => 'null',
        ]);

        $manager = new KycManager(config('kyc'));
        $verifier = $manager->externalVerifier('null');

        $this->assertInstanceOf(NullExternalVerifier::class, $verifier);
    }
}
