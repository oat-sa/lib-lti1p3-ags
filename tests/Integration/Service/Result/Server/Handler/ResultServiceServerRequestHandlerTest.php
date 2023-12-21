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

namespace OAT\Library\Lti1p3Ags\Tests\Integration\Service\Result\Server\Handler;

use OAT\Library\Lti1p3Ags\Model\Result\ResultContainer;
use OAT\Library\Lti1p3Ags\Repository\LineItemRepositoryInterface;
use OAT\Library\Lti1p3Ags\Repository\ResultRepositoryInterface;
use OAT\Library\Lti1p3Ags\Serializer\Result\ResultCollectionSerializer;
use OAT\Library\Lti1p3Ags\Serializer\Result\ResultCollectionSerializerInterface;
use OAT\Library\Lti1p3Ags\Service\LineItem\LineItemServiceInterface;
use OAT\Library\Lti1p3Ags\Service\Result\ResultServiceInterface;
use OAT\Library\Lti1p3Ags\Service\Result\Server\Handler\ResultServiceServerRequestHandler;
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

class ResultServiceServerRequestHandlerTest extends TestCase
{
    use AgsDomainTestingTrait;
    use DomainTestingTrait;
    use NetworkTestingTrait;

    /** @var RequestAccessTokenValidator|MockObject */
    private $validatorMock;

    /** @var LineItemRepositoryInterface */
    private $lineItemRepository;

    /** @var ResultRepositoryInterface */
    private $resultRepository;

    /** @var ResultCollectionSerializerInterface */
    private $serializer;

    /** @var TestLogger */
    private $logger;

    /** @var ResultServiceServerRequestHandler */
    private $subject;

    /** @var LtiServiceServer */
    private $server;

