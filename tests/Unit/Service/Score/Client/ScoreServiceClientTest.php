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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Service\Score\Client;

use InvalidArgumentException;
use OAT\Library\Lti1p3Ags\Model\Score\ScoreInterface;
use OAT\Library\Lti1p3Ags\Serializer\Score\Normalizer\ScoreNormalizer;
use OAT\Library\Lti1p3Ags\Serializer\Score\Normalizer\ScoreNormalizerInterface;
use OAT\Library\Lti1p3Ags\Service\Score\Client\ScoreServiceClient;
use OAT\Library\Lti1p3Ags\Service\Score\ScoreServiceInterface;
use OAT\Library\Lti1p3Ags\Tests\Traits\AgsDomainTestingTrait;
use OAT\Library\Lti1p3Core\Exception\LtiException;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use OAT\Library\Lti1p3Core\Message\Payload\Claim\AgsClaim;
use OAT\Library\Lti1p3Core\Message\Payload\LtiMessagePayloadInterface;
use OAT\Library\Lti1p3Core\Service\Client\ServiceClientInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ScoreServiceClientTest extends TestCase
{
    use AgsDomainTestingTrait;

    /** @var ServiceClientInterface|MockObject */
    private $serviceClientMock;

    /** @var ScoreNormalizerInterface */
    private $scoreNormalizer;

    /** @var ScoreServiceClient */
    private $subject;

    protected function setUp(): void
    {
        $this->serviceClientMock = $this->createMock(ServiceClientInterface::class);
        $this->scoreNormalizer = new ScoreNormalizer();

        $this->subject = new ScoreServiceClient($this->serviceClientMock, $this->scoreNormalizer);
    }

    public function testAuthorizationScopeScoreConstant(): void
    {
        $this->assertEquals(
            'https://purl.imsglobal.org/spec/lti-ags/scope/score',
            ScoreServiceInterface::AUTHORIZATION_SCOPE_SCORE
        );
    }

    /**
     * @dataProvider validProvidedInputDataProvider
     */
    public function testPublishForPayload(
        AgsClaim $agsClaim,
        ScoreInterface $score,
        string $expectedLineItemUrl
    ): void {

        $registration = $this->createTestRegistration();

        $payload = $this->createMock(LtiMessagePayloadInterface::class);
        $payload
            ->expects($this->any())
            ->method('getAgs')
            ->willReturn($agsClaim);

        $this->serviceClientMock
            ->expects($this->once())
            ->method('request')
            ->with(
                $registration,
                'POST',
                $expectedLineItemUrl,
                [
                    'headers' => ['Content-Type' => ScoreServiceClient::CONTENT_TYPE_SCORE],
                    'body' => json_encode($this->scoreNormalizer->normalize($score))
                ]
            );

        $this->subject->publishForPayload($registration, $payload, $score);
    }

    public function validProvidedInputDataProvider(): array
    {
        return [
            'Standard data for AGS claim' => [
                new AgsClaim(
                    [
                        'https://purl.imsglobal.org/spec/lti-ags/scope/lineitem',
                        'https://purl.imsglobal.org/spec/lti-ags/scope/result.readonly',
                        'https://purl.imsglobal.org/spec/lti-ags/scope/score'
                    ],
                    'https://www.myuniv.example.com/2344/lineitems/',
                    'https://www.myuniv.example.com/2344/lineitems/1234/lineitem'
                ),
                $this->createScore(),
                'https://www.myuniv.example.com/2344/lineitems/1234/scores'
            ],
            'Data with lineItemUrl with user' => [
                new AgsClaim(
                    [
                        'https://purl.imsglobal.org/spec/lti-ags/scope/lineitem',
                        'https://purl.imsglobal.org/spec/lti-ags/scope/result.readonly',
                        'https://purl.imsglobal.org/spec/lti-ags/scope/score'
                    ],
                    'https://www.myuniv.example.com/2344/lineitems/',
                    'https://user@www.myuniv.example.com/2344/lineitems/1234/lineitem'
                ),
                $this->createScore(),
                'https://user@www.myuniv.example.com/2344/lineitems/1234/scores'
            ],
            'Data with lineItemUrl with user and password' => [
                new AgsClaim(
                    [
                        'https://purl.imsglobal.org/spec/lti-ags/scope/lineitem',
                        'https://purl.imsglobal.org/spec/lti-ags/scope/result.readonly',
                        'https://purl.imsglobal.org/spec/lti-ags/scope/score'
                    ],
                    'https://www.myuniv.example.com/2344/lineitems/',
                    'https://user:pass@www.myuniv.example.com/2344/lineitems/1234/lineitem'
                ),
                $this->createScore(),
                'https://user:pass@www.myuniv.example.com/2344/lineitems/1234/scores'
            ],
            'Data with lineItemUrl with port' => [
                new AgsClaim(
                    [
                        'https://purl.imsglobal.org/spec/lti-ags/scope/lineitem',
                        'https://purl.imsglobal.org/spec/lti-ags/scope/result.readonly',
                        'https://purl.imsglobal.org/spec/lti-ags/scope/score'
                    ],
                    'https://www.myuniv.example.com/2344/lineitems/',
                    'https://www.myuniv.example.com:1988/2344/lineitems/1234/lineitem'
                ),
                $this->createScore(),
                'https://www.myuniv.example.com:1988/2344/lineitems/1234/scores'
            ],
            'Data with lineItemUrl ending with /' => [
                new AgsClaim(
                    [
                        'https://purl.imsglobal.org/spec/lti-ags/scope/lineitem',
                        'https://purl.imsglobal.org/spec/lti-ags/scope/result.readonly',
                        'https://purl.imsglobal.org/spec/lti-ags/scope/score'
                    ],
                    'https://www.myuniv.example.com/2344/lineitems/',
                    'https://www.myuniv.example.com/2344/lineitems/1234/lineitem/'
                ),
                $this->createScore(),
                'https://www.myuniv.example.com/2344/lineitems/1234/scores'
            ],
            'Data with lineItemUrl ending without line item' => [
                new AgsClaim(
                    [
                        'https://purl.imsglobal.org/spec/lti-ags/scope/lineitem',
                        'https://purl.imsglobal.org/spec/lti-ags/scope/result.readonly',
                        'https://purl.imsglobal.org/spec/lti-ags/scope/score'
                    ],
                    'https://www.myuniv.example.com/2344/lineitems/',
                    'https://www.myuniv.example.com/2344/lineitems/1234/'
                ),
                $this->createScore(),
                'https://www.myuniv.example.com/2344/lineitems/1234/scores'
            ],
            'Data with lineItemUrl ending with parameters' => [
                new AgsClaim(
                    [
                        'https://purl.imsglobal.org/spec/lti-ags/scope/lineitem',
                        'https://purl.imsglobal.org/spec/lti-ags/scope/result.readonly',
                        'https://purl.imsglobal.org/spec/lti-ags/scope/score'
                    ],
                    'https://www.myuniv.example.com/2344/lineitems/',
                    'https://www.myuniv.example.com/2344/lineitems/1234/lineitem?param1=value1&param2=value2'
                ),
                $this->createScore(),
                'https://www.myuniv.example.com/2344/lineitems/1234/scores?param1=value1&param2=value2'
            ],
            'Data with lineItemUrl ending with /parameters' => [
                new AgsClaim(
                    [
                        'https://purl.imsglobal.org/spec/lti-ags/scope/lineitem',
                        'https://purl.imsglobal.org/spec/lti-ags/scope/result.readonly',
                        'https://purl.imsglobal.org/spec/lti-ags/scope/score'
                    ],
                    'https://www.myuniv.example.com/2344/lineitems/',
                    'https://www.myuniv.example.com/2344/lineitems/1234/lineitem/?param1=value1&param2=value2'
                ),
                $this->createScore(),
                'https://www.myuniv.example.com/2344/lineitems/1234/scores?param1=value1&param2=value2'
            ]
        ];
    }

    public function testPublishFailureWithMissingAgsClaim(): void
    {
        $registration = $this->createTestRegistration();

        $payload = $this->createMock(LtiMessagePayloadInterface::class);
        $payload
            ->expects($this->any())
            ->method('getAgs')
            ->willReturn(null);

        $score = $this->createScore();

        $this->serviceClientMock
            ->expects($this->never())
            ->method('request');

        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Cannot publish score for payload: Provided payload does not contain AGS claim');

        $this->subject->publishForPayload($registration, $payload, $score);
    }

    public function testPublishFailureWithMissingLineItemUrl(): void
    {
        $registration = $this->createTestRegistration();

        $agsClaim = new AgsClaim(
            [
                'https://purl.imsglobal.org/spec/lti-ags/scope/lineitem',
                'https://purl.imsglobal.org/spec/lti-ags/scope/result.readonly',
                'https://purl.imsglobal.org/spec/lti-ags/scope/score'
            ],
            'https://www.myuniv.example.com/2344/lineitems/',
            null
        );

        $payload = $this->createMock(LtiMessagePayloadInterface::class);
        $payload
            ->expects($this->any())
            ->method('getAgs')
            ->willReturn($agsClaim);

        $score = $this->createScore();

        $this->serviceClientMock
            ->expects($this->never())
            ->method('request');

        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Cannot publish score for payload: Provided payload AGS claim does not contain a line item url');

        $this->subject->publishForPayload($registration, $payload, $score);
    }

    public function testPublishFailureWithMissingMandatoryScope(): void
    {
        $registration = $this->createTestRegistration();

        $agsClaim = new AgsClaim(
            [
                'https://purl.imsglobal.org/spec/lti-ags/scope/lineitem',
                'https://purl.imsglobal.org/spec/lti-ags/scope/result.readonly',
            ],
            'https://www.myuniv.example.com/2344/lineitems/',
            'https://www.myuniv.example.com/2344/lineitems/1234/lineitem'
        );

        $payload = $this->createMock(LtiMessagePayloadInterface::class);
        $payload
            ->expects($this->any())
            ->method('getAgs')
            ->willReturn($agsClaim);

        $score = $this->createScore();

        $this->serviceClientMock
            ->expects($this->never())
            ->method('request');

        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Cannot publish score: The mandatory scope https://purl.imsglobal.org/spec/lti-ags/scope/score is missing');

        $this->subject->publishForPayload($registration, $payload, $score);
    }

    public function testPublishFailureWithGenericLtiError(): void
    {
        $registration = $this->createTestRegistration();

        $agsClaim = new AgsClaim(
            [
                'https://purl.imsglobal.org/spec/lti-ags/scope/lineitem',
                'https://purl.imsglobal.org/spec/lti-ags/scope/result.readonly',
                'https://purl.imsglobal.org/spec/lti-ags/scope/score'
            ],
            'https://www.myuniv.example.com/2344/lineitems/',
            'https://www.myuniv.example.com/2344/lineitems/1234/lineitem'
        );

        $payload = $this->createMock(LtiMessagePayloadInterface::class);
        $payload
            ->expects($this->any())
            ->method('getAgs')
            ->willReturn($agsClaim);

        $score = $this->createScore();

        $this->serviceClientMock
            ->expects($this->once())
            ->method('request')
            ->willThrowException(new LtiException('custom LTI error'));

        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('custom LTI error');

        $this->subject->publishForPayload($registration, $payload, $score);
    }
}
