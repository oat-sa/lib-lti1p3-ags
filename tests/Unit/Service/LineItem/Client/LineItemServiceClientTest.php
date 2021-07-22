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

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Service\LineItem\Client;

use Exception;
use OAT\Library\Lti1p3Ags\Service\LineItem\Client\LineItemServiceClient;
use OAT\Library\Lti1p3Ags\Service\LineItem\LineItemServiceInterface;
use OAT\Library\Lti1p3Ags\Tests\Traits\AgsDomainTestingTrait;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use OAT\Library\Lti1p3Core\Message\Payload\Claim\AgsClaim;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use OAT\Library\Lti1p3Core\Service\Client\LtiServiceClientInterface;
use OAT\Library\Lti1p3Core\Tests\Traits\DomainTestingTrait;
use OAT\Library\Lti1p3Core\Tests\Traits\NetworkTestingTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LineItemServiceClientTest extends TestCase
{
    use AgsDomainTestingTrait;
    use DomainTestingTrait;
    use NetworkTestingTrait;

    /** @var LtiServiceClientInterface|MockObject */
    private $clientMock;

    /** @var LineItemServiceClient */
    private $subject;

    protected function setUp(): void
    {
        $this->clientMock = $this->createMock(LtiServiceClientInterface::class);

        $this->subject = new LineItemServiceClient($this->clientMock);
    }

    public function testCreateLineItemForClaimSuccess(): void
    {
        $registration = $this->createTestRegistration();
        $lineItem = $this->createTestLineItem();

        $this->prepareClientMockSuccess(
            $registration,
            'POST',
            'https://example.com/line-items',
            [
                'headers' => [
                    'Content-Type' => LineItemServiceInterface::CONTENT_TYPE_LINE_ITEM,
                ],
                'body' => json_encode($lineItem)
            ],
            [
                LineItemServiceInterface::AUTHORIZATION_SCOPE_LINE_ITEM,
            ],
            json_encode($lineItem)
        );

        $claim = new AgsClaim(
            [
                LineItemServiceInterface::AUTHORIZATION_SCOPE_LINE_ITEM
            ],
            'https://example.com/line-items'
        );

        $result = $this->subject->createLineItemForClaim($registration, $lineItem, $claim);

        $this->assertEquals($lineItem, $result);
    }

    public function testCreateLineItemSuccess(): void
    {
        $registration = $this->createTestRegistration();
        $lineItem = $this->createTestLineItem();

        $this->prepareClientMockSuccess(
            $registration,
            'POST',
            'https://example.com/line-items',
            [
                'headers' => [
                    'Content-Type' => LineItemServiceInterface::CONTENT_TYPE_LINE_ITEM,
                ],
                'body' => json_encode($lineItem)
            ],
            [
                LineItemServiceInterface::AUTHORIZATION_SCOPE_LINE_ITEM,
            ],
            json_encode($lineItem)
        );

        $result = $this->subject->createLineItem($registration, $lineItem, 'https://example.com/line-items');

        $this->assertEquals($lineItem, $result);
    }

    public function testCreateLineItemError(): void
    {
        $error = 'create error';

        $registration = $this->createTestRegistration();
        $lineItem = $this->createTestLineItem();

        $this->prepareClientMockError(
            $registration,
            'POST',
            'https://example.com/line-items',
            [
                'headers' => [
                    'Content-Type' => LineItemServiceInterface::CONTENT_TYPE_LINE_ITEM,
                ],
                'body' => json_encode($lineItem)
            ],
            [
                LineItemServiceInterface::AUTHORIZATION_SCOPE_LINE_ITEM,
            ],
            $error
        );

        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Cannot create line item: create error');

        $this->subject->createLineItem($registration, $lineItem, 'https://example.com/line-items');
    }

    public function testUpdateLineItemSuccess(): void
    {
        $registration = $this->createTestRegistration();
        $lineItem = $this->createTestLineItem();

        $this->prepareClientMockSuccess(
            $registration,
            'PUT',
            $lineItem->getIdentifier(),
            [
                'headers' => [
                    'Content-Type' => LineItemServiceInterface::CONTENT_TYPE_LINE_ITEM,
                ],
                'body' => json_encode($lineItem)
            ],
            [
                LineItemServiceInterface::AUTHORIZATION_SCOPE_LINE_ITEM,
            ],
            json_encode($lineItem)
        );

        $result = $this->subject->updateLineItem($registration, $lineItem);

        $this->assertEquals($lineItem, $result);
    }

    public function testUpdateLineItemErrorOnMissingLineItemIdentifier(): void
    {
        $registration = $this->createTestRegistration();
        $lineItem = $this->createTestLineItem()->setIdentifier(null);

        $this->clientMock
            ->expects($this->never())
            ->method('request');

        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Cannot update line item: No provided line item url');

        $this->subject->updateLineItem($registration, $lineItem);
    }

    public function testUpdateLineItemError(): void
    {
        $error = 'update error';

        $registration = $this->createTestRegistration();
        $lineItem = $this->createTestLineItem();

        $this->prepareClientMockError(
            $registration,
            'PUT',
            $lineItem->getIdentifier(),
            [
                'headers' => [
                    'Content-Type' => LineItemServiceInterface::CONTENT_TYPE_LINE_ITEM,
                ],
                'body' => json_encode($lineItem)
            ],
            [
                LineItemServiceInterface::AUTHORIZATION_SCOPE_LINE_ITEM,
            ],
            $error
        );

        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Cannot update line item: update error');

        $this->subject->updateLineItem($registration, $lineItem);
    }

    public function testGetLineItemSuccess(): void
    {
        $registration = $this->createTestRegistration();
        $lineItem = $this->createTestLineItem();

        $this->prepareClientMockSuccess(
            $registration,
            'GET',
            $lineItem->getIdentifier(),
            [
                'headers' => [
                    'Accept' => LineItemServiceInterface::CONTENT_TYPE_LINE_ITEM,
                ]
            ],
            [
                LineItemServiceInterface::AUTHORIZATION_SCOPE_LINE_ITEM,
                LineItemServiceInterface::AUTHORIZATION_SCOPE_LINE_ITEM_READ_ONLY,
            ],
            json_encode($lineItem)
        );

        $result = $this->subject->getLineItem($registration, $lineItem->getIdentifier());

        $this->assertEquals($lineItem, $result);
    }

    public function testGetLineItemError(): void
    {
        $error = 'get error';

        $registration = $this->createTestRegistration();
        $lineItem = $this->createTestLineItem();

        $this->prepareClientMockError(
            $registration,
            'GET',
            $lineItem->getIdentifier(),
            [
                'headers' => [
                    'Accept' => LineItemServiceInterface::CONTENT_TYPE_LINE_ITEM,
                ]
            ],
            [
                LineItemServiceInterface::AUTHORIZATION_SCOPE_LINE_ITEM,
                LineItemServiceInterface::AUTHORIZATION_SCOPE_LINE_ITEM_READ_ONLY,
            ],
            $error
        );

        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Cannot get line item: get error');

        $this->subject->getLineItem($registration, $lineItem->getIdentifier());
    }

    public function testListLineItemsSuccess(): void
    {
        $registration = $this->createTestRegistration();
        $lineItemCollection = $this->createTestLineItemCollection();

        $this->prepareClientMockSuccess(
            $registration,
            'GET',
            'https://example.com/line-items?resource_id=rid&resource_link_id=rlid&tag=tag&limit=1&offset=1',
            [
                'headers' => [
                    'Accept' => LineItemServiceInterface::CONTENT_TYPE_LINE_ITEM_CONTAINER,
                ]
            ],
            [
                LineItemServiceInterface::AUTHORIZATION_SCOPE_LINE_ITEM,
                LineItemServiceInterface::AUTHORIZATION_SCOPE_LINE_ITEM_READ_ONLY,
            ],
            json_encode($lineItemCollection),
            200,
            [
                LineItemServiceInterface::HEADER_LINK => '<https://example.com/line-items?limit=1&offset=2>; rel="next"'
            ]
        );

        $result = $this->subject->listLineItems(
            $registration,
            'https://example.com/line-items',
            'rid',
            'rlid',
            'tag',
            1,
            1
        );

        $this->assertEquals($lineItemCollection, $result->getLineItems());
        $this->assertTrue($result->hasNext());
        $this->assertEquals(
            'https://example.com/line-items?limit=1&offset=2',
            $result->getRelationLinkUrl()
        );
    }

    public function testListLineItemsError(): void
    {
        $error = 'list error';

        $registration = $this->createTestRegistration();

        $this->prepareClientMockError(
            $registration,
            'GET',
            'https://example.com/line-items?resource_id=rid&resource_link_id=rlid&tag=tag&limit=1&offset=1',
            [
                'headers' => [
                    'Accept' => LineItemServiceInterface::CONTENT_TYPE_LINE_ITEM_CONTAINER,
                ]
            ],
            [
                LineItemServiceInterface::AUTHORIZATION_SCOPE_LINE_ITEM,
                LineItemServiceInterface::AUTHORIZATION_SCOPE_LINE_ITEM_READ_ONLY,
            ],
            $error
        );

        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Cannot list line items: list error');

        $this->subject->listLineItems(
            $registration,
            'https://example.com/line-items',
            'rid',
            'rlid',
            'tag',
            1,
            1
        );
    }

    public function testDeleteLineItemSuccess(): void
    {
        $registration = $this->createTestRegistration();
        $lineItem = $this->createTestLineItem();

        $this->prepareClientMockSuccess(
            $registration,
            'DELETE',
            $lineItem->getIdentifier(),
            [],
            [
                LineItemServiceInterface::AUTHORIZATION_SCOPE_LINE_ITEM,
            ],
            '',
            204
        );

        $result = $this->subject->deleteLineItem($registration, $lineItem->getIdentifier());

        $this->assertTrue($result);
    }

    public function testDeleteLineItemError(): void
    {
        $error = 'delete error';

        $registration = $this->createTestRegistration();
        $lineItem = $this->createTestLineItem();

        $this->prepareClientMockError(
            $registration,
            'DELETE',
            $lineItem->getIdentifier(),
            [],
            [
                LineItemServiceInterface::AUTHORIZATION_SCOPE_LINE_ITEM,
            ],
            $error
        );

        $this->expectException(LtiExceptionInterface::class);
        $this->expectExceptionMessage('Cannot delete line item: delete error');

        $this->subject->deleteLineItem($registration, $lineItem->getIdentifier());
    }

    private function prepareClientMockSuccess(
        RegistrationInterface $registration,
        string $expectedRequestMethod,
        string $expectedRequestUrl,
        array $expectedRequestOptions = [],
        array $expectedRequestScopes = [],
        string $expectedResponseBody = '',
        int $expectedResponseStatusCode = 200,
        array $expectedResponseHeaders = []
    ): void {
        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with(
                $registration,
                $expectedRequestMethod,
                $expectedRequestUrl,
                $expectedRequestOptions,
                $expectedRequestScopes
            )
            ->willReturn(
                $this->createResponse($expectedResponseBody, $expectedResponseStatusCode, $expectedResponseHeaders)
            );
    }

    private function prepareClientMockError(
        RegistrationInterface $registration,
        string $expectedRequestMethod,
        string $expectedRequestUrl,
        array $expectedRequestOptions = [],
        array $expectedRequestScopes = [],
        string $expectedExceptionMessage = ''
    ): void {
        $this->clientMock
            ->expects($this->once())
            ->method('request')
            ->with(
                $registration,
                $expectedRequestMethod,
                $expectedRequestUrl,
                $expectedRequestOptions,
                $expectedRequestScopes
            )
            ->willThrowException(new Exception($expectedExceptionMessage));
    }
}
