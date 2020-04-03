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

namespace OAT\Library\Lti1p3Ags\Tool\Service;

use InvalidArgumentException;
use OAT\Library\Lti1p3Ags\Model\Score;
use OAT\Library\Lti1p3Ags\Serializer\Normalizer\Tool\ScorePublishNormalizer;
use OAT\Library\Lti1p3Core\Message\Claim\AgsClaim;
use OAT\Library\Lti1p3Core\Registration\RegistrationInterface;
use OAT\Library\Lti1p3Core\Service\Client\ServiceClient;
use OAT\Library\Lti1p3Core\Service\Client\ServiceClientInterface;
use Psr\Http\Message\ResponseInterface;

class ScoreServiceClient
{
    public const AUTHORIZATION_SCOPE_SCORE = 'https://purl.imsglobal.org/spec/lti-ags/scope/score';

    /** @var ScorePublishNormalizer */
    private $scorePublishNormalizer;

    /** @var ServiceClientInterface */
    private $serviceClient;

    public function __construct(
        ScorePublishNormalizer $scorePublishNormalizer,
        ServiceClientInterface $serviceClient = null
    ) {
        $this->scorePublishNormalizer = $scorePublishNormalizer;
        $this->serviceClient = $serviceClient ?? new ServiceClient();
    }

    public function publish(RegistrationInterface $registration, AgsClaim $agsClaim, Score $score): ResponseInterface
    {
        if (null === $agsClaim->getLineItemUrl()) {
            throw new InvalidArgumentException('The line item url required to send the score is not defined');
        }

        return $this->serviceClient->request(
            $registration,
            'POST',
            $agsClaim->getLineItemUrl() . '/scores',
            [
                'json' => $this->scorePublishNormalizer->normalize($score)
            ],
            [
                self::AUTHORIZATION_SCOPE_SCORE
            ]
        );
    }
}
