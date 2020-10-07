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

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Serializer\LineItem\Normalizer;

use Carbon\Carbon;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItem;
use OAT\Library\Lti1p3Ags\Serializer\LineItem\Normalizer\LineItemNormalizer;
use OAT\Library\Lti1p3Ags\Serializer\LineItem\Normalizer\LineItemSerializerInterface;
use OAT\Library\Lti1p3Ags\Traits\DateConverterTrait;
use PHPUnit\Framework\TestCase;

class LineItemNormalizerTest extends TestCase
{
    use DateConverterTrait;

    /** @var LineItemSerializerInterface  */
    private $subject;

    public function setUp(): void
    {
        $this->subject = new LineItemNormalizer();
    }

    public function testNormalizeWithAllValues(): void
    {
        $contextId = 'line-item-context-id';
        $scoreMaximum = 0.5;
        $label = 'line-item-label';
        $id = 'line-item-id';
        $startDateTime = Carbon::create(1988, 12, 22);
        $endDateTime = Carbon::create(2020, 03, 31);
        $tag = 'line-item-tag';
        $resourceId = 'line-item-resource-id';
        $resourceLinkId = 'line-item-resource-link-id';

        $lineItem = new LineItem(
            $contextId,
            $scoreMaximum,
            $label,
            $id,
            $startDateTime,
            $endDateTime,
            $tag,
            $resourceId,
            $resourceLinkId
        );

        $values = [
            'id' => $id,
            'startDateTime' => $this->dateToIso8601($startDateTime),
            'endDateTime' => $this->dateToIso8601($endDateTime),
            'scoreMaximum' => $scoreMaximum,
            'label' => $label,
            'tag' => $tag,
            'resourceId' => $resourceId,
            'resourceLinkId' => $resourceLinkId,
        ];

        $this->assertSame(
            $values,
            $this->subject->normalize($lineItem)
        );
    }

    public function testNormalizeWithRequiredValuesOnly(): void
    {
        $contextId = 'line-item-context-id';
        $scoreMaximum = 0.5;
        $label = 'line-item-label';

        $lineItem = new LineItem($contextId, $scoreMaximum, $label);

        $values = [
            'id' => '',
            'startDateTime' => null,
            'endDateTime' => null,
            'scoreMaximum' => $scoreMaximum,
            'label' => $label,
            'tag' => '',
            'resourceId' => '',
            'resourceLinkId' => '',
        ];

        $this->assertSame(
            $values,
            $this->subject->normalize($lineItem)
        );
    }
}
