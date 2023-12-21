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

use Nyholm\Psr7\ServerRequest;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemInterface;
use OAT\Library\Lti1p3Ags\Repository\LineItemRepositoryInterface;
use OAT\Library\Lti1p3Ags\Serializer\LineItem\LineItemSerializer;
use OAT\Library\Lti1p3Ags\Serializer\LineItem\LineItemSerializerInterface;
use OAT\Library\Lti1p3Ags\Service\LineItem\LineItemServiceInterface;
use OAT\Library\Lti1p3Ags\Service\LineItem\Server\Handler\CreateLineItemServiceServerRequestHandler;
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

class CreateLineItemServiceServerRequestHandlerTest extends TestCase
{
    use AgsDomainTestingTrait;
    use DomainTestingTrait;
    use NetworkTestingTrait;

    /** @var RequestAccessTokenValidator|MockObject */
    private $validatorMock;

    /** @var LineItemRepositoryInterface */
    private $repository;

    /** @var LineItemSerializerInterface */
    private $serializer;

    /** @var TestLogger */
    private $logger;

    /** @var CreateLineItemServiceServerRequestHandler */
    private $subject;

    /** @var LtiServiceServer */
    private $server;

    protected function setUp(): void
    {
        $this->validatorMock = $this->createMock(RequestAccessTokenValidator::class);
        $this->repository = $this->createTestLineItemRepository();
        $this->serializer = new LineItemSerializer();
        $this->logger = new TestLogger();

        $this->subject = new CreateLineItemServiceServerRequestHandler(
            $this->repository,
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

        $lineItem = $this
            ->createTestLineItem()
            ->setIdentifier(null);

        $request = new ServerRequest(
            'POST',
            'https://example.com/line-items',
            [
                'Content-Type' => LineItemServiceInterface::CONTENT_TYPE_LINE_ITEM,
            ],
            $this->serializer->serialize($lineItem)
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

        $result = $this->serializer->deserialize($response->getBody()->__toString());

        $this->assertInstanceOf(LineItemInterface::class, $result);
        $this->assertNotNull($result->getIdentifier());

        $this->assertEquals($result, $this->repository->find($result->getIdentifier()));

        $this->assertTrue($this->logger->hasLog(LogLevel::INFO, 'AGS line item service success'));
    }

    public function testRequestHandlingErrorOnInvalidLineItem(): void
    {
        $registration = $this->createTestRegistration();

        $request = new ServerRequest(
            'POST',
            'https://example.com/line-items',
            [
                'Content-Type' => LineItemServiceInterface::CONTENT_TYPE_LINE_ITEM,
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

        $errorMessage = 'Error during line item deserialization: Syntax error';

        $this->assertEquals($errorMessage, $response->getBody()->__toString());
        $this->assertTrue($this->logger->hasLog(LogLevel::ERROR, $errorMessage));
    }

    public function testRequestHandlingErrorOnInvalidHttpMethod(): void
    {
        $lineItem = $this->createTestLineItem();

        $request = new ServerRequest(
            'GET',
            'https://example.com/line-items',
            [
                'Content-Type' => LineItemServiceInterface::CONTENT_TYPE_LINE_ITEM,
            ],
            $this->serializer->serialize($lineItem)
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
        $request = $this->createServerRequest(
            'POST',
            'https://example.com/line-items',
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

        $errorMessage = 'Not acceptable request content type, accepts: application/vnd.ims.lis.v2.lineitem+json';

        $this->assertEquals($errorMessage, $response->getBody()->__toString());
        $this->assertTrue($this->logger->hasLog(LogLevel::ERROR, $errorMessage));
    }

    public function testRequestHandlingErrorOnInvalidToken(): void
    {
        $registration = $this->createTestRegistration();
        $lineItem = $this->createTestLineItem();

        $errorMessage = 'token validation error';

        $request = new ServerRequest(
            'POST',
            'https://example.com/line-items',
            [
                'Content-Type' => LineItemServiceInterface::CONTENT_TYPE_LINE_ITEM,
            ],
            $this->serializer->serialize($lineItem)
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
