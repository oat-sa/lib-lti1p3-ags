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

namespace OAT\Library\Lti1p3Ags\Serializer\Normalizer\Platform;

use OAT\Library\Lti1p3Ags\Factory\ScoreFactory;
use OAT\Library\Lti1p3Ags\Factory\ScoreFactoryInterface;
use OAT\Library\Lti1p3Ags\Model\Score;
use OAT\Library\Lti1p3Ags\Validator\ScoreValidator;
use OAT\Library\Lti1p3Ags\Validator\ScoreValidatorInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequestScoreNormalizer implements RequestScoreNormalizerInterface
{
    /** @var ScoreValidatorInterface */
    private $validator;

    /** @var ScoreValidatorInterface */
    private $scoreFactory;

    public function __construct(?ScoreValidatorInterface $validator, ?ScoreFactoryInterface $scoreFactory)
    {
        $this->validator = $validator ?? new ScoreValidator();
        $this->scoreFactory = $scoreFactory ?? new ScoreFactory();
    }

    /**
     * @throws \OAT\Library\Lti1p3Ags\Validator\ValidationException
     */
    public function normalize(ServerRequestInterface $request): Score
    {
        $requestData = $request->getParsedBody();
        $this->validator->validate($requestData);

    }
}
