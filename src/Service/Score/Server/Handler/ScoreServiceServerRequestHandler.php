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

namespace OAT\Library\Lti1p3Ags\Service\Score\Server\Handler;

use Nyholm\Psr7\Response;
use OAT\Library\Lti1p3Ags\Repository\LineItemRepositoryInterface;
use OAT\Library\Lti1p3Ags\Repository\ScoreRepositoryInterface;
use OAT\Library\Lti1p3Ags\Serializer\Score\ScoreSerializer;
use OAT\Library\Lti1p3Ags\Serializer\Score\ScoreSerializerInterface;
use OAT\Library\Lti1p3Ags\Service\Score\ScoreServiceInterface;
use OAT\Library\Lti1p3Ags\Url\Extractor\UrlExtractor;
use OAT\Library\Lti1p3Ags\Url\Extractor\UrlExtractorInterface;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use OAT\Library\Lti1p3Core\Security\OAuth2\Validator\Result\RequestAccessTokenValidationResultInterface;
use OAT\Library\Lti1p3Core\Service\Server\Handler\LtiServiceServerRequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @see https://www.imsglobal.org/spec/lti-ags/v2p0#score-publish-service
 */
class ScoreServiceServerRequestHandler implements LtiServiceServerRequestHandlerInterface, ScoreServiceInterface
{
    /** @var LineItemRepositoryInterface */
    private $lineItemRepository;

    /** @var ScoreRepositoryInterface */
    private $scoreRepository;

    /** @var ScoreSerializerInterface */
    private $serializer;

    /** @var UrlExtractorInterface */
    private $extractor;

    /** @var LoggerInterface */
    protected $logger;

    public function __construct(
        LineItemRepositoryInterface $lineItemRepository,
        ScoreRepositoryInterface $scoreRepository,
        ?ScoreSerializerInterface $serializer = null,
        ?UrlExtractorInterface $extractor = null,
        ?LoggerInterface $logger = null
    ) {
        $this->lineItemRepository = $lineItemRepository;
        $this->scoreRepository = $scoreRepository;
        $this->serializer = $serializer ?? new ScoreSerializer();
        $this->extractor = $extractor ?? new UrlExtractor();
        $this->logger = $logger ?? new NullLogger();
    }

    public function getServiceName(): string
    {
        return static::NAME;
    }

    public function getAllowedContentType(): ?string
    {
        return static::CONTENT_TYPE_SCORE;
    }

    public function getAllowedMethods(): array
    {
        return [
            'POST',
        ];
    }

    public function getAllowedScopes(): array
    {
        return [
            static::AUTHORIZATION_SCOPE_SCORE,
        ];
    }

    public function handleValidatedServiceRequest(
        RequestAccessTokenValidationResultInterface $validationResult,
        ServerRequestInterface $request,
        array $options = []
    ): ResponseInterface {
        $lineItemIdentifier = $this->extractor->extract($request->getUri()->__toString(), 'scores');

        $lineItem = $this->lineItemRepository->find($lineItemIdentifier);

        if (null === $lineItem) {
            $message = sprintf('Cannot find line item with id %s', $lineItemIdentifier);

            $this->logger->error($message);

            return new Response(404, [], $message);
        }

        try {
            $score = $this->serializer->deserialize((string)$request->getBody());
        } catch (LtiExceptionInterface $exception) {
            $this->logger->error($exception->getMessage());

            return new Response(400, [], $exception->getMessage());
        }

        $this->scoreRepository->save(
            $score->setLineItemIdentifier($lineItem->getIdentifier())
        );

        return new Response(201);
    }
}
