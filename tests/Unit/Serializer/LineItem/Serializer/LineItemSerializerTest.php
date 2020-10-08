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

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Serializer\LineItem\Serializer;

use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemInterface;
use OAT\Library\Lti1p3Ags\Serializer\LineItem\Normalizer\LineItemNormalizerInterface;
use OAT\Library\Lti1p3Ags\Serializer\LineItem\Serializer\LineItemSerializer;
use OAT\Library\Lti1p3Ags\Serializer\LineItem\Serializer\LineItemSerializerInterface;
use PHPUnit\Framework\TestCase;

class LineItemSerializerTest extends TestCase
{
    /** @var LineItemSerializerInterface  */
    private $subject;

    /** @var LineItemNormalizerInterface */
    private $lineItemNormalizer;

    public function setUp(): void
    {
        $this->lineItemNormalizer = $this->createMock(LineItemNormalizerInterface::class);
        $this->subject = new LineItemSerializer($this->lineItemNormalizer);
    }

    public function testSerialize(): void
    {
        $expected = ['toto'];

        $lineItem = $this->createMock(LineItemInterface::class);

        $this->lineItemNormalizer
            ->expects($this->once())
            ->method('normalize')
            ->with($lineItem)
            ->willReturn($expected);

        $this->assertSame(
            json_encode($expected),
            $this->subject->serialize($lineItem)
        );
    }
}
