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

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Model\LineItem;

use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemCollection;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemCollectionInterface;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemInterface;
use PHPUnit\Framework\TestCase;

class LineItemCollectionTest extends TestCase
{
    /** @var LineItemCollectionInterface */
    private $subject;

    /** @var LineItemInterface[] */
    private $iterator;

    public function setUp(): void
    {
        $this->iterator = [
            $this->createMock(LineItemInterface::class),
            $this->createMock(LineItemInterface::class)
        ];

        $this->subject = new LineItemCollection(...$this->iterator);
    }

    public function testGetIterator(): void
    {
        $this->assertSame($this->iterator, $this->subject->getIterator()->getArrayCopy());
    }

    public function testCount(): void
    {
        $this->assertSame(2, $this->subject->count());
    }

    public function testJsonSerialize(): void
    {
        $this->assertSame($this->iterator, $this->subject->jsonSerialize());
    }
}
