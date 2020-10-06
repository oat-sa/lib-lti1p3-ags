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
use Http\Message\ResponseFactory;
use OAT\Library\Lti1p3Ags\Model\LineItem;
use OAT\Library\Lti1p3Ags\Model\LineItemContainer;
use OAT\Library\Lti1p3Ags\Model\PartialLineItemContainer;
use OAT\Library\Lti1p3Ags\Serializer\Normalizer\Platform\LineItemContainerNormalizerInterface;
use OAT\Library\Lti1p3Ags\Serializer\Normalizer\Platform\LineItemNormalizerInterface;
use OAT\Library\Lti1p3Ags\Serializer\Normalizer\Platform\LineItemQueryDenormalizer;
use OAT\Library\Lti1p3Ags\Serializer\Normalizer\Platform\LineItemQueryDenormalizerInterface;
use OAT\Library\Lti1p3Ags\Service\LineItem\LineItemGetServiceInterface;
use OAT\Library\Lti1p3Ags\Service\LineItem\Query\LineItemQuery;
use OAT\Library\Lti1p3Ags\Service\Server\LineItem\LineItemGetServer;
use OAT\Library\Lti1p3Ags\Service\Server\Parser\UrlParserInterface;
use OAT\Library\Lti1p3Ags\Service\Server\RequestValidator\RequestValidatorException;
use OAT\Library\Lti1p3Ags\Service\Server\RequestValidator\RequestValidatorInterface;
use OAT\Library\Lti1p3Ags\Tests\Unit\Traits\ServerRequestPathTestingTrait;
use OAT\Library\Lti1p3Core\Service\Server\Validator\AccessTokenRequestValidator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

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

    /** @var LineItemQueryDenormalizer  */
    private $queryDenormalizer;

    /** @var LineItemNormalizerInterface  */
    private $lineItemNormalizer;

    /** @var LineItemContainerNormalizerInterface  */
    private $lineItemContainerNormalizer;

    /** @var ResponseFactory */
    private $factory;

    /** @var LoggerInterface */
    private $logger;

    public function setUp()
    {
        $this->validator = $this->createMock(AccessTokenRequestValidator::class);
        $this->service = $this->createMock(LineItemGetServiceInterface::class);
        $this->parser = $this->createMock(UrlParserInterface::class);
        $this->queryDenormalizer = $this->createMock(LineItemQueryDenormalizerInterface::class);
        $this->lineItemNormalizer = $this->createMock(LineItemNormalizerInterface::class);
        $this->lineItemContainerNormalizer = $this->createMock(LineItemContainerNormalizerInterface::class);

        $this->subject = new LineItemGetServer(
            $this->validator,
            $this->service,
            $this->parser,
            $this->queryDenormalizer,
            $this->lineItemNormalizer,
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

    public function testFindOne(): void
    {
        $this->provideMocks(true);

        $lineItem = $this->createMock(LineItem::class);
        $normalizedLineItem = ['encoded-line-item'];

        $expectedEncodedLineItem = json_encode($normalizedLineItem);

        $this->service
            ->expects($this->once())
            ->method('findOne')
            ->willReturn($lineItem);

        $this->lineItemNormalizer
            ->expects($this->once())
            ->method('normalize')
            ->with($lineItem)
            ->willReturn($normalizedLineItem);

        $this->lineItemContainerNormalizer
            ->expects($this->never())
            ->method('normalize');

        $response = $this->subject->handle(
            $this->getMockForServerRequestWithPath('/context-id')
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertSame((string) strlen($expectedEncodedLineItem), $response->getHeaderLine('Content-length'));
        $this->assertSame($expectedEncodedLineItem, (string) $response->getBody());
    }

    public function testFindAllWithFullList(): void
    {
        $this->provideTestForFindAll(
            $this->createMock(LineItemContainer::class),
            200
        );
    }

    public function testFindAllWithParialList(): void
    {
        $this->provideTestForFindAll(
            $this->createMock(PartialLineItemContainer::class),
            206
        );
    }

    private function provideMocks(bool $hasLineItemId): void
    {
        $requestParameters = ['some-parameters'];

        $this->validator->method('validate');
        $this->parser
            ->expects($this->once())
            ->method('parse')
            ->willReturn($requestParameters);

        $query = $this->createMock(LineItemQuery::class);
        $query
            ->expects($this->once())
            ->method('hasLineItemId')
            ->willReturn($hasLineItemId);

        $this->queryDenormalizer
            ->expects($this->once())
            ->method('denormalize')
            ->with($requestParameters)
            ->willReturn($query);
    }

    private function provideTestForFindAll(LineItemContainer $lineItemContainer, int $expectedCode): void
    {
        $this->provideMocks(false);

        $normalizedLineItem = ['encoded-line-item'];

        $expectedEncodedLineItem = json_encode($normalizedLineItem);

        $this->service
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($lineItemContainer);

        $this->lineItemContainerNormalizer
            ->expects($this->once())
            ->method('normalize')
            ->with($lineItemContainer)
            ->willReturn($normalizedLineItem);

        $this->lineItemNormalizer
            ->expects($this->never())
            ->method('normalize');

        $response = $this->subject->handle(
            $this->getMockForServerRequestWithPath('/context-id')
        );

        $this->assertSame($expectedEncodedLineItem, (string) $response->getBody());
        $this->assertSame($expectedCode, $response->getStatusCode());
        $this->assertSame('application/json', $response->getHeaderLine('Content-Type'));
        $this->assertSame((string) strlen($expectedEncodedLineItem), $response->getHeaderLine('Content-length'));
    }
}
