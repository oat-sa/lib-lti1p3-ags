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
use OAT\Library\Lti1p3Ags\Service\Score\ScoreServiceInterface;
use OAT\Library\Lti1p3Core\Exception\LtiException;
use OAT\Library\Lti1p3Core\Exception\LtiExceptionInterface;
use OAT\Library\Lti1p3Core\Message\Payload\LtiMessagePayloadInterface;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use OAT\Library\Lti1p3Core\Service\Client\ServiceClient;
use OAT\Library\Lti1p3Core\Service\Client\ServiceClientInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class ScoreServiceClient implements ScoreServiceInterface
{
    /** @var ServiceClientInterface */
    private $client;

    /** @var ScoreNormalizerInterface */
    private $normalizer;

    public function __construct(
        ServiceClientInterface $client = null,
        ScoreNormalizerInterface $normalizer = null
    ) {
        $this->client = $client ?? new ServiceClient();
        $this->normalizer = $normalizer ?? new ScoreNormalizer();
    }

    /**
     * @see https://www.imsglobal.org/spec/lti-ags/v2p0#score-publish-service
     * @throws LtiExceptionInterface
     */
    public function publishForPayload(
        RegistrationInterface $registration,
        LtiMessagePayloadInterface $payload,
        ScoreInterface $score
    ): ResponseInterface {
        try {
            if (null === $payload->getAgs()) {
                throw new InvalidArgumentException('Provided payload does not contain AGS claim');
            }

            if (null === $payload->getAgs()->getLineItemUrl()) {
                throw new InvalidArgumentException('Provided payload AGS claim does not contain a line item url');
            }

            return $this->publish(
                $registration,
                $score,
                $payload->getAgs()->getLineItemUrl(),
                $payload->getAgs()->getScopes()
            );
        } catch (LtiExceptionInterface $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw new LtiException(
                sprintf('Cannot publish score for payload: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * @see https://www.imsglobal.org/spec/lti-ags/v2p0#score-publish-service
     * @throws LtiExceptionInterface
     */
    public function publish(
        RegistrationInterface $registration,
        ScoreInterface $score,
        string $lineItemUrl,
        array $scopes = null
    ): ResponseInterface {
        try {
            if (!empty($scopes) && !in_array(static::AUTHORIZATION_SCOPE_SCORE, $scopes, true)) {
                throw new InvalidArgumentException(
                    sprintf('The mandatory scope %s is missing', self::AUTHORIZATION_SCOPE_SCORE)
                );
            }

            return $this->client->request(
                $registration,
                'POST',
                $this->buildEndpointUrl($lineItemUrl),
                [
                    'headers' => ['Content-Type' => static::CONTENT_TYPE_SCORE],
                    'body' => json_encode($this->normalizer->normalize($score))
                ],
                $scopes ?? [static::AUTHORIZATION_SCOPE_SCORE]
            );
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

    private function buildEndpointUrl(string $lineItemUrl): string
    {
        $parsedUrl = parse_url($lineItemUrl);

        $parsedUrl['path'] = rtrim($parsedUrl['path'], '/');
        $parsedUrl['path'] = rtrim($parsedUrl['path'], '/lineitem');

        $username = $parsedUrl['user'] ?? '';
        $password = isset($parsedUrl['pass']) ? ':' . $parsedUrl['pass']  : '';

        return sprintf(
            '%s%s%s%s%s%s%s',
            isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '',
            $username !== '' ? $username . $password . '@' : '',
            $parsedUrl['host'],
            isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '',
            $parsedUrl['path'],
            '/scores',
            isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : ''
        );
    }
}
