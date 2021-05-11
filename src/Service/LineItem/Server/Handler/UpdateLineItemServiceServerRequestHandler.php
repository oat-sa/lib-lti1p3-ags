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
use OAT\Library\Lti1p3Ags\Serializer\LineItem\LineItemSerializer;
use OAT\Library\Lti1p3Ags\Serializer\LineItem\LineItemSerializerInterface;
use OAT\Library\Lti1p3Ags\Service\LineItem\LineItemServiceInterface;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use OAT\Library\Lti1p3Core\Security\OAuth2\Validator\Result\RequestAccessTokenValidationResultInterface;
use OAT\Library\Lti1p3Core\Service\Server\Handler\LtiServiceServerRequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @see https://www.imsglobal.org/spec/lti-ags/v2p0#creating-a-new-line-item
 * @see https://www.imsglobal.org/spec/lti-ags/v2p0#updating-a-line-item
 */
class UpdateLineItemServiceServerRequestHandler implements LtiServiceServerRequestHandlerInterface, LineItemServiceInterface
{
    /** @var LineItemRepositoryInterface */
    private $repository;

    /** @var LineItemSerializerInterface */
    private $serializer;

    /** @var RequestUriParameterExtractorInterface */
    private $extractor;

    /** @var ResponseFactory */
    private $factory;

    /** @var LoggerInterface */
    protected $logger;

    public function __construct(
        LineItemRepositoryInterface $repository,
        ?LineItemSerializerInterface $serializer = null,
        ?RequestUriParameterExtractorInterface $extractor = null,
        ?ResponseFactory $factory = null,
        ?LoggerInterface $logger = null
    ) {
        $this->repository = $repository;
        $this->serializer = $serializer ?? new LineItemSerializer();
        $this->extractor = $extractor ?? new RequestUriParameterExtractor();
        $this->factory = $factory ?? new HttplugFactory();
        $this->logger = $logger ?? new NullLogger();
    }

    public function getServiceName(): string
    {
        return static::NAME;
    }

    public function getAllowedContentType(): ?string
    {
        return static::CONTENT_TYPE_LINE_ITEM;
    }

    public function getAllowedMethods(): array
    {
        return [
            'PUT',
        ];
    }

    public function getAllowedScopes(): array
    {
        return [
            static::AUTHORIZATION_SCOPE_LINE_ITEM,
        ];
    }

    public function handleValidatedServiceRequest(
        RequestAccessTokenValidationResultInterface $validationResult,
        ServerRequestInterface $request,
        array $options = []
    ): ResponseInterface {
        $extractedUriParameters = $this->extractor->extract($request);

        $lineItemIdentifier = $options['lineItemIdentifier'] ?? $extractedUriParameters->getLineItemIdentifier();
        $contextIdentifier = $options['contextIdentifier'] ?? $extractedUriParameters->getContextIdentifier();

        if (null === $lineItemIdentifier) {
            $message = 'Missing line item identifier';
            $this->logger->error($message);

            return $this->factory->createResponse(400, null, [], $message);
        }

        $lineItem = $this->repository->find($lineItemIdentifier, $contextIdentifier);

        if (null === $lineItem) {
            $message = sprintf('Cannot find line item with id %s', $lineItemIdentifier);

            if (null !== $contextIdentifier) {
                $message .= sprintf(' and with context id %s', $contextIdentifier);
            }

            $this->logger->error($message);

            return $this->factory->createResponse(404, null, [], $message);
        }

        try {
            $lineItemUpdate = $this->serializer->deserialize((string)$request->getBody());
        } catch (LtiExceptionInterface $exception) {
            $this->logger->error($exception->getMessage());

            return $this->factory->createResponse(400, null, [], $exception->getMessage());
        }

        $lineItem = $this->repository->save(
            $lineItem->copy($lineItemUpdate)
        );

        $responseBody = $this->serializer->serialize($lineItem);
        $responseHeaders = [
            'Content-Type' => static::CONTENT_TYPE_LINE_ITEM,
            'Content-Length' => strlen($responseBody),
        ];

        return $this->factory->createResponse(200, null, $responseHeaders, $responseBody);
    }
}
