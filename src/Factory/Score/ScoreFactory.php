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

namespace OAT\Library\Lti1p3Ags\Factory\Score;

use InvalidArgumentException;
use OAT\Library\Lti1p3Ags\Model\Score\Score;
use OAT\Library\Lti1p3Ags\Model\Score\ScoreInterface;

class ScoreFactory implements ScoreFactoryInterface
{
    /**
     * @throws InvalidArgumentException
     */
    public function create(array $data): ScoreInterface
    {
        $userIdentifier = $data['userId'] ?? null;

        if (null === $userIdentifier) {
            throw new InvalidArgumentException('Missing mandatory user identifier');
        }

        return new Score(
            $userIdentifier,
            $data['activityProgress'] ?? ScoreInterface::ACTIVITY_PROGRESS_STATUS_INITIALIZED,
            $data['gradingProgress'] ?? ScoreInterface::GRADING_PROGRESS_STATUS_NOT_READY,
            null,
            $data['scoreGiven'] ?? null,
            $data['scoreMaximum'] ?? null,
            $data['comment'] ?? null,
            $data['timestamp'] ?? null
        );
    }
}
