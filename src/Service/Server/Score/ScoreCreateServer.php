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

namespace OAT\Library\Lti1p3Ags\Service\Server\Score;

use Http\Message\ResponseFactory;
use Nyholm\Psr7\Factory\HttplugFactory;
use OAT\Library\Lti1p3Ags\Factory\ScoreFactory;
use OAT\Library\Lti1p3Ags\Factory\ScoreFactoryInterface;
use OAT\Library\Lti1p3Ags\Repository\ScoreRepository;
use OAT\Library\Lti1p3Ags\Serializer\Normalizer\Platform\RequestScoreNormalizer;
use OAT\Library\Lti1p3Ags\Serializer\Normalizer\Platform\RequestScoreNormalizerInterface;
use OAT\Library\Lti1p3Ags\Service\Server\LineItem\LineItemCreateService;
use OAT\Library\Lti1p3Core\Service\Server\Validator\AccessTokenRequestValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

class ScoreCreateServer implements RequestHandlerInterface
{
    /** @var LoggerInterface */
    private $logger;

    /** @var ResponseFactory */
    private $factory;

    /** @var RequestScoreNormalizerInterface */
    private $normalizer;
    /** @var AccessTokenRequestValidator */
    private $validator;
    /** @var ScoreRepository */
    private $repository;

    public function __construct(
        AccessTokenRequestValidator $validator,
        ScoreRepository $repository,
        ?LoggerInterface $logger,
        ?RequestScoreNormalizerInterface $normalizer,
        ?ResponseFactory $factory
    )
    {
        $this->logger = $logger ?? new NullLogger();
        $this->factory = $factory ?? new HttplugFactory();
        $this->normalizer = $normalizer ?? new RequestScoreNormalizer();
        $this->validator = $validator;
        $this->repository = $repository;
    }


    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $responseHeaders = [];
        $responseBody = '';

        $validationResult = $this->validator->validate($request);

        if ($validationResult->hasError()) {
            $this->logger->error($validationResult->getError());

            return $this->factory->createResponse(401, null, [], $validationResult->getError());
        }

        try {
            $score = $this->normalizer->normalize($request);
            $this->repository->createFromScore($score);
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());
            $this->factory->createResponse(404, null, [], 'Access Token not valid');

        }

        return $this->factory->createResponse(200, null, $responseHeaders, $responseBody);
    }
}
