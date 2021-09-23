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

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Factory\LineItem;

use InvalidArgumentException;
use OAT\Library\Lti1p3Ags\Factory\LineItem\LineItemSubmissionReviewFactory;
use OAT\Library\Lti1p3Ags\Factory\LineItem\LineItemSubmissionReviewFactoryInterface;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemSubmissionReviewInterface;
use PHPUnit\Framework\TestCase;

class LineItemSubmissionReviewFactoryTest extends TestCase
{
    /** @var LineItemSubmissionReviewFactoryInterface */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new LineItemSubmissionReviewFactory();
    }

    public function testCreateSuccess(): void
    {
        $data = [
            'reviewableStatus' => LineItemSubmissionReviewInterface::SUPPORTED_REVIEWABLE_STATUSES,
            'label' => 'label',
            'url' => 'url',
            'custom' => [
                'key' => 'value'
            ]
        ];

        $submissionReview = $this->subject->create($data);

        $this->assertInstanceOf(LineItemSubmissionReviewInterface::class, $submissionReview);

        $this->assertEquals(
            LineItemSubmissionReviewInterface::SUPPORTED_REVIEWABLE_STATUSES,
            $submissionReview->getReviewableStatuses()
        );
        $this->assertEquals($data['label'], $submissionReview->getLabel());
        $this->assertEquals($data['url'], $submissionReview->getUrl());
        $this->assertEquals(
            [
                'key' => 'value'
            ],
            $submissionReview->getCustomProperties()
        );
    }

    public function testCreateFailureOnMissingReviewableStatuses(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Missing mandatory reviewableStatus');

        $this->subject->create([]);
    }
}
