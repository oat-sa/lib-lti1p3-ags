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

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Service\Server\LineItem;

use Exception;
use OAT\Library\Lti1p3Ags\Model\LineItemContainer\LineItemContainerInterface;
use OAT\Library\Lti1p3Ags\Serializer\LineItemContainer\LineItemContainerSerializer;
use OAT\Library\Lti1p3Ags\Service\LineItem\LineItemGetServiceInterface;
use OAT\Library\Lti1p3Ags\Service\Server\LineItem\LineItemGetAllServer;
use OAT\Library\Lti1p3Ags\Service\Server\Parser\UrlParserInterface;
use OAT\Library\Lti1p3Ags\Service\Server\RequestValidator\RequestValidatorException;
use OAT\Library\Lti1p3Ags\Service\Server\RequestValidator\RequestValidatorInterface;
use OAT\Library\Lti1p3Ags\Tests\Unit\Traits\ServerRequestPathTestingTrait;
use OAT\Library\Lti1p3Core\Service\Server\Validator\AccessTokenRequestValidator;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class LineItemGetAllServerTest extends TestCase
{
    use ServerRequestPathTestingTrait;

    /** @var LineItemGetAllServer */
    private $subject;

    /** @var RequestValidatorInterface */
    private $validator;

    /** @var LineItemGetServiceInterface  */
    private $service;

    /** @var UrlParserInterface  */
    private $parser;

    /** @var LineItemContainerSerializer  */
    private $lineItemContainerSerializer;

    public function setUp(): void
    {
        $this->validator = $this->createMock(AccessTokenRequestValidator::class);
        $this->service = $this->createMock(LineItemGetServiceInterface::class);
        $this->parser = $this->createMock(UrlParserInterface::class);
        $this->lineItemContainerSerializer = $this->createMock(LineItemContainerSerializer::class);

        $this->subject = new LineItemGetAllServer(
            $this->validator,
            $this->service,
            $this->parser,
            $this->lineItemContainerSerializer
        );
    }

    public function testAccessTokenValidationFailed(): void
    {
        $bodyContent = 'error-message';

        $this->validator
            ->method('validate')
            ->willThrowException(new RequestValidatorException($bodyContent, 401));

        $response = $this->subject->handle(
            $this->getMockForServerRequest('/toto')
        );

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('Unauthorized', $response->getReasonPhrase());
        $this->assertSame($bodyContent, (string) $response->getBody());
    }

    public function testInternalError(): void
    {
        $this->validator
            ->method('validate')
            ->willThrowException(new Exception());

        $response = $this->subject->handle(
            $this->getMockForServerRequest('/toto')
        );

        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame('Internal server error.', (string) $response->getBody());
    }

    public function testHttpMethodValidationFailed(): void
    {
        $this->validator->method('validate');

        $response = $this->subject->handle(
            $this->getMockForServerRequest('/toto', 'post')
        );

        $this->assertSame(405, $response->getStatusCode());
        $this->assertSame('Method not allowed', $response->getReasonPhrase());
        $this->assertSame('Expected http method is "get".', (string) $response->getBody());
    }

    public function testRequiredContextIdValidationFailed(): void
    {
        $this->validator->method('validate');

        $response = $this->subject->handle(
            $this->getMockForServerRequest('/')
        );

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Bad Request', $response->getReasonPhrase());
        $this->assertSame('Url path must contain contextId as first uri path part.', (string) $response->getBody());
    }

    public function testFindAll(): void
    {
        $requestParameters = [
            'contextId' => 'toto',
        ];

        $requestQuery = 'page=1&limit=50&resource_link_id=test-resource-link-id&tag=test-tag&resource_id=test-resource-id';
        $expectedServiceParameters = ['toto',
            1,
            50,
            'test-resource-link-id',
            'test-tag',
            'test-resource-id'
        ];

        $serializedLineItemContainer = json_encode(['encoded-line-item']);

        $request = $this->getMockForServerRequest('/context-id', 'get', $requestQuery);

        $this->provideMockForFindAll(
            $request,
            $serializedLineItemContainer,
            $requestParameters,
            $expectedServiceParameters,
            null
        );

        $response = $this->subject->handle($request);

        $this->assertSame($serializedLineItemContainer, (string) $response->getBody());
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertSame((string) strlen($serializedLineItemContainer), $response->getHeaderLine('Content-length'));
        $this->assertArrayNotHasKey('Link', $response->getHeaders());
    }

    public function testFindAllWithRelationLink(): void
    {
        $requestParameters = [
            'contextId' => 'toto',
        ];
        $expectedServiceParameters = ['toto'];
        $serializedLineItemContainer = json_encode(['encoded-line-item']);
        $relationLink = 'relation-link-string';

        $request = $this->getMockForServerRequest('/context-id');

        $this->provideMockForFindAll(
            $request,
            $serializedLineItemContainer,
            $requestParameters,
            $expectedServiceParameters,
            $relationLink
        );

        $response = $this->subject->handle($request);

        $this->assertSame($serializedLineItemContainer, (string) $response->getBody());
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertSame((string) strlen($serializedLineItemContainer), $response->getHeaderLine('Content-length'));
        $this->assertSame('relation-link-string', $response->getHeaderLine('Link'));
    }

    private function provideMockForFindAll(
        ServerRequestInterface $request,
        string $serializedLineItemContainer,
        array $requestParameters,
        array $expectedServiceParameters,
        string $relationLink = null
    ): void {

        $lineItemContainer = $this->createMock(LineItemContainerInterface::class);
        $lineItemContainer
            ->expects($this->exactly($relationLink ? 2 : 1))
            ->method('getRelationLink')
            ->willReturn($relationLink);

        $this->validator->method('validate');
        $this->parser
            ->expects($this->once())
            ->method('parse')
            ->with($request)
            ->willReturn($requestParameters);

        $this->service
            ->expects($this->once())
            ->method('findAll')
            ->with(...$expectedServiceParameters)
            ->willReturn($lineItemContainer);

        $this->lineItemContainerSerializer
            ->expects($this->once())
            ->method('serialize')
            ->with($lineItemContainer)
            ->willReturn($serializedLineItemContainer);
    }
}
