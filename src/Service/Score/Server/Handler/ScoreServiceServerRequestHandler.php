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

use Http\Message\ResponseFactory;
use Nyholm\Psr7\Factory\HttplugFactory;
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
    /** @var ScoreRepositoryInterface */
    private $repository;

    /** @var ScoreSerializerInterface */
    private $serializer;

    /** @var ResponseFactory */
    private $factory;

    /** @var UrlExtractorInterface */
    private $extractor;

    /** @var LoggerInterface */
    protected $logger;

    public function __construct(
        ScoreRepositoryInterface $repository,
        ?ScoreSerializerInterface $serializer = null,
        ?ResponseFactory $factory = null,
        ?UrlExtractorInterface $extractor = null,
        ?LoggerInterface $logger = null
    ) {
        $this->repository = $repository;
        $this->serializer = $serializer ?? new ScoreSerializer();
        $this->factory = $factory ?? new HttplugFactory();
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

        try {
            $score = $this->serializer->deserialize((string)$request->getBody());
        } catch (LtiExceptionInterface $exception) {
            $this->logger->error($exception->getMessage());

            return $this->factory->createResponse(400, null, [], $exception->getMessage());
        }

        $this->repository->save(
            $score->setLineItemIdentifier($lineItemIdentifier)
        );

        return $this->factory->createResponse(204);
    }
}
