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
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemInterface;
use OAT\Library\Lti1p3Ags\Serializer\LineItem\LineItemSerializerInterface;
use OAT\Library\Lti1p3Ags\Service\LineItem\LineItemGetServiceInterface;
use OAT\Library\Lti1p3Ags\Service\Server\LineItem\LineItemGetServer;
use OAT\Library\Lti1p3Ags\Service\Server\Parser\UrlParserInterface;
use OAT\Library\Lti1p3Ags\Service\Server\RequestValidator\RequestValidatorInterface;
use OAT\Library\Lti1p3Ags\Tests\Unit\Traits\ServerRequestPathTestingTrait;
use OAT\Library\Lti1p3Core\Service\Server\Validator\AccessTokenRequestValidator;
use PHPUnit\Framework\TestCase;

class LineItemGetServerTest extends TestCase
{
    use ServerRequestPathTestingTrait;

    /** @var LineItemGetServer */
    private $subject;

    /** @var RequestValidatorInterface */
    private $validator;

    /** @var LineItemGetServiceInterface  */
    private $service;

    /** @var UrlParserInterface  */
    private $parser;

    /** @var LineItemSerializerInterface  */
    private $lineItemSerializer;

    public function setUp(): void
    {
        $this->validator = $this->createMock(AccessTokenRequestValidator::class);
        $this->service = $this->createMock(LineItemGetServiceInterface::class);
        $this->parser = $this->createMock(UrlParserInterface::class);
        $this->lineItemSerializer = $this->createMock(LineItemSerializerInterface::class);

        $this->subject = new LineItemGetServer(
            $this->validator,
            $this->service,
            $this->parser,
            $this->lineItemSerializer
        );
    }

    public function testRequiredLineItemIdValidationFailed(): void
    {
        $this->validator->method('validate');

        $response = $this->subject->handle(
            $this->getMockForServerRequest('/without/lineItemId')
        );

        $this->assertSame(400, $response->getStatusCode());
        $this->assertSame('Bad Request', $response->getReasonPhrase());
        $this->assertSame('Url path must contain lineItemId as third uri path part.', (string) $response->getBody());
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

    public function testFindOne(): void
    {
        $requestParameters = [
            'contextId' => 'toto',
            'lineItemId' => 'titi'
        ];

        $lineItem = $this->createMock(LineItemInterface::class);
        $expectedEncodedLineItem = json_encode(['encoded-line-item']);

        $this->validator->method('validate');
        $this->parser
            ->expects($this->once())
            ->method('parse')
            ->willReturn($requestParameters);

        $this->service
            ->expects($this->once())
            ->method('findOne')
            ->willReturn($lineItem);

        $this->lineItemSerializer
            ->expects($this->once())
            ->method('serialize')
            ->with($lineItem)
            ->willReturn($expectedEncodedLineItem);

        $response = $this->subject->handle(
            $this->getMockForServerRequest('/context-id/lineItem/line-item-id')
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertSame((string) strlen($expectedEncodedLineItem), $response->getHeaderLine('Content-length'));
        $this->assertSame($expectedEncodedLineItem, (string) $response->getBody());
    }
}
