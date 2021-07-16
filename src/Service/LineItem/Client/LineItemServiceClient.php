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

namespace OAT\Library\Lti1p3Ags\Service\LineItem\Client;

use InvalidArgumentException;
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
use OAT\Library\Lti1p3Ags\Voter\ScopePermissionVoter;
use OAT\Library\Lti1p3Core\Exception\LtiException;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use OAT\Library\Lti1p3Core\Message\Payload\Claim\AgsClaim;
use OAT\Library\Lti1p3Core\Message\Payload\LtiMessagePayloadInterface;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use OAT\Library\Lti1p3Core\Service\Client\LtiServiceClient;
use OAT\Library\Lti1p3Core\Service\Client\LtiServiceClientInterface;
use Throwable;

/**
 * @see https://www.imsglobal.org/spec/lti-ags/v2p0#line-item-service-scope-and-allowed-http-methods
 */
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
     * @throws LtiExceptionInterface
     */
    public function createLineItemForPayload(
        RegistrationInterface $registration,
        LineItemInterface $lineItem,
        LtiMessagePayloadInterface $payload
    ): LineItemInterface {
        try {
            $claim = $payload->getAgs();

            if (null === $claim) {
                throw new InvalidArgumentException('Provided payload does not contain AGS claim');
            }

            return $this->createLineItemForClaim($registration, $lineItem, $claim);
        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot create line item for payload: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @throws LtiExceptionInterface
     */
    public function createLineItemForClaim(
        RegistrationInterface $registration,
        LineItemInterface $lineItem,
        AgsClaim $claim
    ): LineItemInterface {
        try {
            $lineItemsContainerUrl = $claim->getLineItemsContainerUrl();

            if (null === $lineItemsContainerUrl) {
                throw new InvalidArgumentException('Provided AGS claim does not contain line items container url');
            }

            if (!ScopePermissionVoter::canWriteLineItem($claim->getScopes())) {
                throw new InvalidArgumentException('Provided AGS claim does not contain line item write scope');
            }

            return $this->createLineItem($registration, $lineItem, $lineItemsContainerUrl);
        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot create line item for claim: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @throws LtiExceptionInterface
     */
    public function createLineItem(
        RegistrationInterface $registration,
        LineItemInterface $lineItem,
        string $lineItemsContainerUrl
    ): LineItemInterface {
        try {
            $response = $this->client->request(
                $registration,
                'POST',
                $lineItemsContainerUrl,
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
     * @throws LtiExceptionInterface
     */
    public function getLineItemForPayload(
        RegistrationInterface $registration,
        LtiMessagePayloadInterface $payload
    ): LineItemInterface {
        try {
            $claim = $payload->getAgs();

            if (null === $claim) {
                throw new InvalidArgumentException('Provided payload does not contain AGS claim');
            }

            return $this->getLineItemForClaim($registration, $claim);
        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot get line item for payload: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @throws LtiExceptionInterface
     */
    public function getLineItemForClaim(
        RegistrationInterface $registration,
        AgsClaim $claim
    ): LineItemInterface {
        try {
            $lineItemUrl = $claim->getLineItemUrl();

            if (null === $lineItemUrl) {
                throw new InvalidArgumentException('Provided AGS claim does not contain line item url');
            }

            if (!ScopePermissionVoter::canReadLineItem($claim->getScopes())) {
                throw new InvalidArgumentException('Provided AGS claim does not contain line item read scope');
            }

            return $this->getLineItem($registration, $lineItemUrl, $this->extractLineItemScopesFromClaim($claim));
        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot get line item for claim: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
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
     * @throws LtiExceptionInterface
     */
    public function listLineItemsForPayload(
        RegistrationInterface $registration,
        LtiMessagePayloadInterface $payload,
        ?string $resourceIdentifier = null,
        ?string $resourceLinkIdentifier = null,
        ?string $tag = null,
        ?int $limit = null,
        ?int $offset = null
    ): LineItemContainerInterface {
        try {
            $claim = $payload->getAgs();

            if (null === $claim) {
                throw new InvalidArgumentException('Provided payload does not contain AGS claim');
            }

            return $this->listLineItemsForClaim(
                $registration,
                $claim,
                $resourceIdentifier,
                $resourceLinkIdentifier,
                $tag,
                $limit,
                $offset
            );
        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot list line items for payload: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @throws LtiExceptionInterface
     */
    public function listLineItemsForClaim(
        RegistrationInterface $registration,
        AgsClaim $claim,
        ?string $resourceIdentifier = null,
        ?string $resourceLinkIdentifier = null,
        ?string $tag = null,
        ?int $limit = null,
        ?int $offset = null
    ): LineItemContainerInterface {
        try {
            $lineItemsContainerUrl = $claim->getLineItemsContainerUrl();

            if (null === $lineItemsContainerUrl) {
                throw new InvalidArgumentException('Provided AGS claim does not contain line items container url');
            }

            if (!ScopePermissionVoter::canReadLineItem($claim->getScopes())) {
                throw new InvalidArgumentException('Provided AGS claim does not contain line item read scope');
            }

            return $this->listLineItems(
                $registration,
                $lineItemsContainerUrl,
                $resourceIdentifier,
                $resourceLinkIdentifier,
                $tag,
                $limit,
                $offset,
                $this->extractLineItemScopesFromClaim($claim)
            );
        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot list line items for claim: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @throws LtiExceptionInterface
     */
    public function listLineItems(
        RegistrationInterface $registration,
        string $lineItemsContainerUrl,
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
                $this->builder->build($lineItemsContainerUrl, null, array_filter($queryParameters)),
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
     * @throws LtiExceptionInterface
     */
    public function updateLineItemForPayload(
        RegistrationInterface $registration,
        LineItemInterface $lineItem,
        LtiMessagePayloadInterface $payload
    ): LineItemInterface {
        try {
            $claim = $payload->getAgs();

            if (null === $claim) {
                throw new InvalidArgumentException('Provided payload does not contain AGS claim');
            }

            return $this->updateLineItemForClaim($registration, $lineItem, $claim);
        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot update line item for payload: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @throws LtiExceptionInterface
     */
    public function updateLineItemForClaim(
        RegistrationInterface $registration,
        LineItemInterface $lineItem,
        AgsClaim $claim
    ): LineItemInterface {
        try {
            $lineItemUrl = $lineItem->getIdentifier() ?? $claim->getLineItemUrl();

            if (null === $lineItemUrl) {
                throw new InvalidArgumentException('Provided AGS claim or line item does not contain line item url');
            }

            if (!ScopePermissionVoter::canWriteLineItem($claim->getScopes())) {
                throw new InvalidArgumentException('Provided AGS claim does not contain line item write scope');
            }

            return $this->updateLineItem($registration, $lineItem, $lineItemUrl);
        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot update line item for claim: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @throws LtiExceptionInterface
     */
    public function updateLineItem(
        RegistrationInterface $registration,
        LineItemInterface $lineItem,
        ?string $lineItemUrl = null
    ): LineItemInterface {
        try {
            $lineItemUrl = $lineItem->getIdentifier() ?? $lineItemUrl;

            if (null === $lineItemUrl) {
                throw new InvalidArgumentException('No provided line item url');
            }

            $response = $this->client->request(
                $registration,
                'PUT',
                $lineItem->getIdentifier(),
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
                sprintf('Cannot update line item: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @throws LtiExceptionInterface
     */
    public function deleteLineItemForPayload(
        RegistrationInterface $registration,
        LtiMessagePayloadInterface $payload
    ): bool {
        try {
            $claim = $payload->getAgs();

            if (null === $claim) {
                throw new InvalidArgumentException('Provided payload does not contain AGS claim');
            }

            return $this->deleteLineItemForClaim($registration, $claim);
        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot delete line item for payload: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @throws LtiExceptionInterface
     */
    public function deleteLineItemForClaim(
        RegistrationInterface $registration,
        AgsClaim $claim
    ): bool {
        try {
            $lineItemUrl = $claim->getLineItemUrl();

            if (null === $lineItemUrl) {
                throw new InvalidArgumentException('Provided AGS claim does not contain line item url');
            }

            if (!ScopePermissionVoter::canWriteLineItem($claim->getScopes())) {
                throw new InvalidArgumentException('Provided AGS claim does not contain line item write scope');
            }

            return $this->deleteLineItem($registration, $lineItemUrl);
        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot delete line item for claim: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @throws LtiExceptionInterface
     */
    public function deleteLineItem(
        RegistrationInterface $registration,
        string $lineItemUrl
    ): bool {
        try {
            $response = $this->client->request(
                $registration,
                'DELETE',
                $lineItemUrl,
                [],
                [
                    static::AUTHORIZATION_SCOPE_LINE_ITEM,
                ]
            );

            return in_array($response->getStatusCode(), [200, 201, 202, 204]);
        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot delete line item: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    private function extractLineItemScopesFromClaim(AgsClaim $claim): ?array
    {
        $intersect = array_intersect(
            [
                self::AUTHORIZATION_SCOPE_LINE_ITEM,
                self::AUTHORIZATION_SCOPE_LINE_ITEM_READ_ONLY,
            ],
            $claim->getScopes()
        );

        return !empty($intersect) ? $intersect : null;
    }
}
