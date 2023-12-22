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

namespace OAT\Library\Lti1p3Ags\Tests\Integration\Service\Score\Server\Handler;

use Nyholm\Psr7\ServerRequest;
use OAT\Library\Lti1p3Ags\Model\Score\ScoreInterface;
use OAT\Library\Lti1p3Ags\Repository\LineItemRepositoryInterface;
use OAT\Library\Lti1p3Ags\Repository\ScoreRepositoryInterface;
use OAT\Library\Lti1p3Ags\Serializer\Score\ScoreSerializer;
use OAT\Library\Lti1p3Ags\Serializer\Score\ScoreSerializerInterface;
use OAT\Library\Lti1p3Ags\Service\Score\ScoreServiceInterface;
use OAT\Library\Lti1p3Ags\Service\Score\Server\Handler\ScoreServiceServerRequestHandler;
use OAT\Library\Lti1p3Ags\Tests\Traits\AgsDomainTestingTrait;
use OAT\Library\Lti1p3Core\Security\OAuth2\Validator\RequestAccessTokenValidator;
use OAT\Library\Lti1p3Core\Security\OAuth2\Validator\Result\RequestAccessTokenValidationResult;
use OAT\Library\Lti1p3Core\Service\Server\LtiServiceServer;
use OAT\Library\Lti1p3Core\Tests\Resource\Logger\TestLogger;
use OAT\Library\Lti1p3Core\Tests\Traits\DomainTestingTrait;
use OAT\Library\Lti1p3Core\Tests\Traits\NetworkTestingTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LogLevel;

class ScoreServiceServerRequestHandlerTest extends TestCase
{
    use AgsDomainTestingTrait;
    use DomainTestingTrait;
    use NetworkTestingTrait;

    /** @var RequestAccessTokenValidator|MockObject */
    private $validatorMock;

    /** @var LineItemRepositoryInterface */
    private $lineItemRepository;

    /** @var ScoreRepositoryInterface */
    private $scoreRepository;

    /** @var ScoreSerializerInterface */
    private $serializer;

    /** @var TestLogger */
    private $logger;

    /** @var ScoreServiceServerRequestHandler */
    private $subject;

    /** @var LtiServiceServer */
    private $server;

    protected function setUp(): void
    {
        $this->validatorMock = $this->createMock(RequestAccessTokenValidator::class);
        $this->lineItemRepository = $this->createTestLineItemRepository();
        $this->scoreRepository = $this->createTestScoreRepository();
        $this->serializer = new ScoreSerializer();
        $this->logger = new TestLogger();

        $this->subject = new ScoreServiceServerRequestHandler(
            $this->lineItemRepository,
            $this->scoreRepository,
            null,
            null,
            $this->logger
        );

        $this->server = new LtiServiceServer(
            $this->validatorMock,
            $this->subject,
            $this->logger
        );
    }

