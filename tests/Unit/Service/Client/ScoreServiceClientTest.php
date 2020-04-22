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
    public function testItWillPublish(AgsClaim $agsClaim, Score $score, array $scopes = null): void
    {
        $registration = $this->createTestRegistration();

        $this->serviceClientMock
            ->expects($this->once())
            ->method('request')
            ->with(
                $registration,
                'POST',
                $agsClaim->getLineItemUrl() . '/scores',
                [
                    'json' => $this->scoreNormalizer->normalize($score)
                ]
            );

        $this->subject->publish($registration, $agsClaim, $score, $scopes);
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

    public function validProvidedInputDataProvider(): array
    {
        return [
            [//Provided score as method parameter is as expected
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
                ['https://purl.imsglobal.org/spec/lti-ags/scope/score']
            ],
            [//Provided score as method parameter is null but scope in AgsClaim is as expected
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
                null
            ],
            [//Provided score as method parameter is as expected
                new AgsClaim(
                    [
                        'https://purl.imsglobal.org/spec/lti-ags/scope/lineitem',
                        'https://purl.imsglobal.org/spec/lti-ags/scope/result.readonly',
                    ],
                    'https://www.myuniv.example.com/2344/lineitems/',
                    'https://www.myuniv.example.com/2344/lineitems/1234/lineitem'
                ),
                $this->createScore(),
                ['https://purl.imsglobal.org/spec/lti-ags/scope/score']
            ],
        ];
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
