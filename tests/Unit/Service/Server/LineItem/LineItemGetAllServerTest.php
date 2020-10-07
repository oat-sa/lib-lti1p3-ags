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
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemContainerInterface;
use OAT\Library\Lti1p3Ags\Serializer\LineItem\Normalizer\LineItemContainerNormalizerInterface;
use OAT\Library\Lti1p3Ags\Service\LineItem\LineItemGetServiceInterface;
use OAT\Library\Lti1p3Ags\Service\Server\LineItem\LineItemGetAllServer;
use OAT\Library\Lti1p3Ags\Service\Server\Parser\UrlParserInterface;
use OAT\Library\Lti1p3Ags\Service\Server\RequestValidator\RequestValidatorException;
use OAT\Library\Lti1p3Ags\Service\Server\RequestValidator\RequestValidatorInterface;
use OAT\Library\Lti1p3Ags\Tests\Unit\Traits\ServerRequestPathTestingTrait;
use OAT\Library\Lti1p3Core\Service\Server\Validator\AccessTokenRequestValidator;
use PHPUnit\Framework\TestCase;

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

    /** @var LineItemContainerNormalizerInterface  */
    private $lineItemContainerNormalizer;

    public function setUp(): void
    {
        $this->validator = $this->createMock(AccessTokenRequestValidator::class);
        $this->service = $this->createMock(LineItemGetServiceInterface::class);
        $this->parser = $this->createMock(UrlParserInterface::class);
        $this->lineItemContainerNormalizer = $this->createMock(LineItemContainerNormalizerInterface::class);

        $this->subject = new LineItemGetAllServer(
            $this->validator,
            $this->service,
            $this->parser,
            $this->lineItemContainerNormalizer
        );
    }

    public function testAccessTokenValidationFailed(): void
    {
        $bodyContent = 'error-message';

        $this->validator
            ->method('validate')
            ->willThrowException(new RequestValidatorException($bodyContent, 401));

        $response = $this->subject->handle(
            $this->getMockForServerRequestWithPath('/toto')
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
            $this->getMockForServerRequestWithPath('/toto')
        );

        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame('Internal server error.', (string) $response->getBody());
    }

    public function testHttpMethodValidationFailed(): void
    {
        $this->validator->method('validate');

        $response = $this->subject->handle(
            $this->getMockForServerRequestWithPath('/toto', 'post')
        );

        $this->assertSame(405, $response->getStatusCode());
        $this->assertSame('Method not allowed', $response->getReasonPhrase());
        $this->assertSame('Expected http method is "get".', (string) $response->getBody());
    }

    public function testRequiredContextIdValidationFailed(): void
    {
        $this->validator->method('validate');

        $response = $this->subject->handle(
            $this->getMockForServerRequestWithPath('/')
        );

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Bad Request', $response->getReasonPhrase());
        $this->assertSame('Url path must contain contextId as first uri path part.', (string) $response->getBody());
    }

    public function testFindAll(): void
    {
        $requestParameters = [
            'contextId' => 'toto',
            'page' => 1,
            'limit' => 50,
            'resource_link_id' => 'test-resource-link-id',
            'tag' => 'test-tag',
            'resource_id' => 'test-resource-id'
        ];
        $normalizedLineItem = ['encoded-line-item'];

        $lineItemContainer = $this->createMock(LineItemContainerInterface::class);
        $expectedEncodedLineItem = json_encode($normalizedLineItem);

        $this->validator->method('validate');
        $this->parser
            ->expects($this->once())
            ->method('parse')
            ->willReturn($requestParameters);

        $this->service
            ->expects($this->once())
            ->method('findAll')
            ->with(...array_values($requestParameters))
            ->willReturn($lineItemContainer);

        $this->lineItemContainerNormalizer
            ->expects($this->once())
            ->method('normalize')
            ->with($lineItemContainer)
            ->willReturn($normalizedLineItem);

        $response = $this->subject->handle(
            $this->getMockForServerRequestWithPath('/context-id')
        );

        $this->assertSame($expectedEncodedLineItem, (string) $response->getBody());
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertSame((string) strlen($expectedEncodedLineItem), $response->getHeaderLine('Content-length'));
    }
}