    public function testRequestHandlingSuccess(): void
    {
        $registration = $this->createTestRegistration();
        $lineItem = $this->createTestLineItem();
        $score = $this->createTestScore();

        $request = new ServerRequest(
            'POST',
            $lineItem->getIdentifier() . '/scores',
            [
                'Content-Type' => ScoreServiceInterface::CONTENT_TYPE_SCORE,
            ],
            $this->serializer->serialize($score)
        );

        $validationResult = new RequestAccessTokenValidationResult($registration);

        $this->validatorMock
            ->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn($validationResult);

        $response = $this->server->handle($request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEmpty($response->getBody()->__toString());

        $this->assertTrue($this->logger->hasLog(LogLevel::INFO, 'AGS score service success'));

        $createdScores = $this->scoreRepository->findByLineItemIdentifier($lineItem->getIdentifier());

        $this->assertCount(1, $createdScores);

        /** @var ScoreInterface $createdScore */
        $createdScore = current($createdScores);

        $this->assertInstanceOf(ScoreInterface::class, $createdScore);
        $this->assertEquals($lineItem->getIdentifier(), $createdScore->getLineItemIdentifier());
    }

    public function testRequestHandlingErrorOnLineItemNotFound(): void
    {
        $registration = $this->createTestRegistration();

        $request = $this->createServerRequest(
            'POST',
            'https://example.com/line-items/invalid/scores',
            [],
            [
                'Content-Type' => ScoreServiceInterface::CONTENT_TYPE_SCORE
            ]
        );

        $validationResult = new RequestAccessTokenValidationResult($registration);

        $this->validatorMock
            ->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn($validationResult);

        $response = $this->server->handle($request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(404, $response->getStatusCode());

        $errorMessage = 'Cannot find line item with id https://example.com/line-items/invalid';

        $this->assertEquals($errorMessage, $response->getBody()->__toString());
        $this->assertTrue($this->logger->hasLog(LogLevel::ERROR, $errorMessage));
    }

    public function testRequestHandlingErrorOnInvalidScore(): void
    {
        $registration = $this->createTestRegistration();
        $lineItem = $this->createTestLineItem();

        $request = new ServerRequest(
            'POST',
            $lineItem->getIdentifier() . '/scores',
            [
                'Content-Type' => ScoreServiceInterface::CONTENT_TYPE_SCORE,
            ],
            'invalid'
        );

        $validationResult = new RequestAccessTokenValidationResult($registration);

        $this->validatorMock
            ->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn($validationResult);

        $response = $this->server->handle($request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $errorMessage = 'Error during score deserialization: Syntax error';

        $this->assertEquals($errorMessage, $response->getBody()->__toString());
        $this->assertTrue($this->logger->hasLog(LogLevel::ERROR, $errorMessage));
    }

    public function testRequestHandlingErrorOnInvalidHttpMethod(): void
    {
        $lineItem = $this->createTestLineItem();
        $score = $this->createTestScore();

        $request = new ServerRequest(
            'GET',
            $lineItem->getIdentifier() . '/scores',
            [
                'Content-Type' => ScoreServiceInterface::CONTENT_TYPE_SCORE,
            ],
            $this->serializer->serialize($score)
        );

        $this->validatorMock
            ->expects($this->never())
            ->method('validate');

        $response = $this->server->handle($request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(405, $response->getStatusCode());

        $errorMessage = 'Not acceptable request method, accepts: [post]';

        $this->assertEquals($errorMessage, $response->getBody()->__toString());
        $this->assertTrue($this->logger->hasLog(LogLevel::ERROR, $errorMessage));
    }

    public function testRequestHandlingErrorOnInvalidContentType(): void
    {
        $lineItem = $this->createTestLineItem();

        $request = $this->createServerRequest(
            'POST',
            $lineItem->getIdentifier() . '/scores',
            [],
            [
                'Content-Type' => 'invalid'
            ]
        );

        $this->validatorMock
            ->expects($this->never())
            ->method('validate');

        $response = $this->server->handle($request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(406, $response->getStatusCode());

        $errorMessage = 'Not acceptable request content type, accepts: application/vnd.ims.lis.v1.score+json';

        $this->assertEquals($errorMessage, $response->getBody()->__toString());
        $this->assertTrue($this->logger->hasLog(LogLevel::ERROR, $errorMessage));
    }

    public function testRequestHandlingErrorOnInvalidToken(): void
    {
        $registration = $this->createTestRegistration();
        $lineItem = $this->createTestLineItem();
        $score = $this->createTestScore();

        $errorMessage = 'token validation error';

        $request = new ServerRequest(
            'POST',
            $lineItem->getIdentifier() . '/scores',
            [
                'Content-Type' => ScoreServiceInterface::CONTENT_TYPE_SCORE,
            ],
            $this->serializer->serialize($score)
        );

        $validationResult = new RequestAccessTokenValidationResult($registration, null, [], $errorMessage);

        $this->validatorMock
            ->expects($this->once())
            ->method('validate')
            ->with($request)
            ->willReturn($validationResult);

        $response = $this->server->handle($request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(401, $response->getStatusCode());

        $this->assertEquals($errorMessage, $response->getBody()->__toString());
        $this->assertTrue($this->logger->hasLog(LogLevel::ERROR, $errorMessage));
    }
}
