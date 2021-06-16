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

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Service\Score\Client;

use Exception;
use OAT\Library\Lti1p3Ags\Service\Score\Client\ScoreServiceClient;
use OAT\Library\Lti1p3Ags\Service\Score\ScoreServiceInterface;
use OAT\Library\Lti1p3Ags\Tests\Traits\AgsDomainTestingTrait;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use OAT\Library\Lti1p3Core\Service\Client\LtiServiceClientInterface;
use OAT\Library\Lti1p3Core\Tests\Traits\DomainTestingTrait;
use OAT\Library\Lti1p3Core\Tests\Traits\NetworkTestingTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ScoreServiceClientTest extends TestCase
{
    use AgsDomainTestingTrait;
    use DomainTestingTrait;
    use NetworkTestingTrait;

    /** @var LtiServiceClientInterface|MockObject */
    private $clientMock;

    /** @var ScoreServiceClient */
    private $subject;

    protected function setUp(): void
    {
        $this->clientMock = $this->createMock(LtiServiceClientInterface::class);

        $this->subject = new ScoreServiceClient($this->clientMock);
    }

    public function testCreateLineItemSuccess(): void
    {
        $registration = $this->createTestRegistration();
        $lineItem = $this->createTestLineItem();
        $score = $this->createTestScore();

        $this->prepareClientMockSuccess(
            $registration,
            'POST',
            $lineItem->getIdentifier() . '/scores',
            [
                'headers' => [
                    'Content-Type' => ScoreServiceInterface::CONTENT_TYPE_SCORE,
                ],
                'body' => json_encode($score)
            ],
            [
                ScoreServiceInterface::AUTHORIZATION_SCOPE_SCORE,
            ],
            json_encode($score)
        );

        $result = $this->subject->publishScore($registration, $score, $lineItem->getIdentifier());

        $this->assertTrue($result);
    }

    public function testCreateLineItemError(): void
    {
        $error = 'publish error';

        $registration = $this->createTestRegistration();
        $lineItem = $this->createTestLineItem();
        $score = $this->createTestScore();

        $this->prepareClientMockError(
            $registration,
            'POST',
            $lineItem->getIdentifier() . '/scores',
            [
                'headers' => [
                    'Content-Type' => ScoreServiceInterface::CONTENT_TYPE_SCORE,
                ],
                'body' => json_encode($score)
            ],
            [
                ScoreServiceInterface::AUTHORIZATION_SCOPE_SCORE,
            ],
            $error
        );

        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Cannot publish score: publish error');

        $this->subject->publishScore($registration, $score, $lineItem->getIdentifier());
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
