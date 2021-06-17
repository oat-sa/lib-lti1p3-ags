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

namespace OAT\Library\Lti1p3Ags\Service\Result\Client;

use OAT\Library\Lti1p3Ags\Model\Result\ResultContainer;
use OAT\Library\Lti1p3Ags\Model\Result\ResultContainerInterface;
use OAT\Library\Lti1p3Ags\Serializer\Result\ResultCollectionSerializer;
use OAT\Library\Lti1p3Ags\Serializer\Result\ResultCollectionSerializerInterface;
use OAT\Library\Lti1p3Ags\Service\Result\ResultServiceInterface;
use OAT\Library\Lti1p3Ags\Url\Builder\UrlBuilder;
use OAT\Library\Lti1p3Ags\Url\Builder\UrlBuilderInterface;
use OAT\Library\Lti1p3Core\Exception\LtiException;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use OAT\Library\Lti1p3Core\Service\Client\LtiServiceClient;
use OAT\Library\Lti1p3Core\Service\Client\LtiServiceClientInterface;
use Throwable;

class ResultServiceClient implements ResultServiceInterface
{
    /** @var LtiServiceClientInterface */
    private $client;

    /** @var ResultCollectionSerializerInterface */
    private $serializer;

    /** @var UrlBuilderInterface */
    private $builder;

    public function __construct(
        ?LtiServiceClientInterface $client = null,
        ?ResultCollectionSerializerInterface $serializer = null,
        ?UrlBuilderInterface $builder = null
    ) {
        $this->client = $client ?? new LtiServiceClient();
        $this->serializer = $serializer ?? new ResultCollectionSerializer();
        $this->builder = $builder ?? new UrlBuilder();
    }

    /**
     * @see https://www.imsglobal.org/spec/lti-ags/v2p0#result-service
     * @throws LtiExceptionInterface
     */
    public function listResults(
        RegistrationInterface $registration,
        string $lineItemUrl,
        ?string $userIdentifier = null,
        ?int $limit = null,
        ?int $offset = null
    ): ResultContainerInterface {
        try {
            $queryParameters = [
                'user_id' => $userIdentifier,
                'limit' => $limit,
                'offset' => $offset
            ];

            $response = $this->client->request(
                $registration,
                'GET',
                $this->builder->build($lineItemUrl, 'results', array_filter($queryParameters)),
                [
                    'headers' => [
                        'Accept' => static::CONTENT_TYPE_RESULT_CONTAINER,
                    ],
                ],
                [
                    static::AUTHORIZATION_SCOPE_RESULT_READ_ONLY,
                ]
            );

            $resultContainer = new ResultContainer(
                $this->serializer->deserialize($response->getBody()->__toString())
            );

            $relationLink = $response->getHeaderLine(static::HEADER_LINK);
            if (!empty($relationLink)) {
                $resultContainer->setRelationLink($relationLink);
            }

            return $resultContainer;
        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot list results: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }
}
