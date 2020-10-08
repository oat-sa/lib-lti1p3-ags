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

namespace OAT\Library\Lti1p3Ags\Serializer\LineItem\Serializer;

use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemInterface;
use OAT\Library\Lti1p3Ags\Serializer\LineItem\Normalizer\LineItemNormalizer;
use OAT\Library\Lti1p3Ags\Serializer\LineItem\Normalizer\LineItemNormalizerInterface;

class LineItemSerializer implements LineItemSerializerInterface
{
    /** @var LineItemNormalizerInterface */
    private $normalizer;

    public function __construct(LineItemNormalizerInterface $normalizer = null)
    {
        $this->normalizer = $normalizer ?? new LineItemNormalizer();
    }

    public function serialize(LineItemInterface $lineItem): string
    {
        return json_encode(
            $this->normalizer->normalize($lineItem)
        );
    }
}
