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

namespace OAT\Library\Lti1p3Ags\Tests\Integration\Service\LineItem\Server;

use OAT\Library\Lti1p3Ags\Repository\LineItemRepositoryInterface;
use OAT\Library\Lti1p3Ags\Serializer\LineItem\LineItemSerializer;
use OAT\Library\Lti1p3Ags\Serializer\LineItem\LineItemSerializerInterface;
use OAT\Library\Lti1p3Ags\Service\LineItem\LineItemServiceInterface;
use OAT\Library\Lti1p3Ags\Service\LineItem\Server\ListLineItemsServiceServer;
use OAT\Library\Lti1p3Ags\Tests\Traits\AgsDomainTestingTrait;
use OAT\Library\Lti1p3Core\Service\Server\Validator\AccessTokenRequestValidator;
use OAT\Library\Lti1p3Core\Tests\Resource\Logger\TestLogger;
use OAT\Library\Lti1p3Core\Tests\Traits\DomainTestingTrait as CoreDomainTestingTrait;
use OAT\Library\Lti1p3Core\Tests\Traits\NetworkTestingTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class ListLineItemsServiceServerTest extends TestCase
{
    use AgsDomainTestingTrait;
    use CoreDomainTestingTrait;
    use NetworkTestingTrait;

    /** @var AccessTokenRequestValidator|MockObject */
    private $validatorMock;

    /** @var LineItemRepositoryInterface */
    private $respository;

    /** @var LineItemSerializerInterface */
    private $serializer;

    /** @var TestLogger */
    private $logger;

    /** @var ListLineItemsServiceServer */
    private $subject;

    protected function setUp(): void
    {
        $this->validatorMock = new AccessTokenRequestValidator($this->createTestRegistrationRepository());
        $this->respository = $this->createTestLineItemRepository();
        $this->serializer = new LineItemSerializer();
        $this->logger = new TestLogger();

        $this->subject = new ListLineItemsServiceServer(
            $this->validatorMock,
            $this->respository,
            $this->serializer,
            null,
            null,
            $this->logger
        );
    }

    public function testListLineItems(): void
    {
        $accessToken = $this->createTestClientAccessToken(
            $this->createTestRegistration(),
            [
                LineItemServiceInterface::AUTHORIZATION_SCOPE_LINE_ITEM
            ]
        );

        $request = $this->createServerRequest(
            'GET',
            'http://example.com/contexts/contextIdentifier/lineitems',
            [],
            [
                'Authorization' => sprintf('Bearer %s', $accessToken),
                'Accept' => LineItemServiceInterface::CONTENT_TYPE_LINE_ITEM_CONTAINER
            ]
        );

        $response = $this->subject->handle($request);

        var_dump((string)$response->getBody());

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
