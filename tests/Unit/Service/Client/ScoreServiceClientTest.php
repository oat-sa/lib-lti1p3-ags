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
use OAT\Library\Lti1p3Ags\Serializer\Normalizer\Tool\ScorePublishNormalizer;
use OAT\Library\Lti1p3Ags\Service\Client\ScoreServiceClient;
use OAT\Library\Lti1p3Ags\Tests\Traits\DomainTestingTrait;
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

    /** @var ScorePublishNormalizer */
    private $scoreNormalizer;

    protected function setUp(): void
    {
        $this->serviceClientMock = $this->createMock(ServiceClient::class);
        $this->scoreNormalizer = new ScorePublishNormalizer();

        $this->subject = new ScoreServiceClient($this->scoreNormalizer, $this->serviceClientMock);
    }

    public function testItWillPublish(): void
    {
        $registration = $this->createTestRegistration();
        $score = new Score(
            'userId',
            'contextId',
            'lineItemId',
            null,
            0.2,
            0.3
        );
        $agsClaim = new AgsClaim(
            [
                'https://purl.imsglobal.org/spec/lti-ags/scope/lineitem',
                'https://purl.imsglobal.org/spec/lti-ags/scope/result.readonly',
                'https://purl.imsglobal.org/spec/lti-ags/scope/score'
            ],
            'https://www.myuniv.example.com/2344/lineitems/',
            'https://www.myuniv.example.com/2344/lineitems/1234/lineitem'
        );

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

        $this->subject->publish($registration, $agsClaim, $score);
    }

    public function testItWillThrowsAnExceptionIfLineItemUrlIsNotSet(): void
    {
        $registration = $this->createTestRegistration();
        $score = new Score(
            'userId',
            'contextId',
            'lineItemId',
            null,
            0.2,
            0.3
        );
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
}
