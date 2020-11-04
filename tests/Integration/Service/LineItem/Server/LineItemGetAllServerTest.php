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

namespace OAT\Library\Lti1p3Ags\Tests\Integration\Service\LineItem\Server;

use Exception;
use GuzzleHttp\Psr7\ServerRequest;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItem;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemCollection;
use OAT\Library\Lti1p3Ags\Model\LineItemContainer\LineItemContainer;
use OAT\Library\Lti1p3Ags\Repository\LineItemRepositoryInterface;
use OAT\Library\Lti1p3Ags\Service\LineItem\LineItemServiceInterface;
use OAT\Library\Lti1p3Ags\Service\LineItem\Server\LineItemGetAllServer;
use OAT\Library\Lti1p3Ags\Service\LineItem\Server\LineItemGetServer;
use OAT\Library\Lti1p3Core\Service\Server\Validator\AccessTokenRequestValidationResult;
use OAT\Library\Lti1p3Core\Service\Server\Validator\AccessTokenRequestValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Throwable;

class LineItemGetAllServerTest extends TestCase
{
    /** @var LineItemGetAllServer */
    private $subject;

    /** @var LineItemRepositoryInterface|MockObject */
    private $repository;

    /** @var AccessTokenRequestValidator|MockObject */
    private $validator;

    public function setUp(): void
    {
        $this->repository = $this->createMock(LineItemRepositoryInterface::class);
        $this->validator = $this->createMock(AccessTokenRequestValidator::class);
        $this->subject = new LineItemGetAllServer(
            $this->validator,
            $this->repository
        );
    }

    /**
     * @dataProvider handleProvider
     */
    public function testHandleWithInvalidRequest(
        int $expectedStatusCode,
        string $expectedResponseBody,
        string $requestMethod,
        string $url,
        Throwable $unhandledException = null,
        string $scope = LineItemServiceInterface::SCOPE_LINE_ITEM
    ): void {
        $validationResult = $this->createMock(AccessTokenRequestValidationResult::class);

        $validationResult
            ->method('hasError')
            ->willReturn(false);

        $validationResult
            ->method('getScopes')
            ->willReturn([$scope]);

        $this->validator
            ->method('validate')
            ->willReturn($validationResult);

        $this->repository
            ->method('findAll')
            ->willReturnCallback(
                function () use ($unhandledException): void {
                    if ($unhandledException) {
                        throw $unhandledException;
                    }
                }
            );

        $response = $this->subject->handle(
            new ServerRequest($requestMethod, $url)
        );

        $this->assertSame($expectedResponseBody, (string)$response->getBody());
        $this->assertEquals($expectedStatusCode, $response->getStatusCode());
    }

    public function handleProvider(): array
    {
        $validUri = sprintf('/%s/lineitems/%s', 'myContextId', 'myLineItemId');

        return [
            'ContextId not Provided' => [
                'expectedStatusCode' => 400,
                'expectedResponseBody' => 'Url path must contain contextId as first uri path part.',
                'requestMethod' => 'GET',
                'url' => '/',
            ],
            'Invalid scope' => [
                'expectedStatusCode' => 401,
                'expectedResponseBody' => 'Only allowed for scope https://purl.imsglobal.org/spec/lti-ags/scope/lineitem',
                'requestMethod' => 'GET',
                'url' => $validUri,
                'unhandledException' => null,
                'scope' => 'another scope'
            ],
            'HTTP method not accepted' => [
                'expectedStatusCode' => 405,
                'expectedResponseBody' => 'Expected http method is "get".',
                'requestMethod' => 'POST',
                'url' => $validUri,
            ],
            'Internal error' => [
                'expectedStatusCode' => 500,
                'expectedResponseBody' => 'Internal AGS service error.',
                'requestMethod' => 'GET',
                'url' => $validUri,
                'unhandledException' => new Exception('Not handled exception')
            ],
        ];
    }

    public function testHandle(): void
    {
        $contextId = 'myContextId';
        $lineItemContainer = $this->createLineItemContainer();
        $queryParameters = $this->getQueryParameters();

        $validationResult = $this->createMock(AccessTokenRequestValidationResult::class);

        $validationResult
            ->method('hasError')
            ->willReturn(false);

        $this->validator
            ->method('validate')
            ->willReturn($validationResult);

        $validationResult
            ->method('getScopes')
            ->willReturn([LineItemServiceInterface::SCOPE_LINE_ITEM]);

        $this->repository
            ->method('findAll')
            ->with(...array_merge([$contextId], array_values($queryParameters)))
            ->willReturn($lineItemContainer)
        ;

        $url = sprintf('/%s/lineitems/', $contextId);

        $request = (new ServerRequest('GET', $url))->withQueryParams($queryParameters);

        $response = $this->subject->handle($request);

        $this->assertSame(json_encode($lineItemContainer), (string)$response->getBody());
        $this->assertSame('myRelationLink', current($response->getHeader('Link')));
        $this->assertEquals(200, $response->getStatusCode());
    }

    private function createLineItemContainer(): LineItemContainer
    {
        return new LineItemContainer(
            new LineItemCollection(...[
                new LineItem(
                    'myContextId',
                    100,
                    'myLabel-1',
                    'myLineItemId-1'
                ),
                new LineItem(
                    'myContextId',
                    50,
                    'myLabel-2',
                    'myLineItemId-2'
                ),
            ]),
            'myRelationLink'
        );
    }

    private function getQueryParameters(): array
    {
        return [
            'page' => 2,
            'limit' => 1,
            'resource_link_id' => 'myResourceLinkId',
            'tag' => 'myTag',
            'resource_id' => 'myResourceId',
        ];
    }
}
