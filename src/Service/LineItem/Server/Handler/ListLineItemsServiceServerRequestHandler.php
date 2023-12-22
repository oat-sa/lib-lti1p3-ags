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

namespace OAT\Library\Lti1p3Ags\Service\LineItem\Server\Handler;

use Nyholm\Psr7\Response;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemContainerInterface;
use OAT\Library\Lti1p3Ags\Repository\LineItemRepositoryInterface;
use OAT\Library\Lti1p3Ags\Serializer\LineItem\LineItemCollectionSerializer;
use OAT\Library\Lti1p3Ags\Serializer\LineItem\LineItemCollectionSerializerInterface;
use OAT\Library\Lti1p3Ags\Service\LineItem\LineItemServiceInterface;
use OAT\Library\Lti1p3Ags\Url\Builder\UrlBuilder;
use OAT\Library\Lti1p3Ags\Url\Builder\UrlBuilderInterface;
use OAT\Library\Lti1p3Core\Security\OAuth2\Validator\Result\RequestAccessTokenValidationResultInterface;
use OAT\Library\Lti1p3Core\Service\Server\Handler\LtiServiceServerRequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @see https://www.imsglobal.org/spec/lti-ags/v2p0#example-getting-all-line-items-for-a-given-container
 */
class ListLineItemsServiceServerRequestHandler implements LtiServiceServerRequestHandlerInterface, LineItemServiceInterface
{
    /** @var LineItemRepositoryInterface */
    private $repository;

    /** @var LineItemCollectionSerializerInterface */
    private $serializer;

    /** @var UrlBuilderInterface */
    private $builder;

    public function __construct(
        LineItemRepositoryInterface $repository,
        ?LineItemCollectionSerializerInterface $serializer = null,
        ?UrlBuilderInterface $builder = null,
    ) {
        $this->repository = $repository;
        $this->serializer = $serializer ?? new LineItemCollectionSerializer();
        $this->builder = $builder ?? new UrlBuilder();
    }

    public function getServiceName(): string
    {
        return static::NAME;
    }

    public function getAllowedContentType(): ?string
    {
        return static::CONTENT_TYPE_LINE_ITEM_CONTAINER;
    }

    public function getAllowedMethods(): array
    {
        return [
            'GET',
        ];
    }

    public function getAllowedScopes(): array
    {
        return [
            static::AUTHORIZATION_SCOPE_LINE_ITEM,
            static::AUTHORIZATION_SCOPE_LINE_ITEM_READ_ONLY,
        ];
    }

    public function handleValidatedServiceRequest(
        RequestAccessTokenValidationResultInterface $validationResult,
        ServerRequestInterface $request,
        array $options = []
    ): ResponseInterface {
        parse_str($request->getUri()->getQuery(), $parameters);

        $limit = array_key_exists('limit', $parameters) ? intval($parameters['limit']) : null;
        $offset = array_key_exists('offset', $parameters) ? intval($parameters['offset']) : null;

        $lineItemCollection = $this->repository->findCollection(
            $parameters['resource_id'] ?? null,
            $parameters['resource_link_id'] ?? null,
            $parameters['tag'] ?? null,
            $limit,
            $offset
        );

        $responseBody = $this->serializer->serialize($lineItemCollection);
        $responseHeaders = [
            'Content-Type' => static::CONTENT_TYPE_LINE_ITEM_CONTAINER,
            'Content-Length' => strlen($responseBody),
        ];

        if ($lineItemCollection->hasNext()) {
             $linkUrl = $this->builder->build(
                $request->getUri()->__toString(),
                null,
                [
                    'offset' => ($limit ?? 0) + $offset
                ]
            );

            $responseHeaders[static::HEADER_LINK] = sprintf(
                '<%s>; rel="%s"',
                $linkUrl,
                LineItemContainerInterface::REL_NEXT
            );
        }

        return new Response(200, $responseHeaders, $responseBody);
    }
}
