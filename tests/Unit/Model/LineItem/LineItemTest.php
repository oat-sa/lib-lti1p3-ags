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

use Carbon\Carbon;
use DateTimeInterface;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItem;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemInterface;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemSubmissionReview;
use OAT\Library\Lti1p3Ags\Tests\Traits\AgsDomainTestingTrait;
use OAT\Library\Lti1p3Core\Util\Collection\Collection;
use PHPUnit\Framework\TestCase;

class LineItemTest extends TestCase
{
    use AgsDomainTestingTrait;

    /** @var LineItemInterface */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new LineItem(
            100,
            'lineItemLabel'
        );
    }

    public function testDefaults(): void
    {
        $this->assertEquals(100, $this->subject->getScoreMaximum());
        $this->assertEquals('lineItemLabel', $this->subject->getLabel());

        $this->assertNull($this->subject->getIdentifier());
        $this->assertNull($this->subject->getUrl());
        $this->assertNull($this->subject->getResourceIdentifier());
        $this->assertNull($this->subject->getResourceLinkIdentifier());
        $this->assertNull($this->subject->getTag());
        $this->assertNull($this->subject->getStartDateTime());
        $this->assertNull($this->subject->getEndDateTime());
        $this->assertNull($this->subject->getSubmissionReview());
        $this->assertEmpty($this->subject->getAdditionalProperties()->all());

        $this->assertEquals(
            [
                'scoreMaximum' => 100,
                'label' => 'lineItemLabel',
            ],
            $this->subject->jsonSerialize()
        );
    }

    public function testScoreMaximum(): void
    {
        $this->subject->setScoreMaximum(50);

        $this->assertEquals(50, $this->subject->getScoreMaximum());
    }

    public function testLabel(): void
    {
        $this->subject->setLabel('otherLabel');

        $this->assertEquals('otherLabel', $this->subject->getLabel());
    }

    public function testIdentifier(): void
    {
        $this->subject->setIdentifier('https://example.com/line-items/otherLineItemIdentifier');

        $this->assertEquals(
            'https://example.com/line-items/otherLineItemIdentifier',
            $this->subject->getIdentifier()
        );
    }

    public function testResourceIdentifier(): void
    {
        $this->subject->setResourceIdentifier('lineItemResourceIdentifier');

        $this->assertEquals('lineItemResourceIdentifier', $this->subject->getResourceIdentifier());
    }

    public function testResourceLinkIdentifier(): void
    {
        $this->subject->setResourceLinkIdentifier('lineItemResourceLinkIdentifier');

        $this->assertEquals('lineItemResourceLinkIdentifier', $this->subject->getResourceLinkIdentifier());
    }

    public function testTag(): void
    {
        $this->subject->setTag('tag');

        $this->assertEquals('tag', $this->subject->getTag());
    }

    public function testStartDateTime(): void
    {
        $now = Carbon::now();

        $this->subject->setStartDateTime($now);

        $this->assertEquals($now, $this->subject->getStartDateTime());
    }

    public function testEndDateTime(): void
    {
        $now = Carbon::now();

        $this->subject->setEndDateTime($now);

        $this->assertEquals($now, $this->subject->getEndDateTime());
    }

    public function testSubmissionReview(): void
    {
        $submissionReview = new LineItemSubmissionReview([]);

        $this->subject->setSubmissionReview($submissionReview);

        $this->assertEquals($submissionReview, $this->subject->getSubmissionReview());
    }

    public function testAdditionalProperties(): void
    {
        $additionalProperties = (new Collection())->add(['key' => 'value']);

        $this->subject->setAdditionalProperties($additionalProperties);

        $this->assertSame($additionalProperties, $this->subject->getAdditionalProperties());
    }

    public function testCopy(): void
    {
        $this->subject->setIdentifier('https://example.com/line-items/preservedLineItemIdentifier');

        $lineItemToCopyFrom = $this->createTestLineItem();

        $this->subject->copy($lineItemToCopyFrom);

        $this->assertEquals(
            'https://example.com/line-items/preservedLineItemIdentifier',
            $this->subject->getIdentifier()
        );

        $this->assertEquals($lineItemToCopyFrom->getScoreMaximum(), $this->subject->getScoreMaximum());
        $this->assertEquals($lineItemToCopyFrom->getLabel(), $this->subject->getLabel());
        $this->assertEquals($lineItemToCopyFrom->getResourceIdentifier(), $this->subject->getResourceIdentifier());
        $this->assertEquals($lineItemToCopyFrom->getResourceLinkIdentifier(), $this->subject->getResourceLinkIdentifier());
        $this->assertEquals($lineItemToCopyFrom->getTag(), $this->subject->getTag());
        $this->assertEquals($lineItemToCopyFrom->getStartDateTime(), $this->subject->getStartDateTime());
        $this->assertEquals($lineItemToCopyFrom->getEndDateTime(), $this->subject->getEndDateTime());
        $this->assertSame($lineItemToCopyFrom->getAdditionalProperties(), $this->subject->getAdditionalProperties());
    }

    public function testJsonSerialize(): void
    {
        $start = Carbon::now();
        $end = Carbon::now()->addHour();

        $subject = $this
            ->createTestLineItem()
            ->setStartDateTime($start)
            ->setEndDateTime($end);

        $this->assertEquals(
            [
                'id' => 'https://example.com/line-items/lineItemIdentifier',
                'startDateTime' => $start->format(DateTimeInterface::ATOM),
                'endDateTime' => $end->format(DateTimeInterface::ATOM),
                'scoreMaximum' => (float)100,
                'label' => 'lineItemLabel',
                'tag' => 'lineItemTag',
                'resourceId' => 'lineItemResourceIdentifier',
                'resourceLinkId' => 'lineItemResourceLinkIdentifier',
                'key' => 'value'
            ],
            $subject->jsonSerialize()
        );
    }

    public function testJsonSerializeWithZeroValues(): void
    {
        $start = Carbon::now();
        $end = Carbon::now()->addHour();

        $subject = $this
            ->createTestLineItem()
            ->setStartDateTime($start)
            ->setEndDateTime($end)
            ->setScoreMaximum(0);

        $this->assertEquals(
            [
                'id' => 'https://example.com/line-items/lineItemIdentifier',
                'startDateTime' => $start->format(DateTimeInterface::ATOM),
                'endDateTime' => $end->format(DateTimeInterface::ATOM),
                'scoreMaximum' => 0,
                'label' => 'lineItemLabel',
                'tag' => 'lineItemTag',
                'resourceId' => 'lineItemResourceIdentifier',
                'resourceLinkId' => 'lineItemResourceLinkIdentifier',
                'key' => 'value'
            ],
            $subject->jsonSerialize()
        );
    }
}
