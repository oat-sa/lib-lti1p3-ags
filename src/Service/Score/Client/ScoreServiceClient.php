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

namespace OAT\Library\Lti1p3Ags\Service\Score\Client;

use InvalidArgumentException;
use OAT\Library\Lti1p3Ags\Model\Score\ScoreInterface;
use OAT\Library\Lti1p3Ags\Serializer\Score\ScoreSerializer;
use OAT\Library\Lti1p3Ags\Serializer\Score\ScoreSerializerInterface;
use OAT\Library\Lti1p3Ags\Service\Score\ScoreServiceInterface;
use OAT\Library\Lti1p3Ags\Url\Builder\UrlBuilder;
use OAT\Library\Lti1p3Ags\Url\Builder\UrlBuilderInterface;
use OAT\Library\Lti1p3Ags\Voter\ScopePermissionVoter;
use OAT\Library\Lti1p3Core\Exception\LtiException;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use OAT\Library\Lti1p3Core\Message\Payload\Claim\AgsClaim;
use OAT\Library\Lti1p3Core\Message\Payload\LtiMessagePayloadInterface;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use OAT\Library\Lti1p3Core\Service\Client\LtiServiceClientInterface;
use Throwable;

/**
 * @see https://www.imsglobal.org/spec/lti-ags/v2p0#score-publish-service
 */
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
     * @throws LtiExceptionInterface
     */
    public function publishScoreForPayload(
        RegistrationInterface $registration,
        ScoreInterface $score,
        LtiMessagePayloadInterface $payload
    ): bool {
        try {
            $claim = $payload->getAgs();

            if (null === $claim) {
                throw new InvalidArgumentException('Provided payload does not contain AGS claim');
            }

            return $this->publishScoreForClaim($registration, $score, $claim);
        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot publish score for payload: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @throws LtiExceptionInterface
     */
    public function publishScoreForClaim(
        RegistrationInterface $registration,
        ScoreInterface $score,
        AgsClaim $claim
    ): bool {
        try {
            $lineItemUrl = $claim->getLineItemUrl();

            if (null === $lineItemUrl) {
                throw new InvalidArgumentException('Provided AGS claim does not contain line item url');
            }

            if (!ScopePermissionVoter::canWriteScore($claim->getScopes())) {
                throw new InvalidArgumentException('Provided AGS claim does not contain score scope');
            }

            return $this->publishScore($registration, $score, $lineItemUrl);
        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot publish score for claim: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @throws LtiExceptionInterface
     */
    public function publishScore(
        RegistrationInterface $registration,
        ScoreInterface $score,
        string $lineItemUrl
    ): bool {
        try {
            $response = $this->client->request(
                $registration,
                'POST',
                $this->builder->build($lineItemUrl, 'scores'),
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
        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot publish score: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }
}
