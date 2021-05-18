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

namespace OAT\Library\Lti1p3Ags\Service\LineItem\Client;

use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemContainer;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemContainerInterface;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemInterface;
use OAT\Library\Lti1p3Ags\Serializer\LineItem\LineItemCollectionSerializer;
use OAT\Library\Lti1p3Ags\Serializer\LineItem\LineItemCollectionSerializerInterface;
use OAT\Library\Lti1p3Ags\Serializer\LineItem\LineItemSerializer;
use OAT\Library\Lti1p3Ags\Serializer\LineItem\LineItemSerializerInterface;
use OAT\Library\Lti1p3Ags\Service\LineItem\LineItemServiceInterface;
use OAT\Library\Lti1p3Ags\Url\Builder\UrlBuilder;
use OAT\Library\Lti1p3Ags\Url\Builder\UrlBuilderInterface;
use OAT\Library\Lti1p3Core\Exception\LtiException;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use OAT\Library\Lti1p3Core\Service\Client\LtiServiceClient;
use OAT\Library\Lti1p3Core\Service\Client\LtiServiceClientInterface;
use Throwable;

class LineItemServiceClient implements LineItemServiceInterface
{
    /** @var LtiServiceClientInterface */
    private $client;

    /** @var LineItemSerializerInterface */
    private $serializer;

    /** @var LineItemCollectionSerializerInterface */
    private $collectionSerializer;

    /** @var UrlBuilderInterface */
    private $builder;

    public function __construct(
        ?LtiServiceClientInterface $client = null,
        ?LineItemSerializerInterface $serializer = null,
        ?LineItemCollectionSerializerInterface $collectionSerializer = null,
        ?UrlBuilderInterface $builder = null
    ) {
        $this->client = $client ?? new LtiServiceClient();
        $this->serializer = $serializer ?? new LineItemSerializer();
        $this->collectionSerializer = $collectionSerializer ?? new LineItemCollectionSerializer();
        $this->builder = $builder ?? new UrlBuilder();
    }

    /**
     * @see https://www.imsglobal.org/spec/lti-ags/v2p0#creating-a-new-line-item
     * @throws LtiExceptionInterface
     */
    public function createLineItem(
        RegistrationInterface $registration,
        LineItemInterface $lineItem,
        string $lineItemContainerUrl
    ): LineItemInterface {
        try {
            $response = $this->client->request(
                $registration,
                'POST',
                $lineItemContainerUrl,
                [
                    'headers' => [
                        'Content-Type' => static::CONTENT_TYPE_LINE_ITEM,
                    ],
                    'body' => $this->serializer->serialize($lineItem)
                ],
                [
                    static::AUTHORIZATION_SCOPE_LINE_ITEM,
                ]
            );

            return $this->serializer->deserialize($response->getBody()->__toString());
        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot create line item: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @see https://www.imsglobal.org/spec/lti-ags/v2p0#example-getting-a-single-line-item
     * @throws LtiExceptionInterface
     */
    public function getLineItem(
        RegistrationInterface $registration,
        string $lineItemUrl,
        ?array $scopes = null
    ): LineItemInterface {
        try {
            $response = $this->client->request(
                $registration,
                'GET',
                $lineItemUrl,
                [
                    'headers' => [
                        'Accept' => static::CONTENT_TYPE_LINE_ITEM,
                    ],
                ],
                $scopes ?? [
                    static::AUTHORIZATION_SCOPE_LINE_ITEM,
                    static::AUTHORIZATION_SCOPE_LINE_ITEM_READ_ONLY,
                ]
            );

            return $this->serializer->deserialize($response->getBody()->__toString());
        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot get line item: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @see https://www.imsglobal.org/spec/lti-ags/v2p0#example-getting-all-line-items-for-a-given-container
     * @throws LtiExceptionInterface
     */
    public function listLineItems(
        RegistrationInterface $registration,
        string $lineItemContainerUrl,
        ?string $resourceIdentifier = null,
        ?string $resourceLinkIdentifier = null,
        ?string $tag = null,
        ?int $limit = null,
        ?int $offset = null,
        ?array $scopes = null
    ): LineItemContainerInterface {
        try {
            $queryParameters = [
                'resource_id' => $resourceIdentifier,
                'resource_link_id' => $resourceLinkIdentifier,
                'tag' => $tag,
                'limit' => $limit,
                'offset' => $offset
            ];

            $response = $this->client->request(
                $registration,
                'GET',
                $this->builder->build($lineItemContainerUrl, null, array_filter($queryParameters)),
                [
                    'headers' => [
                        'Accept' => static::CONTENT_TYPE_LINE_ITEM_CONTAINER,
                    ],
                ],
                $scopes ?? [
                    static::AUTHORIZATION_SCOPE_LINE_ITEM,
                    static::AUTHORIZATION_SCOPE_LINE_ITEM_READ_ONLY,
                ]
            );

            $lineItemContainer = new LineItemContainer(
                $this->collectionSerializer->deserialize($response->getBody()->__toString())
            );

            $relationLink = $response->getHeaderLine(static::HEADER_LINK);
            if (!empty($relationLink)) {
                $lineItemContainer->setRelationLink($relationLink);
            }

            return $lineItemContainer;
        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot list line items: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @see https://www.imsglobal.org/spec/lti-ags/v2p0#updating-a-line-item
     * @throws LtiExceptionInterface
     */
    public function updateLineItem(
        RegistrationInterface $registration,
        LineItemInterface $lineItem,
        string $lineItemUrl
    ): LineItemInterface {
        try {
            $response = $this->client->request(
                $registration,
                'PUT',
                $lineItemUrl,
                [
                    'headers' => [
                        'Content-Type' => static::CONTENT_TYPE_LINE_ITEM,
                    ],
                    'body' => $this->serializer->serialize($lineItem)
                ],
                [
                    static::AUTHORIZATION_SCOPE_LINE_ITEM,
                ]
            );

            return $this->serializer->deserialize($response->getBody()->__toString());
        } catch (LtiExceptionInterface $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot update line item: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @see https://www.imsglobal.org/spec/lti-ags/v2p0#line-item-service-scope-and-allowed-http-methods
     * @throws LtiExceptionInterface
     */
    public function deleteLineItem(RegistrationInterface $registration, string $lineItemUrl): void
    {
        try {
            $this->client->request(
                $registration,
                'DELETE',
                $lineItemUrl,
                [],
                [
                    static::AUTHORIZATION_SCOPE_LINE_ITEM,
                ]
            );
        } catch (LtiExceptionInterface $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot delete line item: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }
}
