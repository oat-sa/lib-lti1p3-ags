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

use OAT\Library\Lti1p3Ags\Model\LineItem\LineItem;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemContainer;
use OAT\Library\Lti1p3Ags\Serializer\Normalizer\Platform\LineItemContainerNormalizer;
use OAT\Library\Lti1p3Ags\Serializer\Normalizer\Platform\LineItemNormalizerInterface;
use PHPUnit\Framework\TestCase;

class LineItemContainerNormalizerTest extends TestCase
{
    /** @var LineItemContainerNormalizer  */
    private $subject;

    /** @var LineItemNormalizerInterface */
    private $lineItemNormalizer;

    public function setUp(): void
    {
        $this->lineItemNormalizer = $this->createMock(LineItemNormalizerInterface::class);
        $this->subject = new LineItemContainerNormalizer($this->lineItemNormalizer);
    }

    public function testNormalize(): void
    {
        $iterator = [
            $this->createMock(LineItem::class),
            $this->createMock(LineItem::class)
        ];

        $expected = [
            ['id' => 2],
            ['id' => 4],
        ];

        $lineItemContainer = new LineItemContainer(...$iterator);

        $this->lineItemNormalizer
            ->expects($this->exactly(2))
            ->method('normalize')
            ->willReturnOnConsecutiveCalls(...$expected);

        $this->assertSame(
            $expected,
            $this->subject->normalize($lineItemContainer)
        );
    }

    public function testNormalizeWithEmptyLineItemContainer(): void
    {
        $iterator = [];

        $expected = [];

        $lineItemContainer = new LineItemContainer(...$iterator);

        $this->lineItemNormalizer
            ->expects($this->never())
            ->method('normalize');

        $this->assertSame(
            $expected,
            $this->subject->normalize($lineItemContainer)
        );
    }
}
