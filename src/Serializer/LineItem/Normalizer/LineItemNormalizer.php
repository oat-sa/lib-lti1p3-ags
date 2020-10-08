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

namespace OAT\Library\Lti1p3Ags\Serializer\LineItem\Normalizer;

use DateTimeInterface;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemInterface;

class LineItemNormalizer implements LineItemNormalizerInterface
{
    public function normalize(LineItemInterface $lineItem): array
    {
        $startDateTime = $lineItem->getStartDateTime()
            ? $lineItem->getStartDateTime()->format(DateTimeInterface::ATOM)
            : null;

        $endDateTime = $lineItem->getEndDateTime()
            ? $lineItem->getEndDateTime()->format(DateTimeInterface::ATOM)
            : null;

        return [
            'id' => $lineItem->getId() ?? '',
            'startDateTime' => $startDateTime,
            'endDateTime' => $endDateTime,
            'scoreMaximum' => $lineItem->getScoreMaximum(),
            'label' => $lineItem->getLabel(),
            'tag' => $lineItem->getTag() ?? '',
            'resourceId' => $lineItem->getResourceId() ?? '',
            'resourceLinkId' => $lineItem->getResourceLinkId() ?? ''
        ];
    }
}
