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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Model\LineItem;

use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemCollectionInterface;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemContainer;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemContainerInterface;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemInterface;
use OAT\Library\Lti1p3Ags\Tests\Traits\AgsDomainTestingTrait;
use PHPUnit\Framework\TestCase;

class LineItemContainerTest extends TestCase
{
    use AgsDomainTestingTrait;

    /** @var LineItemContainerInterface */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new LineItemContainer(
            $this->createTestLineItemCollection(),
            '<http://example.com/line-items>; rel="next"'
        );
    }

    public function testLineItems(): void
    {
        $this->assertInstanceOf(LineItemCollectionInterface::class, $this->subject->getLineItems());
        $this->assertEquals(3, $this->subject->getLineItems()->count());

        foreach ($this->subject->getLineItems() as $lineItem) {
            $this->assertInstanceOf(LineItemInterface::class, $lineItem);
        }
    }

    public function testRelationLink(): void
    {
        $this->assertEquals('<http://example.com/line-items>; rel="next"', $this->subject->getRelationLink());

        $this->subject->setRelationLink('<http://example.com/other/line-items>; rel="next"');

        $this->assertEquals('<http://example.com/other/line-items>; rel="next"', $this->subject->getRelationLink());
    }

    public function testGetRelationLinkUrl(): void
    {
        $this->assertEquals('http://example.com/line-items', $this->subject->getRelationLinkUrl());

        $this->subject->setRelationLink('<http://example.com/line-items>; rel="next"');
        $this->assertEquals('http://example.com/line-items', $this->subject->getRelationLinkUrl());

        $this->subject->setRelationLink(' <http://example.com/line-items>; rel="next"');
        $this->assertEquals('http://example.com/line-items', $this->subject->getRelationLinkUrl());

        $this->subject->setRelationLink('<http://example.com/line-items> ; rel="next"');
        $this->assertEquals('http://example.com/line-items', $this->subject->getRelationLinkUrl());

        $this->subject->setRelationLink('<http://example.com/line-items>');
        $this->assertEquals('http://example.com/line-items', $this->subject->getRelationLinkUrl());

        $this->subject->setRelationLink(null);
        $this->assertNull($this->subject->getRelationLinkUrl());
    }

    public function testRelation(): void
    {
        $this->assertTrue($this->subject->hasNext());

        $this->subject->setRelationLink('<http://example.com/line-items>; rel="other"');

        $this->assertFalse($this->subject->hasNext());

        $this->subject->setRelationLink(null);

        $this->assertFalse($this->subject->hasNext());
    }

    public function testJsonSerialize(): void
    {
        $this->assertEquals(
            [
                'lineItems' => $this->subject->getLineItems(),
                'relationLink' => $this->subject->getRelationLink()
            ],
            $this->subject->jsonSerialize()
        );
    }
}
