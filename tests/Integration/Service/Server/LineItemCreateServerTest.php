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

namespace OAT\Library\Lti1p3Ags\Tests\Integration\Service\LineItem;

use Exception;
use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Utils;
use OAT\Library\Lti1p3Ags\Service\LineItem\LineItemCreateServiceInterface;
use OAT\Library\Lti1p3Ags\Service\Server\LineItem\LineItemCreateServer;
use OAT\Library\Lti1p3Core\Service\Server\Validator\AccessTokenRequestValidationResult;
use OAT\Library\Lti1p3Core\Service\Server\Validator\AccessTokenRequestValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Throwable;

class LineItemCreateServerTest extends TestCase
{
    /** @var LineItemCreateServer */
    private $subject;

    /** @var LineItemCreateServiceInterface|MockObject */
    private $service;

    /** @var AccessTokenRequestValidator|MockObject */
    private $validator;

    public function setUp(): void
    {
        $this->service = $this->createMock(LineItemCreateServiceInterface::class);
        $this->validator = $this->createMock(AccessTokenRequestValidator::class);
        $this->subject = new LineItemCreateServer(
            $this->validator,
            $this->service
        );
    }

    /**
     * @dataProvider handleProvider
     */
    public function testHandle(
        int $expectedStatusCode,
        $expectedResponseBody,
        string $requestMethod,
        string $url,
        array $requestBody,
        Throwable $unhandledException = null
    ): void
    {
        $validationResult = $this->createMock(AccessTokenRequestValidationResult::class);

        $validationResult
            ->method('hasError')
            ->willReturn(false);

        $validationResult
            ->method('getScopes')
            ->willReturn([LineItemCreateServer::ALLOWED_SCOPE]);

        $this->validator
            ->method('validate')
            ->willReturn($validationResult);

        $this->service
            ->method('create')
            ->willReturnCallback(
                function () use ($unhandledException): void {
                    if ($unhandledException) {
                        throw $unhandledException;
                    }
                }
            );

        $request = new ServerRequest(
            $requestMethod,
            $url,
            [],
            Utils::streamFor(json_encode($requestBody))
        );

        $response = $this->subject->handle($request);

        $this->assertSame(
            is_string($expectedResponseBody) ? $expectedResponseBody : json_encode($expectedResponseBody),
            (string)$response->getBody()
        );
        $this->assertEquals($expectedStatusCode, $response->getStatusCode());
    }

    public function handleProvider(): array
    {
        $urlWithContext = sprintf('/%s/lineitems', 'myContextId');

        return [
            'ContextId not Provided' => [
                'expectedStatusCode' => 400,
                'expectedResponseBody' => 'Url path must contain contextId as first uri path part.',
                'requestMethod' => 'POST',
                'url' => '/',
                'requestBody' => $this->createLineItem(),
                'unhandledException' => null
            ],
            'Required fields not provided' => [
                'expectedStatusCode' => 400,
                'expectedResponseBody' => 'All required fields were not provided',
                'requestMethod' => 'POST',
                'url' => $urlWithContext,
                'requestBody' => [],
                'unhandledException' => null
            ],
            'HTTP method not accepted' => [
                'expectedStatusCode' => 405,
                'expectedResponseBody' => 'Expected http method is "post".',
                'requestMethod' => 'GET',
                'url' => $urlWithContext,
                'requestBody' => [],
                'unhandledException' => null
            ],
            'Internal error' => [
                'expectedStatusCode' => 500,
                'expectedResponseBody' => 'Internal AGS service error',
                'requestMethod' => 'POST',
                'contextId' => $urlWithContext,
                'requestBody' => $this->createLineItem(),
                'unhandledException' => new Exception('Not handled exception')
            ],
            'LineItem Created successfully' => [
                'expectedStatusCode' => 201,
                'expectedResponseBody' => $this->createLineItem(),
                'requestMethod' => 'POST',
                'contextId' => $urlWithContext,
                'requestBody' => $this->createLineItem(),
                'unhandledException' => null
            ]
        ];
    }

    private function createLineItem(): array
    {
        return [
            'id' => 'myId',
            'startDateTime' => '2010-10-10T00:00:00+00:00',
            'endDateTime' => '2010-10-10T00:59:59+00:00',
            'scoreMaximum' => 100,
            'label' => 'My Label',
            'tag' => 'My tag',
            'resourceId' => 'myResourceId',
            'resourceLinkId' => 'myResourceLinkId',
        ];
    }
}
