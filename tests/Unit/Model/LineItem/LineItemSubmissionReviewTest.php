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

use InvalidArgumentException;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemSubmissionReview;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemSubmissionReviewInterface;
use OAT\Library\Lti1p3Ags\Tests\Traits\AgsDomainTestingTrait;
use PHPUnit\Framework\TestCase;

class LineItemSubmissionReviewTest extends TestCase
{
    use AgsDomainTestingTrait;

    /** @var LineItemSubmissionReviewInterface */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new LineItemSubmissionReview(
            [
                LineItemSubmissionReviewInterface::REVIEWABLE_STATUS_NONE
            ]
        );
    }

    public function testDefaults(): void
    {
        $this->assertEquals(
            [
                LineItemSubmissionReviewInterface::REVIEWABLE_STATUS_NONE
            ],
            $this->subject->getReviewableStatuses()
        );

        $this->assertNull($this->subject->getLabel());
        $this->assertNull($this->subject->getUrl());
        $this->assertEmpty($this->subject->getCustomProperties());
    }

    public function testReviewableStatuses(): void
    {
        $this->subject->setReviewableStatuses(LineItemSubmissionReviewInterface::SUPPORTED_REVIEWABLE_STATUSES);

        $this->assertEquals(
            LineItemSubmissionReviewInterface::SUPPORTED_REVIEWABLE_STATUSES,
            $this->subject->getReviewableStatuses()
        );
    }

    public function testSetReviewableStatusesWithInvalidStatus(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Line item reviewable status invalid is not supported');

        $this->subject->setReviewableStatuses(['invalid']);
    }

    public function testLabel(): void
    {
        $this->subject->setLabel('label');

        $this->assertEquals('label', $this->subject->getLabel());
    }

    public function testUrl(): void
    {
        $this->subject->setUrl('url');

        $this->assertEquals('url', $this->subject->getUrl());
    }

    public function testCustomProperties(): void
    {
        $customProperties = [
            'key' => 'value'
        ];

        $this->subject->setCustomProperties($customProperties);

        $this->assertEquals($customProperties, $this->subject->getCustomProperties());
    }

    public function testJsonSerialize(): void
    {
        $subject = new LineItemSubmissionReview(
            LineItemSubmissionReviewInterface::SUPPORTED_REVIEWABLE_STATUSES,
            'label',
            'url',
            [
                'key' => 'value',
            ]
        );

        $this->assertEquals(
            [
                'reviewableStatus' => LineItemSubmissionReviewInterface::SUPPORTED_REVIEWABLE_STATUSES,
                'label' => 'label',
                'url' => 'url',
                'custom' => [
                    'key' => 'value',
                ]
            ],
            $subject->jsonSerialize()
        );
    }
}
