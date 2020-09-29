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

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Service\Client;

use InvalidArgumentException;
use OAT\Library\Lti1p3Ags\Model\Score;
use OAT\Library\Lti1p3Ags\Serializer\Normalizer\Tool\ScoreNormalizer;
use OAT\Library\Lti1p3Ags\Service\Client\ScoreServiceClient;
use OAT\Library\Lti1p3Ags\Tests\Unit\Traits\DomainTestingTrait;
use OAT\Library\Lti1p3Core\Message\Claim\AgsClaim;
use OAT\Library\Lti1p3Core\Service\Client\ServiceClient;
use PHPUnit\Framework\TestCase;

class ScoreServiceClientTest extends TestCase
{
    use DomainTestingTrait;

    /** @var ServiceClient */
    private $serviceClientMock;

    /** @var ServiceClient */
    private $subject;

    /** @var ScoreNormalizer */
    private $scoreNormalizer;

    protected function setUp(): void
    {
        $this->serviceClientMock = $this->createMock(ServiceClient::class);
        $this->scoreNormalizer = new ScoreNormalizer();

        $this->subject = new ScoreServiceClient($this->scoreNormalizer, $this->serviceClientMock);
    }

    /**
     * @dataProvider validProvidedInputDataProvider
     */
    public function testItWillPublish(AgsClaim $agsClaim, Score $score, ?array $scopes, string $expectedLineItemUrl): void
    {
        $registration = $this->createTestRegistration();

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

        $this->subject->publish($registration, $agsClaim, $score, $scopes);
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
                ['https://purl.imsglobal.org/spec/lti-ags/scope/score'],
                'https://www.myuniv.example.com/2344/lineitems/1234/scores'
            ],
            'Data for AGS claim with no scope' => [
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
                null,
                'https://www.myuniv.example.com/2344/lineitems/1234/scores'
            ],
            'Data for AGS claim without score scope from claim' => [
                new AgsClaim(
                    [
                        'https://purl.imsglobal.org/spec/lti-ags/scope/lineitem',
                        'https://purl.imsglobal.org/spec/lti-ags/scope/result.readonly',
                    ],
                    'https://www.myuniv.example.com/2344/lineitems/',
                    'https://www.myuniv.example.com/2344/lineitems/1234/lineitem'
                ),
                $this->createScore(),
                ['https://purl.imsglobal.org/spec/lti-ags/scope/score'],
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
                ['https://purl.imsglobal.org/spec/lti-ags/scope/score'],
                'https://user@www.myuniv.example.com/2344/lineitems/1234/scores'
            ],
            'Data with lineItemUrl with user+pass' => [
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
                ['https://purl.imsglobal.org/spec/lti-ags/scope/score'],
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
                ['https://purl.imsglobal.org/spec/lti-ags/scope/score'],
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
                ['https://purl.imsglobal.org/spec/lti-ags/scope/score'],
                'https://www.myuniv.example.com/2344/lineitems/1234/scores'
            ],
            'Data with lineItemUrl ending without lineitem' => [
                new AgsClaim(
                    [
                        'https://purl.imsglobal.org/spec/lti-ags/scope/lineitem',
                        'https://purl.imsglobal.org/spec/lti-ags/scope/result.readonly',
                    ],
                    'https://www.myuniv.example.com/2344/lineitems/',
                    'https://www.myuniv.example.com/2344/lineitems/1234/'
                ),
                $this->createScore(),
                ['https://purl.imsglobal.org/spec/lti-ags/scope/score'],
                'https://www.myuniv.example.com/2344/lineitems/1234/scores'
            ],
            'Data with lineItemUrl ending with parameters' => [
                new AgsClaim(
                    [
                        'https://purl.imsglobal.org/spec/lti-ags/scope/lineitem',
                        'https://purl.imsglobal.org/spec/lti-ags/scope/result.readonly',
                    ],
                    'https://www.myuniv.example.com/2344/lineitems/',
                    'https://www.myuniv.example.com/2344/lineitems/1234/lineitem?param1=value1&param2=value2'
                ),
                $this->createScore(),
                ['https://purl.imsglobal.org/spec/lti-ags/scope/score'],
                'https://www.myuniv.example.com/2344/lineitems/1234/scores?param1=value1&param2=value2'
            ],
            'Data with lineItemUrl ending with /parameters' => [
                new AgsClaim(
                    [
                        'https://purl.imsglobal.org/spec/lti-ags/scope/lineitem',
                        'https://purl.imsglobal.org/spec/lti-ags/scope/result.readonly',
                    ],
                    'https://www.myuniv.example.com/2344/lineitems/',
                    'https://www.myuniv.example.com/2344/lineitems/1234/lineitem/?param1=value1&param2=value2'
                ),
                $this->createScore(),
                ['https://purl.imsglobal.org/spec/lti-ags/scope/score'],
                'https://www.myuniv.example.com/2344/lineitems/1234/scores?param1=value1&param2=value2'
            ]
        ];
    }

    public function testItWillThrowsAnExceptionIfLineItemUrlIsNotSet(): void
    {
        $registration = $this->createTestRegistration();
        $score = $this->createScore();
        $agsClaim = new AgsClaim(
            [
                'https://purl.imsglobal.org/spec/lti-ags/scope/lineitem',
                'https://purl.imsglobal.org/spec/lti-ags/scope/result.readonly',
                'https://purl.imsglobal.org/spec/lti-ags/scope/score'
            ],
            'https://www.myuniv.example.com/2344/lineitems/',
            null
        );

        $this->serviceClientMock
            ->expects($this->never())
            ->method('request');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The line item url required to send the score is not defined');

        $this->subject->publish($registration, $agsClaim, $score);
    }

    public function testAuthorizationScopeScoreConstant(): void
    {
        $this->assertEquals(
            'https://purl.imsglobal.org/spec/lti-ags/scope/score',
            $this->subject::AUTHORIZATION_SCOPE_SCORE
        );
    }

    /**
     * @dataProvider invalidProvidedInputDataProvider
     */
    public function testItWillThrowsAnExceptionIfWrongScopeIsGiven(
        AgsClaim $agsClaim,
        Score $score,
        string $errorMessage,
        array $scopes = null
    ): void {
        $registration = $this->createTestRegistration();

        $this->serviceClientMock
            ->expects($this->never())
            ->method('request');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($errorMessage);

        $this->subject->publish($registration, $agsClaim, $score, $scopes);
    }

    public function invalidProvidedInputDataProvider(): array
    {
        return [
            [
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
                'The provided scopes https://purl.imsglobal.org/spec/lti-ags/scope/result.readonly is not valid. The only scope allowed is https://purl.imsglobal.org/spec/lti-ags/scope/score',
                ['https://purl.imsglobal.org/spec/lti-ags/scope/result.readonly'],
            ],
            [
                new AgsClaim(
                    [
                        'https://purl.imsglobal.org/spec/lti-ags/scope/lineitem',
                        'https://purl.imsglobal.org/spec/lti-ags/scope/result.readonly',
                    ],
                    'https://www.myuniv.example.com/2344/lineitems/',
                    'https://www.myuniv.example.com/2344/lineitems/1234/lineitem'
                ),
                $this->createScore(),
                'The provided scopes https://purl.imsglobal.org/spec/lti-ags/scope/lineitem, https://purl.imsglobal.org/spec/lti-ags/scope/result.readonly is not valid. The only scope allowed is https://purl.imsglobal.org/spec/lti-ags/scope/score',
                null,
            ]
        ];
    }
}
