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

use OAT\Library\Lti1p3Ags\Model\Result\Result;
use OAT\Library\Lti1p3Ags\Model\Result\ResultInterface;
use OAT\Library\Lti1p3Ags\Tests\Traits\AgsDomainTestingTrait;
use OAT\Library\Lti1p3Core\Util\Collection\Collection;
use PHPUnit\Framework\TestCase;

class ResultTest extends TestCase
{
    use AgsDomainTestingTrait;

    /** @var ResultInterface */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new Result(
            'resultUserIdentifier',
            'resultLineItemIdentifier'
        );
    }

    public function testDefaults(): void
    {
        $this->assertEquals('resultUserIdentifier', $this->subject->getUserIdentifier());
        $this->assertEquals('resultLineItemIdentifier', $this->subject->getLineItemIdentifier());

        $this->assertNull($this->subject->getIdentifier());
        $this->assertNull($this->subject->getResultScore());
        $this->assertNull($this->subject->getResultMaximum());
        $this->assertNull($this->subject->getComment());
        $this->assertEmpty($this->subject->getAdditionalProperties()->all());

        $this->assertEquals(
            [
                'userId' => 'resultUserIdentifier',
                'scoreOf' => 'resultLineItemIdentifier',
            ],
            $this->subject->jsonSerialize()
        );
    }

    public function testUserIdentifier(): void
    {
        $this->subject->setUserIdentifier('resultOtherUserIdentifier');

        $this->assertEquals('resultOtherUserIdentifier', $this->subject->getUserIdentifier());
    }

    public function testLineItemIdentifier(): void
    {
        $this->subject->setLineItemIdentifier('resultOtherLineItemIdentifier');

        $this->assertEquals('resultOtherLineItemIdentifier', $this->subject->getLineItemIdentifier());
    }

    public function testIdentifier(): void
    {
        $this->subject->setIdentifier('resultIdentifier');

        $this->assertEquals('resultIdentifier', $this->subject->getIdentifier());
    }

    public function testResultScore(): void
    {
        $this->subject->setResultScore(10);

        $this->assertEquals(10, $this->subject->getResultScore());
    }

    public function testResultMaximum(): void
    {
        $this->subject->setResultMaximum(10);

        $this->assertEquals(10, $this->subject->getResultMaximum());
    }

    public function testComment(): void
    {
        $this->subject->setComment('resultComment');

        $this->assertEquals('resultComment', $this->subject->getComment());
    }

    public function testAdditionalProperties(): void
    {
        $additionalProperties = (new Collection())->add(['key' => 'value']);

        $this->subject->setAdditionalProperties($additionalProperties);

        $this->assertSame($additionalProperties, $this->subject->getAdditionalProperties());
    }

    public function testJsonSerialize(): void
    {
        $subject = $this->createTestResult();

        $this->assertEquals(
            [
                'id' => 'resultIdentifier',
                'scoreOf' => 'resultLineItemIdentifier',
                'userId' => 'resultUserIdentifier',
                'resultScore' => (float)10,
                'resultMaximum' => (float)100,
                'comment' => 'resultComment',
                'key' => 'value'
            ],
            $subject->jsonSerialize()
        );
    }
}
