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

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Model\Result;

use OAT\Library\Lti1p3Ags\Model\Result\ResultCollectionInterface;
use OAT\Library\Lti1p3Ags\Model\Result\ResultContainer;
use OAT\Library\Lti1p3Ags\Model\Result\ResultContainerInterface;
use OAT\Library\Lti1p3Ags\Model\Result\ResultInterface;
use OAT\Library\Lti1p3Ags\Tests\Traits\AgsDomainTestingTrait;
use PHPUnit\Framework\TestCase;

class ResultContainerTest extends TestCase
{
    use AgsDomainTestingTrait;

    /** @var ResultContainerInterface */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new ResultContainer(
            $this->createTestResultCollection(),
            '<http://example.com/results>; rel="next"'
        );
    }

    public function testLineItems(): void
    {
        $this->assertInstanceOf(ResultCollectionInterface::class, $this->subject->getResults());
        $this->assertEquals(3, $this->subject->getResults()->count());

        foreach ($this->subject->getResults() as $result) {
            $this->assertInstanceOf(ResultInterface::class, $result);
        }
    }

    public function testRelationLink(): void
    {
        $this->assertEquals('<http://example.com/results>; rel="next"', $this->subject->getRelationLink());

        $this->subject->setRelationLink('<http://example.com/other/results>; rel="next"');

        $this->assertEquals('<http://example.com/other/results>; rel="next"', $this->subject->getRelationLink());
    }

    public function testGetRelationLinkUrl(): void
    {
        $this->assertEquals('http://example.com/results', $this->subject->getRelationLinkUrl());

        $this->subject->setRelationLink('<http://example.com/results>; rel="next"');
        $this->assertEquals('http://example.com/results', $this->subject->getRelationLinkUrl());

        $this->subject->setRelationLink(' <http://example.com/results>; rel="next"');
        $this->assertEquals('http://example.com/results', $this->subject->getRelationLinkUrl());

        $this->subject->setRelationLink('<http://example.com/results> ; rel="next"');
        $this->assertEquals('http://example.com/results', $this->subject->getRelationLinkUrl());

        $this->subject->setRelationLink('<http://example.com/results>');
        $this->assertEquals('http://example.com/results', $this->subject->getRelationLinkUrl());

        $this->subject->setRelationLink(null);
        $this->assertNull($this->subject->getRelationLinkUrl());
    }

    public function testRelation(): void
    {
        $this->assertTrue($this->subject->hasNext());

        $this->subject->setRelationLink('<http://example.com/results>; rel="other"');

        $this->assertFalse($this->subject->hasNext());

        $this->subject->setRelationLink(null);

        $this->assertFalse($this->subject->hasNext());
    }

    public function testJsonSerialize(): void
    {
        $this->assertEquals(
            [
                'results' => $this->subject->getResults(),
                'relationLink' => $this->subject->getRelationLink()
            ],
            $this->subject->jsonSerialize()
        );
    }
}
