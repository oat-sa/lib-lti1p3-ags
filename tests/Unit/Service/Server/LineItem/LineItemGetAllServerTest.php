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
use OAT\Library\Lti1p3Ags\Repository\LineItemRepositoryInterface;
use OAT\Library\Lti1p3Ags\Service\Server\LineItem\LineItemGetAllServer;
use OAT\Library\Lti1p3Ags\Service\Server\Parser\UrlParserInterface;
use OAT\Library\Lti1p3Ags\Service\Server\RequestValidator\AccessTokenRequestValidatorDecorator;
use OAT\Library\Lti1p3Ags\Service\Server\RequestValidator\RequestValidatorException;
use OAT\Library\Lti1p3Ags\Service\Server\RequestValidator\RequestValidatorInterface;
use OAT\Library\Lti1p3Core\Service\Server\Validator\AccessTokenRequestValidationResult;
use OAT\Library\Lti1p3Core\Service\Server\Validator\AccessTokenRequestValidator;
use OAT\Library\Lti1p3Core\Tests\Traits\NetworkTestingTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class LineItemGetAllServerTest extends TestCase
{
    use NetworkTestingTrait;

    /** @var LineItemGetAllServer */
    private $subject;

    /** @var RequestValidatorInterface */
    private $validator;

    /** @var LineItemRepositoryInterface  */
    private $repository;

    /** @var UrlParserInterface  */
    private $parser;

    public function setUp(): void
    {
        $this->validator = $this->createMock(AccessTokenRequestValidator::class);
        $this->repository = $this->createMock(LineItemRepositoryInterface::class);
        $this->parser = $this->createMock(UrlParserInterface::class);

        $this->subject = new LineItemGetAllServer(
            $this->validator,
            $this->repository,
            $this->parser
        );
    }

    public function testAccessTokenValidationFailed(): void
    {
        $bodyContent = 'error-message';

        $this->validator
            ->method('validate')
            ->willThrowException(new RequestValidatorException($bodyContent, 401));

        $response = $this->subject->handle(
            $this->createServerRequest('GET', '/toto')
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
            $this->createServerRequest('GET', '/toto')
        );

        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame('Internal server error.', (string) $response->getBody());
    }

    public function testHttpMethodValidationFailed(): void
    {
        $this->mockValidationWithScopes();

        $response = $this->subject->handle(
            $this->createServerRequest('POST', '/toto')
        );

        $this->assertSame(405, $response->getStatusCode());
        $this->assertSame('Method not allowed', $response->getReasonPhrase());
        $this->assertSame('Expected http method is "get".', (string) $response->getBody());
    }

    public function testRequiredContextIdValidationFailed(): void
    {
        $this->mockValidationWithScopes();

        $response = $this->subject->handle(
            $this->createServerRequest('GET', '/')
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

        $expectedServiceParameters = [
            'toto',
            1,
            50,
            'test-resource-link-id',
            'test-tag',
            'test-resource-id'
        ];

        $lineItemContainer = ['encoded-line-item'];
        $serializedLineItemContainer = json_encode($lineItemContainer);

        $requestQuery = [
            'page' => 1,
            'limit' => 50,
            'resource_link_id' => 'test-resource-link-id',
            'tag' => 'test-tag',
            'resource_id' => 'test-resource-id'
        ];

        $request = $this->createServerRequest('GET', '/context-id', $requestQuery);

        $this->provideMockForFindAll(
            $request,
            $lineItemContainer,
            $requestParameters,
            $expectedServiceParameters,
            null
        );

        $this->mockValidationWithScopes();

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
        $lineItemContainer = ['encoded-line-item'];
        $serializedLineItemContainer = json_encode($lineItemContainer);
        $relationLink = 'relation-link-string';

        $request = $this->createServerRequest('GET', '/context-id');

        $this->provideMockForFindAll(
            $request,
            $lineItemContainer,
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
        array $serializedLineItemContainer,
        array $requestParameters,
        array $expectedServiceParameters,
        string $relationLink = null
    ): void {

        $lineItemContainer = $this->createMock(LineItemContainerInterface::class);
        $lineItemContainer
            ->expects($this->exactly($relationLink ? 2 : 1))
            ->method('getRelationLink')
            ->willReturn($relationLink);

        $lineItemContainer
            ->expects($this->once())
            ->method('jsonSerialize')
            ->willReturn($serializedLineItemContainer);

        $this->mockValidationWithScopes();

        $this->parser
            ->expects($this->once())
            ->method('parse')
            ->with($request)
            ->willReturn($requestParameters);

        $this->repository
            ->expects($this->once())
            ->method('findAll')
            ->with(...$expectedServiceParameters)
            ->willReturn($lineItemContainer);
    }

    private function mockValidationWithScopes(): void
    {
        $validationResult = $this->createMock(AccessTokenRequestValidationResult::class);

        $validationResult
            ->method('hasError')
            ->willReturn(false);

        $validationResult
            ->method('getScopes')
            ->willReturn([AccessTokenRequestValidatorDecorator::SCOPE_LINE_ITEM]);

        $this->validator
            ->method('validate')
            ->willReturn($validationResult);
    }
}
