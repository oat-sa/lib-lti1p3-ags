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

namespace OAT\Library\Lti1p3Ags\Service\Score\Client;

use InvalidArgumentException;
use OAT\Library\Lti1p3Ags\Model\Score\ScoreInterface;
use OAT\Library\Lti1p3Ags\Serializer\Score\Normalizer\ScoreNormalizer;
use OAT\Library\Lti1p3Ags\Serializer\Score\Normalizer\ScoreNormalizerInterface;
use OAT\Library\Lti1p3Ags\Serializer\Score\ScoreSerializer;
use OAT\Library\Lti1p3Ags\Serializer\Score\ScoreSerializerInterface;
use OAT\Library\Lti1p3Ags\Service\Score\ScoreServiceInterface;
use OAT\Library\Lti1p3Ags\Url\Builder\UrlBuilder;
use OAT\Library\Lti1p3Ags\Url\Builder\UrlBuilderInterface;
use OAT\Library\Lti1p3Core\Exception\LtiException;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use OAT\Library\Lti1p3Core\Message\Payload\LtiMessagePayloadInterface;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use OAT\Library\Lti1p3Core\Service\Client\LtiServiceClientInterface;
use OAT\Library\Lti1p3Core\Service\Client\ServiceClient;
use OAT\Library\Lti1p3Core\Service\Client\ServiceClientInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class ScoreServiceClient implements ScoreServiceInterface
{
    /** @var LtiServiceClientInterface */
    private $client;

    /** @var ScoreSerializerInterface */
    private $serializer;

    /** @var UrlBuilderInterface */
    private $builder;

    public function __construct(
        LtiServiceClientInterface $client,
        ?ScoreSerializerInterface $serializer = null,
        ?UrlBuilderInterface $builder = null
    ) {
        $this->client = $client;
        $this->serializer = $serializer ?? new ScoreSerializer();
        $this->builder = $builder ?? new UrlBuilder();
    }

    /**
     * @see https://www.imsglobal.org/spec/lti-ags/v2p0#score-publish-service
     * @throws LtiExceptionInterface
     */
    public function publish(
        RegistrationInterface $registration,
        ScoreInterface $score,
        string $lineItemUrl
    ): bool {
        try {
            $scoreUrl = $this->builder->build($lineItemUrl, 'scores');

            $response =  $this->client->request(
                $registration,
                'POST',
                $scoreUrl,
                [
                    'headers' => [
                        'Content-Type' => static::CONTENT_TYPE_SCORE,
                    ],
                    'body' => $this->serializer->serialize($score),
                ],
                [
                    static::AUTHORIZATION_SCOPE_SCORE,
                ]
            );

            return in_array($response->getStatusCode(), [200, 201, 202, 204]);
        } catch (LtiExceptionInterface $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot publish score: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }
}
