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

namespace OAT\Library\Lti1p3Ags\Factory\LineItem;

use Carbon\Carbon;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItem;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemInterface;

class LineItemFactory implements LineItemFactoryInterface
{
    public function create(array $data): LineItemInterface
    {
        $additionalProperties = array_diff_key(
            $data,
            array_flip(
                [
                    'id',
                    'scoreMaximum',
                    'label',
                    'resourceId',
                    'resourceLinkId',
                    'tag',
                    'startDateTime',
                    'endDateTime'
                ]
            )
        );

        return new LineItem(
            (float)$data['scoreMaximum'],
            (string)$data['label'],
            $data['id'] ?? null,
            $data['contextId'] ?? null,
            $data['resourceId'] ?? null,
            $data['resourceLinkId'] ?? null,
            $data['tag'] ?? null,
            $data['startDateTime'] ? new Carbon($data['startDateTime']) : null,
            $data['endDateTime'] ? new Carbon($data['endDateTime']) : null,
            $additionalProperties
        );
    }
}
