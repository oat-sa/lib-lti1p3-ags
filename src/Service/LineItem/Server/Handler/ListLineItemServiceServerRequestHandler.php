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

use Http\Message\ResponseFactory;
use Nyholm\Psr7\Factory\HttplugFactory;
use OAT\Library\Lti1p3Ags\Extractor\RequestUriParameterExtractor;
use OAT\Library\Lti1p3Ags\Extractor\RequestUriParameterExtractorInterface;
use OAT\Library\Lti1p3Ags\Repository\LineItemRepositoryInterface;
use OAT\Library\Lti1p3Ags\Serializer\LineItem\LineItemCollectionSerializer;
use OAT\Library\Lti1p3Ags\Serializer\LineItem\LineItemCollectionSerializerInterface;
use OAT\Library\Lti1p3Ags\Service\LineItem\LineItemServiceInterface;
use OAT\Library\Lti1p3Core\Security\OAuth2\Validator\Result\RequestAccessTokenValidationResultInterface;
use OAT\Library\Lti1p3Core\Service\Server\Handler\LtiServiceServerRequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @see https://www.imsglobal.org/spec/lti-ags/v2p0#example-getting-all-line-items-for-a-given-container
 */
class ListLineItemServiceServerRequestHandler implements LtiServiceServerRequestHandlerInterface, LineItemServiceInterface
{
    /** @var LineItemRepositoryInterface */
    private $repository;

    /** @var LineItemCollectionSerializerInterface */
    private $serializer;

    /** @var RequestUriParameterExtractorInterface */
    private $extractor;

    /** @var ResponseFactory */
    private $factory;

    public function __construct(
        LineItemRepositoryInterface $repository,
        ?LineItemCollectionSerializerInterface $serializer = null,
        ?RequestUriParameterExtractorInterface $extractor = null,
        ?ResponseFactory $factory = null
    ) {
        $this->repository = $repository;
        $this->serializer = $serializer ?? new LineItemCollectionSerializer();
        $this->extractor = $extractor ?? new RequestUriParameterExtractor();
        $this->factory = $factory ?? new HttplugFactory();
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
        $extractedUriParameters = $this->extractor->extract($request);

        $contextIdentifier = $options['contextIdentifier'] ?? $extractedUriParameters->getContextIdentifier();

        parse_str($request->getUri()->getQuery(), $parameters);

        $lineItemCollection = $this->repository->findBy(
            $contextIdentifier,
            $parameters['resource_link_id'] ?? null,
            $parameters['resource_id'] ?? null,
            $parameters['tag'] ?? null,
            array_key_exists('limit', $parameters) ? intval($parameters['limit']) : null,
            array_key_exists('offset', $parameters) ? intval($parameters['offset']) : null
        );

        $responseBody = $this->serializer->serialize($lineItemCollection);
        $responseHeaders = [
            'Content-Type' => static::CONTENT_TYPE_LINE_ITEM_CONTAINER,
            'Content-Length' => strlen($responseBody),
        ];

        if ($lineItemCollection->hasNext()) {
            $responseHeaders['Link'] = 'todo';
        }

        return $this->factory->createResponse(200, null, $responseHeaders, $responseBody);
    }
}
