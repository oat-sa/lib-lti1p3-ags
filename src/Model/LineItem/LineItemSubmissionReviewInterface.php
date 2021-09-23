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

namespace OAT\Library\Lti1p3Ags\Model\LineItem;

use JsonSerializable;
use OAT\Library\Lti1p3Ags\Model\Score\ScoreInterface;

/**
 * @see https://www.imsglobal.org/spec/lti-sr/v1p0#on-a-per-line-item-basis-0
 */
interface LineItemSubmissionReviewInterface extends JsonSerializable
{
    public const REVIEWABLE_STATUS_NONE = 'None';

    public const SUPPORTED_REVIEWABLE_STATUSES = [
        ScoreInterface::ACTIVITY_PROGRESS_STATUS_INITIALIZED,
        ScoreInterface::ACTIVITY_PROGRESS_STATUS_STARTED,
        ScoreInterface::ACTIVITY_PROGRESS_STATUS_IN_PROGRESS,
        ScoreInterface::ACTIVITY_PROGRESS_STATUS_SUBMITTED,
        ScoreInterface::ACTIVITY_PROGRESS_STATUS_COMPLETED,
        self::REVIEWABLE_STATUS_NONE
    ];

    public function getReviewableStatuses(): array;

    public function setReviewableStatuses(array $reviewableStatuses): LineItemSubmissionReviewInterface;

    public function getLabel(): ?string;

    public function setLabel(?string $label): LineItemSubmissionReviewInterface;

    public function getUrl(): ?string;

    public function setUrl(?string $url): LineItemSubmissionReviewInterface;

    public function getCustomProperties(): array;

    public function setCustomProperties(array $customProperties): LineItemSubmissionReviewInterface;
}
