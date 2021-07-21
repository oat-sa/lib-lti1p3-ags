<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Service\Result\Client;

use Exception;
use OAT\Library\Lti1p3Ags\Service\Result\Client\ResultServiceClient;
use OAT\Library\Lti1p3Ags\Service\Result\ResultServiceInterface;
use OAT\Library\Lti1p3Ags\Tests\Traits\AgsDomainTestingTrait;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use OAT\Library\Lti1p3Core\Message\Payload\Claim\AgsClaim;
use OAT\Library\Lti1p3Core\Message\Payload\LtiMessagePayloadInterface;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use OAT\Library\Lti1p3Core\Service\Client\LtiServiceClientInterface;
use OAT\Library\Lti1p3Core\Tests\Traits\DomainTestingTrait;
use OAT\Library\Lti1p3Core\Tests\Traits\NetworkTestingTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ResultServiceClientTest extends TestCase
{
    use AgsDomainTestingTrait;
    use DomainTestingTrait;
    use NetworkTestingTrait;

    /** @var LtiServiceClientInterface|MockObject */
    private $clientMock;

    /** @var ResultServiceClient */
    private $subject;

    protected function setUp(): void
    {
        $this->clientMock = $this->createMock(LtiServiceClientInterface::class);

        $this->subject = new ResultServiceClient($this->clientMock);
    }

    public function testListResultsForPayloadSuccess(): void
    {
        $registration = $this->createTestRegistration();
        $lineItem = $this->createTestLineItem();
        $resultCollection = $this->createTestResultCollection();

        $this->prepareClientMockSuccess(
            $registration,
            'GET',
            $lineItem->getIdentifier() . '/results?user_id=uid&limit=1&offset=1',
            [
                'headers' => [
                    'Accept' => ResultServiceInterface::CONTENT_TYPE_RESULT_CONTAINER,
                ]
            ],
            [
                ResultServiceInterface::AUTHORIZATION_SCOPE_RESULT_READ_ONLY,
            ],
            json_encode($resultCollection),
            200,
            [
                ResultServiceInterface::HEADER_LINK => sprintf(
                    '<%s/results?limit=1&offset=2>; rel="next"',
                    $lineItem->getIdentifier()
                )
            ]
        );

        $claim = new AgsClaim(
            [
                ResultServiceInterface::AUTHORIZATION_SCOPE_RESULT_READ_ONLY
            ],
            null,
            $lineItem->getIdentifier()
        );

        $payload = $this->createMock(LtiMessagePayloadInterface::class);
        $payload
            ->expects($this->once())
            ->method('getAgs')
            ->willReturn($claim);

        $result = $this->subject->listResultsForPayload(
            $registration,
            $payload,
            'uid',
            1,
            1
        );

        $this->assertEquals($resultCollection, $result->getResults());
        $this->assertTrue($result->hasNext());
        $this->assertEquals(
            $lineItem->getIdentifier() . '/results?limit=1&offset=2',
            $result->getRelationLinkUrl()
        );
    }

    public function testListResultsForPayloadErrorOnMissingAgsClaim(): void
    {
        $registration = $this->createTestRegistration();

        $this->clientMock
            ->expects($this->never())
            ->method('request');

        $payload = $this->createMock(LtiMessagePayloadInterface::class);
        $payload
            ->expects($this->once())
            ->method('getAgs')
            ->willReturn(null);

        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Cannot list results for payload: Provided payload does not contain AGS claim');

        $this->subject->listResultsForPayload($registration, $payload);
    }

    public function testListResultsForClaimSuccess(): void
    {
        $registration = $this->createTestRegistration();
        $lineItem = $this->createTestLineItem();
        $resultCollection = $this->createTestResultCollection();

        $this->prepareClientMockSuccess(
            $registration,
            'GET',
            $lineItem->getIdentifier() . '/results?user_id=uid&limit=1&offset=1',
            [
                'headers' => [
                    'Accept' => ResultServiceInterface::CONTENT_TYPE_RESULT_CONTAINER,
                ]
            ],
            [
                ResultServiceInterface::AUTHORIZATION_SCOPE_RESULT_READ_ONLY,
            ],
            json_encode($resultCollection),
            200,
            [
                ResultServiceInterface::HEADER_LINK => sprintf(
                    '<%s/results?limit=1&offset=2>; rel="next"',
                    $lineItem->getIdentifier()
                )
            ]
        );

        $claim = new AgsClaim(
            [
                ResultServiceInterface::AUTHORIZATION_SCOPE_RESULT_READ_ONLY
            ],
            null,
            $lineItem->getIdentifier()
        );

        $result = $this->subject->listResultsForClaim(
            $registration,
            $claim,
            'uid',
            1,
            1
        );

        $this->assertEquals($resultCollection, $result->getResults());
        $this->assertTrue($result->hasNext());
        $this->assertEquals(
            $lineItem->getIdentifier() . '/results?limit=1&offset=2',
            $result->getRelationLinkUrl()
        );
    }

    public function testListResultsForClaimErrorOnMissingLineItemUrl(): void
    {
        $registration = $this->createTestRegistration();

        $this->clientMock
            ->expects($this->never())
            ->method('request');

        $claim = new AgsClaim(
            [
                ResultServiceInterface::AUTHORIZATION_SCOPE_RESULT_READ_ONLY
            ]
        );

        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Cannot list results for claim: Provided AGS claim does not contain line item url');

        $this->subject->listResultsForClaim($registration, $claim);
    }

    public function testListResultsForClaimErrorOnInvalidScopes(): void
    {
        $registration = $this->createTestRegistration();
        $lineItem = $this->createTestLineItem();

        $this->clientMock
            ->expects($this->never())
            ->method('request');

        $claim = new AgsClaim(
            [
                'invalid'
            ],
            null,
            $lineItem->getIdentifier()
        );

        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Cannot list results for claim: Provided AGS claim does not contain result scope');

        $this->subject->listResultsForClaim($registration, $claim);
    }

    public function testListResultsSuccess(): void
    {
        $registration = $this->createTestRegistration();
        $lineItem = $this->createTestLineItem();
        $resultCollection = $this->createTestResultCollection();

        $this->prepareClientMockSuccess(
            $registration,
            'GET',
            $lineItem->getIdentifier() . '/results?user_id=uid&limit=1&offset=1',
            [
                'headers' => [
                    'Accept' => ResultServiceInterface::CONTENT_TYPE_RESULT_CONTAINER,
                ]
            ],
            [
                ResultServiceInterface::AUTHORIZATION_SCOPE_RESULT_READ_ONLY,
            ],
            json_encode($resultCollection),
            200,
            [
                ResultServiceInterface::HEADER_LINK => sprintf(
                    '<%s/results?limit=1&offset=2>; rel="next"',
                    $lineItem->getIdentifier()
                )
            ]
        );

        $result = $this->subject->listResults(
            $registration,
            $lineItem->getIdentifier(),
            'uid',
            1,
            1
        );

        $this->assertEquals($resultCollection, $result->getResults());
        $this->assertTrue($result->hasNext());
        $this->assertEquals(
            $lineItem->getIdentifier() . '/results?limit=1&offset=2',
            $result->getRelationLinkUrl()
        );
    }

    public function testListResultsError(): void
    {
        $error = 'list error';

        $registration = $this->createTestRegistration();
        $lineItem = $this->createTestLineItem();

        $this->prepareClientMockError(
            $registration,
            'GET',
            $lineItem->getIdentifier() . '/results?user_id=uid&limit=1&offset=1',
            [
                'headers' => [
                    'Accept' => ResultServiceInterface::CONTENT_TYPE_RESULT_CONTAINER,
                ]
            ],
            [
                ResultServiceInterface::AUTHORIZATION_SCOPE_RESULT_READ_ONLY,
            ],
            $error
        );

        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Cannot list results: list error');

        $this->subject->listResults(
            $registration,
            $lineItem->getIdentifier(),
            'uid',
            1,
            1
        );
    }

    private function prepareClientMockSuccess(
        RegistrationInterface $registration,
        string $expectedRequestMethod,
        string $expectedRequestUrl,
        array $expectedRequestOptions = [],
        array $expectedRequestScopes = [],
        string $expectedResponseBody = '',
        int $expectedResponseStatusCode = 200,
        array $expectedResponseHeaders = []
    ): void {
        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with(
                $registration,
                $expectedRequestMethod,
                $expectedRequestUrl,
                $expectedRequestOptions,
                $expectedRequestScopes
            )
            ->willReturn(
                $this->createResponse($expectedResponseBody, $expectedResponseStatusCode, $expectedResponseHeaders)
            );
    }

    private function prepareClientMockError(
        RegistrationInterface $registration,
        string $expectedRequestMethod,
        string $expectedRequestUrl,
        array $expectedRequestOptions = [],
        array $expectedRequestScopes = [],
        string $expectedExceptionMessage = ''
    ): void {
        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with(
                $registration,
                $expectedRequestMethod,
                $expectedRequestUrl,
                $expectedRequestOptions,
                $expectedRequestScopes
            )
            ->willThrowException(new Exception($expectedExceptionMessage));
    }
}
