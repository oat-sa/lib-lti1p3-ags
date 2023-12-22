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

namespace OAT\Library\Lti1p3Ags\Tests\Integration\Service\LineItem\Server\Handler;

use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemContainer;
use OAT\Library\Lti1p3Ags\Repository\LineItemRepositoryInterface;
use OAT\Library\Lti1p3Ags\Serializer\LineItem\LineItemCollectionSerializer;
use OAT\Library\Lti1p3Ags\Serializer\LineItem\LineItemCollectionSerializerInterface;
use OAT\Library\Lti1p3Ags\Service\LineItem\LineItemServiceInterface;
use OAT\Library\Lti1p3Ags\Service\LineItem\Server\Handler\ListLineItemsServiceServerRequestHandler;
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

class ListLineItemsServiceServerRequestHandlerTest extends TestCase
{
    use AgsDomainTestingTrait;
    use DomainTestingTrait;
    use NetworkTestingTrait;

    /** @var RequestAccessTokenValidator|MockObject */
    private $validatorMock;

    /** @var LineItemRepositoryInterface */
    private $repository;

    /** @var LineItemCollectionSerializerInterface */
    private $serializer;

    /** @var TestLogger */
    private $logger;

    /** @var ListLineItemsServiceServerRequestHandler */
    private $subject;

    /** @var LtiServiceServer */
    private $server;

    protected function setUp(): void
    {
        $this->validatorMock = $this->createMock(RequestAccessTokenValidator::class);
        $this->repository = $this->createTestLineItemRepository();
        $this->serializer = new LineItemCollectionSerializer();
        $this->logger = new TestLogger();

        $this->subject = new ListLineItemsServiceServerRequestHandler($this->repository);

        $this->server = new LtiServiceServer(
            $this->validatorMock,
            $this->subject,
            $this->logger
        );
    }