    protected function setUp(): void
    {
        $this->validatorMock = $this->createMock(RequestAccessTokenValidator::class);
        $this->lineItemRepository = $this->createTestLineItemRepository();
        $this->resultRepository = $this->createTestResultRepository();
        $this->serializer = new ResultCollectionSerializer();
        $this->logger = new TestLogger();

        $this->subject = new ResultServiceServerRequestHandler(
            $this->lineItemRepository,
            $this->resultRepository,
            null,
            null,
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

        $request = $this->createServerRequest(
            'GET',
            $lineItem->getIdentifier() . '/results',
            [],
            [
                'Accept' => ResultServiceInterface::CONTENT_TYPE_RESULT_CONTAINER
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
        $this->assertEquals(200, $response->getStatusCode());

        $result = new ResultContainer(
            $this->serializer->deserialize($response->getBody()->__toString()),
            $response->getHeaderLine(ResultServiceInterface::HEADER_LINK)
        );

        $this->assertEquals(3, $result->getResults()->count());
        $this->assertFalse($result->hasNext());

        $this->assertTrue($result->getResults()->has('https://example.com/line-items/lineItemIdentifier/results/resultIdentifier'));
        $this->assertTrue($result->getResults()->has('https://example.com/line-items/lineItemIdentifier/results/resultIdentifier2'));
        $this->assertTrue($result->getResults()->has('https://example.com/line-items/lineItemIdentifier/results/resultIdentifier3'));

        $this->assertTrue($this->logger->hasLog(LogLevel::INFO, 'AGS result service success'));
    }

    public function testRequestHandlingSuccessWithUserIdentifierFilter(): void
    {
        $registration = $this->createTestRegistration();
        $lineItem = $this->createTestLineItem();

        $request = $this->createServerRequest(
            'GET',
            $lineItem->getIdentifier() . '/results?user_id=resultUserIdentifier',
            [],
            [
                'Accept' => ResultServiceInterface::CONTENT_TYPE_RESULT_CONTAINER
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
        $this->assertEquals(200, $response->getStatusCode());

        $result = new ResultContainer(
            $this->serializer->deserialize($response->getBody()->__toString()),
            $response->getHeaderLine(ResultServiceInterface::HEADER_LINK)
        );

        $this->assertEquals(1, $result->getResults()->count());
        $this->assertFalse($result->hasNext());

        $this->assertFalse($result->getResults()->has('https://example.com/line-items/lineItemIdentifier/results/resultIdentifier'));
        $this->assertFalse($result->getResults()->has('https://example.com/line-items/lineItemIdentifier/results/resultIdentifier2'));
        $this->assertTrue($result->getResults()->has('https://example.com/line-items/lineItemIdentifier/results/resultIdentifier3'));

        $this->assertTrue($this->logger->hasLog(LogLevel::INFO, 'AGS result service success'));
    }

    public function testRequestHandlingSuccessWithPagination(): void
    {
        $registration = $this->createTestRegistration();
        $lineItem = $this->createTestLineItem();

        $request = $this->createServerRequest(
            'GET',
            $lineItem->getIdentifier() . '/results?limit=1&offset=1',
            [],
            [
                'Accept' => ResultServiceInterface::CONTENT_TYPE_RESULT_CONTAINER
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
        $this->assertEquals(200, $response->getStatusCode());

        $result = new ResultContainer(
            $this->serializer->deserialize($response->getBody()->__toString()),
            $response->getHeaderLine(LineItemServiceInterface::HEADER_LINK)
        );

        $this->assertEquals(1, $result->getResults()->count());
        $this->assertTrue($result->hasNext());
        $this->assertEquals($lineItem->getIdentifier() . '/results?limit=1&offset=2', $result->getRelationLinkUrl());

        $this->assertFalse($result->getResults()->has('https://example.com/line-items/lineItemIdentifier/results/resultIdentifier'));
        $this->assertTrue($result->getResults()->has('https://example.com/line-items/lineItemIdentifier/results/resultIdentifier2'));
        $this->assertFalse($result->getResults()->has('https://example.com/line-items/lineItemIdentifier/results/resultIdentifier3'));

        $this->assertTrue($this->logger->hasLog(LogLevel::INFO, 'AGS result service success'));
    }

    public function testRequestHandlingErrorOnLineItemNotFound(): void
    {
        $registration = $this->createTestRegistration();

        $request = $this->createServerRequest(
            'GET',
            'https://example.com/line-items/invalid/results',
            [],
            [
                'Accept' => ResultServiceInterface::CONTENT_TYPE_RESULT_CONTAINER
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

    public function testRequestHandlingErrorOnInvalidHttpMethod(): void
    {
        $lineItem = $this->createTestLineItem();

        $request = $this->createServerRequest(
            'POST',
            $lineItem->getIdentifier() . '/results',
            [],
            [
                'Accept' => ResultServiceInterface::CONTENT_TYPE_RESULT_CONTAINER
            ]
        );

        $this->validatorMock
            ->expects($this->never())
            ->method('validate');

        $response = $this->server->handle($request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(405, $response->getStatusCode());

        $errorMessage = 'Not acceptable request method, accepts: [get]';

        $this->assertEquals($errorMessage, $response->getBody()->__toString());
        $this->assertTrue($this->logger->hasLog(LogLevel::ERROR, $errorMessage));
    }

    public function testRequestHandlingErrorOnInvalidContentType(): void
    {
        $lineItem = $this->createTestLineItem();

        $request = $this->createServerRequest(
            'GET',
            $lineItem->getIdentifier() . '/results',
            [],
            [
                'Accept' => 'invalid'
            ]
        );

        $this->validatorMock
            ->expects($this->never())
            ->method('validate');

        $response = $this->server->handle($request);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(406, $response->getStatusCode());

        $errorMessage = 'Not acceptable request content type, accepts: application/vnd.ims.lis.v2.resultcontainer+json';

        $this->assertEquals($errorMessage, $response->getBody()->__toString());
        $this->assertTrue($this->logger->hasLog(LogLevel::ERROR, $errorMessage));
    }

    public function testRequestHandlingErrorOnInvalidToken(): void
    {
        $registration = $this->createTestRegistration();
        $lineItem = $this->createTestLineItem();

        $errorMessage = 'token validation error';

        $request = $this->createServerRequest(
            'GET',
            $lineItem->getIdentifier() . '/results',
            [],
            [
                'Accept' => ResultServiceInterface::CONTENT_TYPE_RESULT_CONTAINER
            ]
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
