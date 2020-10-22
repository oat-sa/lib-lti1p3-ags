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

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Model\LineItemContainer;

use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemCollection;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemCollectionInterface;
use OAT\Library\Lti1p3Ags\Model\LineItemContainer\LineItemContainer;
use OAT\Library\Lti1p3Ags\Model\LineItemContainer\LineItemContainerInterface;
use PHPUnit\Framework\TestCase;

class LineItemContainerTest extends TestCase
{
    /** @var LineItemContainerInterface */
    private $subject;

    /** @var LineItemCollectionInterface */
    private $lineItemCollection;

    public function setUp(): void
    {
        $this->lineItemCollection = $this->createMock(LineItemCollection::class);
        $this->subject = new LineItemContainer($this->lineItemCollection);
    }

    public function testGetLineItems(): void
    {
        $this->assertSame($this->lineItemCollection, $this->subject->getLineItems());
    }

    public function testGetEmptyRelationLink(): void
    {
        $this->assertNull($this->subject->getRelationLink());
        $this->assertFalse($this->subject->hasNext());
    }

    public function testSetNextRelationLink(): void
    {
        $relationUrl = 'http://next-url.org?rel=next';
        $this->subject->setRelationLink($relationUrl);

        $this->assertSame($relationUrl, $this->subject->getRelationLink());
        $this->assertTrue($this->subject->hasNext());
    }
}