    public function testRequestHandlingSuccess(): void
    {
        $registration = $this->createTestRegistration();

        $request = $this->createServerRequest(
            'GET',
            'https://example.com/line-items',
            [],
            [
                'Accept' => LineItemServiceInterface::CONTENT_TYPE_LINE_ITEM_CONTAINER
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

        $result = new LineItemContainer(
            $this->serializer->deserialize($response->getBody()->__toString()),
            $response->getHeaderLine(LineItemServiceInterface::HEADER_LINK)
        );

        $this->assertEquals(3, $result->getLineItems()->count());
        $this->assertFalse($result->hasNext());

        $this->assertTrue($result->getLineItems()->has('https://example.com/line-items/lineItemIdentifier'));
        $this->assertTrue($result->getLineItems()->has('https://example.com/line-items/lineItemIdentifier2'));
        $this->assertTrue($result->getLineItems()->has('https://example.com/line-items/lineItemIdentifier3'));

        $this->assertTrue($this->logger->hasLog(LogLevel::INFO, 'AGS line item service success'));
    }

    public function testRequestHandlingSuccessWithResourceIdentifierFilter(): void
    {
        $registration = $this->createTestRegistration();

        $request = $this->createServerRequest(
            'GET',
            'https://example.com/line-items?resource_id=lineItemResourceIdentifier2',
            [],
            [
                'Accept' => LineItemServiceInterface::CONTENT_TYPE_LINE_ITEM_CONTAINER
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

        $result = new LineItemContainer(
            $this->serializer->deserialize($response->getBody()->__toString()),
            $response->getHeaderLine(LineItemServiceInterface::HEADER_LINK)
        );

        $this->assertEquals(1, $result->getLineItems()->count());
        $this->assertFalse($result->hasNext());

        $this->assertFalse($result->getLineItems()->has('https://example.com/line-items/lineItemIdentifier'));
        $this->assertTrue($result->getLineItems()->has('https://example.com/line-items/lineItemIdentifier2'));
        $this->assertFalse($result->getLineItems()->has('https://example.com/line-items/lineItemIdentifier3'));

        $this->assertTrue($this->logger->hasLog(LogLevel::INFO, 'AGS line item service success'));
    }

    public function testRequestHandlingSuccessWithResourceLinkIdentifierFilter(): void
    {
        $registration = $this->createTestRegistration();

        $request = $this->createServerRequest(
            'GET',
            'https://example.com/line-items?resource_link_id=lineItemResourceLinkIdentifier3',
            [],
            [
                'Accept' => LineItemServiceInterface::CONTENT_TYPE_LINE_ITEM_CONTAINER
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

        $result = new LineItemContainer(
            $this->serializer->deserialize($response->getBody()->__toString()),
            $response->getHeaderLine(LineItemServiceInterface::HEADER_LINK)
        );

        $this->assertEquals(1, $result->getLineItems()->count());
        $this->assertFalse($result->hasNext());

        $this->assertFalse($result->getLineItems()->has('https://example.com/line-items/lineItemIdentifier'));
        $this->assertFalse($result->getLineItems()->has('https://example.com/line-items/lineItemIdentifier2'));
        $this->assertTrue($result->getLineItems()->has('https://example.com/line-items/lineItemIdentifier3'));

        $this->assertTrue($this->logger->hasLog(LogLevel::INFO, 'AGS line item service success'));
    }

    public function testRequestHandlingSuccessWithTagFilter(): void
    {
        $registration = $this->createTestRegistration();

        $request = $this->createServerRequest(
            'GET',
            'https://example.com/line-items?tag=lineItemTag2',
            [],
            [
                'Accept' => LineItemServiceInterface::CONTENT_TYPE_LINE_ITEM_CONTAINER
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

        $result = new LineItemContainer(
            $this->serializer->deserialize($response->getBody()->__toString()),
            $response->getHeaderLine(LineItemServiceInterface::HEADER_LINK)
        );

        $this->assertEquals(1, $result->getLineItems()->count());
        $this->assertFalse($result->hasNext());

        $this->assertFalse($result->getLineItems()->has('https://example.com/line-items/lineItemIdentifier'));
        $this->assertTrue($result->getLineItems()->has('https://example.com/line-items/lineItemIdentifier2'));
        $this->assertFalse($result->getLineItems()->has('https://example.com/line-items/lineItemIdentifier3'));

        $this->assertTrue($this->logger->hasLog(LogLevel::INFO, 'AGS line item service success'));
    }

    public function testRequestHandlingSuccessWithPagination(): void
    {
        $registration = $this->createTestRegistration();

        $request = $this->createServerRequest(
            'GET',
            'https://example.com/line-items?limit=1&offset=1',
            [],
            [
                'Accept' => LineItemServiceInterface::CONTENT_TYPE_LINE_ITEM_CONTAINER
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

        $result = new LineItemContainer(
            $this->serializer->deserialize($response->getBody()->__toString()),
            $response->getHeaderLine(LineItemServiceInterface::HEADER_LINK)
        );

        $this->assertEquals(1, $result->getLineItems()->count());
        $this->assertTrue($result->hasNext());
        $this->assertEquals('https://example.com/line-items?limit=1&offset=2', $result->getRelationLinkUrl());

        $this->assertFalse($result->getLineItems()->has('https://example.com/line-items/lineItemIdentifier'));
        $this->assertTrue($result->getLineItems()->has('https://example.com/line-items/lineItemIdentifier2'));
        $this->assertFalse($result->getLineItems()->has('https://example.com/line-items/lineItemIdentifier3'));

        $this->assertTrue($this->logger->hasLog(LogLevel::INFO, 'AGS line item service success'));
    }

    public function testRequestHandlingErrorOnInvalidHttpMethod(): void
    {
        $request = $this->createServerRequest(
            'POST',
            'https://example.com/line-items',
            [],
            [
                'Accept' => LineItemServiceInterface::CONTENT_TYPE_LINE_ITEM_CONTAINER
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
        $request = $this->createServerRequest(
            'GET',
            'https://example.com/line-items',
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

        $errorMessage = 'Not acceptable request content type, accepts: application/vnd.ims.lis.v2.lineitemcontainer+json';

        $this->assertEquals($errorMessage, $response->getBody()->__toString());
        $this->assertTrue($this->logger->hasLog(LogLevel::ERROR, $errorMessage));
    }

    public function testRequestHandlingErrorOnInvalidToken(): void
    {
        $registration = $this->createTestRegistration();

        $errorMessage = 'token validation error';

        $request = $this->createServerRequest(
            'GET',
            'https://example.com/line-items',
            [],
            [
                'Accept' => LineItemServiceInterface::CONTENT_TYPE_LINE_ITEM_CONTAINER
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
