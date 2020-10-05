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

namespace OAT\Library\Lti1p3Ags\Service\Server\LineItem;

use Http\Message\ResponseFactory;
use Nyholm\Psr7\Factory\HttplugFactory;
use OAT\Library\Lti1p3Ags\Factory\LineItemFactory;
use OAT\Library\Lti1p3Ags\Factory\LineItemFactoryInterface;
use OAT\Library\Lti1p3Ags\Serializer\Normalizer\Platform\LineItemNormalizer;
use OAT\Library\Lti1p3Ags\Serializer\Normalizer\Platform\LineItemNormalizerInterface;
use OAT\Library\Lti1p3Ags\Service\LineItem\LineItemCreateService;
use OAT\Library\Lti1p3Ags\Validator\RequestValidationException;
use OAT\Library\Lti1p3Ags\Validator\RequestValidator\ContentTypeValidator;
use OAT\Library\Lti1p3Ags\Validator\RequestValidator\HttpValidatorAggregator;
use OAT\Library\Lti1p3Ags\Validator\RequestValidator\MethodValidator;
use OAT\Library\Lti1p3Ags\Validator\RequestValidator\RequestParameterValidator;
use OAT\Library\Lti1p3Ags\Validator\RequestValidatorInterface;
use OAT\Library\Lti1p3Core\Service\Server\Validator\AccessTokenRequestValidator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

class LineItemCreateServer implements RequestHandlerInterface
{
    /** @var LoggerInterface */
    private $logger;

    /** @var AccessTokenRequestValidator */
    private $validator;

    /** @var ResponseFactory */
    private $factory;

    /** @var LineItemCreateService  */
    private $lineItemService;

    /** @var LineItemFactory */
    private $lineItemFactory;

    /** @var */
    private $normalizer;

    public function __construct(
        AccessTokenRequestValidator $accessTokenRequestValidator,
        RequestValidatorInterface $validator,
        ResponseFactory $factory,
        ?LineItemNormalizerInterface $normalizer,
        ?LineItemFactoryInterface $lineItemFactory,
        ?LineItemCreateService $lineItemService,
        ?LoggerInterface $logger
    )
    {
        $this->lineItemService = $lineItemService;
        $this->lineItemFactory = $lineItemFactory ?? new LineItemFactory();
        $this->logger = $logger ?? new NullLogger();
        $this->factory = $factory ?? new HttplugFactory();
        $this->setValidator($accessTokenRequestValidator);
        $this->normalizer = $normalizer ?? new LineItemNormalizer();
    }

    /**
     * @todo TokenValidation and ErrorHandling will be duplicated accross all handlers
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $this->validator->validate($request);

            $data = $request->getParsedBody();
            $data = json_decode($data, true);

            $lineItem = $this->normalizer->normalize($data);
            $this->lineItemService->create($lineItem);

            $responseBody = 'LineItem successfully created.';
            $responseHeaders = [
                'Content-Type' => 'application/json',
                'Content-Length' => strlen($responseBody),
            ];

            return $this->factory->createResponse(200, null, $responseHeaders, $responseBody);
        } catch (RequestValidationException $exception){
            $this->logger->error($exception->getMessage());

            return $this->factory->createResponse($exception->getCode(), $exception->getMessage());
        } catch (Throwable $exception) {
            $this->logger->error($exception->getMessage());

            return $this->factory->createResponse(500, null, [], 'Internal membership service error');
        }
    }

    private function setValidator(AccessTokenRequestValidator $accessTokenValidator)
    {
        $this->validator = new HttpValidatorAggregator([
            $accessTokenValidator,
            new MethodValidator('post'),
            new ContentTypeValidator('application/json'),
            new RequestParameterValidator('param1', 'param2', 'param3'),
        ]);
    }
}
