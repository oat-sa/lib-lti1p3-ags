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

namespace OAT\Library\Lti1p3Ags\Factory\LineItem;

use Carbon\Carbon;
use InvalidArgumentException;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItem;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemInterface;

class LineItemFactory implements LineItemFactoryInterface
{
    /** @var LineItemSubmissionReviewFactoryInterface */
    private $submissionReviewFactory;

    public function __construct(?LineItemSubmissionReviewFactoryInterface $submissionReviewFactory = null)
    {
        $this->submissionReviewFactory = $submissionReviewFactory ?? new LineItemSubmissionReviewFactory();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function create(array $data): LineItemInterface
    {
        $scoreMaximum = $data['scoreMaximum'] ?? null;

        if (null === $scoreMaximum) {
            throw new InvalidArgumentException('Missing mandatory scoreMaximum');
        }

        $label = $data['label'] ?? null;

        if (null === $label) {
            throw new InvalidArgumentException('Missing mandatory label');
        }

        $submissionReview = null;

        if (array_key_exists('submissionReview', $data)) {
            $submissionReview = $this->submissionReviewFactory->create($data['submissionReview']);
        }

        $additionalProperties = array_diff_key(
            $data,
            array_flip(
                [
                    'id',
                    'scoreMaximum',
                    'label',
                    'resourceId',
                    'resourceLinkId',
                    'tag',
                    'startDateTime',
                    'endDateTime',
                    'submissionReview'
                ]
            )
        );

        return new LineItem(
            (float)$scoreMaximum,
            (string)$label,
            $data['id'] ?? null,
            $data['resourceId'] ?? null,
            $data['resourceLinkId'] ?? null,
            $data['tag'] ?? null,
            isset($data['startDateTime']) ? new Carbon($data['startDateTime']) : null,
            isset($data['endDateTime']) ? new Carbon($data['endDateTime']) : null,
            $submissionReview,
            $additionalProperties
        );
    }
}
