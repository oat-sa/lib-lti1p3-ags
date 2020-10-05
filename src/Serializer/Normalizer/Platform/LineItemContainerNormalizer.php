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

use OAT\Library\Lti1p3Ags\Model\LineItemContainer;

class LineItemContainerNormalizer implements LineItemContainerNormalizerInterface
{
    /** @var LineItemNormalizer */
    private $lineItemNormalizer;

    public function __construct(LineItemNormalizer $lineItemNormalizer)
    {
        $this->lineItemNormalizer = $lineItemNormalizer;
    }

    public function normalize(LineItemContainer $lineItemContainer): array
    {
        $normalizedData = [];

        foreach ($lineItemContainer->getLineItems() as $lineItem) {
            $normalizedData[] = $this->lineItemNormalizer->normalize($lineItem);
        }

        return $normalizedData;
    }
}
