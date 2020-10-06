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

use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Utils;
use OAT\Library\Lti1p3Ags\Repository\LineItemRepository;
use OAT\Library\Lti1p3Ags\Service\LineItem\LineItemCreateServiceInterface;
use OAT\Library\Lti1p3Ags\Service\Server\LineItem\LineItemCreateServer;
use OAT\Library\Lti1p3Core\Service\Server\Validator\AccessTokenRequestValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LineItemCreateServerTest extends TestCase
{
    /** @var LineItemCreateServer */
    private $subject;

    /** @var LineItemCreateServiceInterface|MockObject */
    private $service;

    /** @var LineItemRepository */
    private $repository;

    /** @var AccessTokenRequestValidator|MockObject */
    private $validator;

    public function setUp()
    {
        $this->service = $this->createMock(LineItemCreateServiceInterface::class);
        $this->validator = $this->createMock(AccessTokenRequestValidator::class);
        $this->subject = new LineItemCreateServer(
            $this->validator,
            $this->service,
            null,
            null,
            null,
            null,
            null
        );
    }

    /**
     * @dataProvider handleProvider
     */
    public function testHandle(
        int $expectedStatusCode,
        $expectedResponseBody,
        string $requestMethod,
        string $contextId,
        array $requestBody
    ): void
    {
        $this->service
            ->method('create');

        $request = new ServerRequest(
            $requestMethod,
            sprintf('/%s/lineitems', $contextId),
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
        return [
            [
                'expectedStatusCode' => 500,
                'expectedResponseBody' => "Internal membership service error",
                'requestMethod' => 'POST',
                'contextId' => 'myContextId',
                'requestBody' => []
            ],
            'LineItem Created successfully' => [
                'expectedStatusCode' => 201,
                'expectedResponseBody' => $this->createLineItem(),
                'requestMethod' => 'POST',
                'contextId' => 'myContextId',
                'requestBody' => $this->createLineItem()
            ]
        ];
    }

    private function createLineItem(): array
    {
        return [
            'contextId' => 'myContextId',
            'scoreMaximum' => 100,
            'label' => 'My Label',
            'id' => 'myId',
            'startDateTime' => '2010-10-10T00:00:00+00:00',
            'endDateTime' => '2010-10-10T00:59:59+00:00',
            'tag' => 'My tag',
            'resourceId' => 'myResourceId',
            'resourceLinkId' => 'myResourceLinkId',
        ];
    }
}
