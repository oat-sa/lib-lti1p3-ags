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

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Serializer\Normalizer\Platform;

use OAT\Library\Lti1p3Ags\Serializer\Normalizer\Platform\LineItemQueryDenormalizer;
use PHPUnit\Framework\TestCase;

class LineItemQueryDenormalizerTest extends TestCase
{
    private $subject;

    public function setUp(): void
    {
        $this->subject = new LineItemQueryDenormalizer();
    }

    public function testDenormalizeWithAllValues(): void
    {
        $contextId = 'line-item-context-id';
        $lineItemId = 'line-item-id';
        $page = 3;
        $limit = 150;

        $data = [
            'contextId' => $contextId,
            'lineItemId' => $lineItemId,
            'page' => $page,
            'limit' => $limit,
        ];

        $lineItemQuery = $this->subject->denormalize($data);

        $this->assertSame($contextId, $lineItemQuery->getContextId());
        $this->assertSame($lineItemId, $lineItemQuery->getLineItemId());
        $this->assertTrue($lineItemQuery->hasLineItemId());
        $this->assertSame($page, $lineItemQuery->getPage());
        $this->assertSame($limit, $lineItemQuery->getLimit());
    }

    public function testDenormalizeWithRequiredValuesOnly(): void
    {
        $contextId = 'line-item-context-id';

        $data = [
            'contextId' => $contextId,
        ];

        $lineItemQuery = $this->subject->denormalize($data);

        $this->assertSame($contextId, $lineItemQuery->getContextId());
        $this->assertEmpty($lineItemQuery->getLineItemId());
        $this->assertFalse($lineItemQuery->hasLineItemId());
        $this->assertEmpty($lineItemQuery->getPage());
        $this->assertEmpty($lineItemQuery->getLimit());
    }
}
