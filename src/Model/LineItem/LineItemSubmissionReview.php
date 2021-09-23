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

use InvalidArgumentException;

/**
 * @see https://www.imsglobal.org/spec/lti-sr/v1p0#on-a-per-line-item-basis-0
 */
class LineItemSubmissionReview implements LineItemSubmissionReviewInterface
{
    /** @var string[] */
    private $reviewableStatuses;

    /** @var string|null */
    private $label;

    /** @var string|null */
    private $url;

    /** @var string[] */
    private $customProperties;

    public function __construct(
        array $reviewableStatuses,
        ?string $label = null,
        ?string $url = null,
        array $customProperties = []
    ) {
        $this->setReviewableStatuses($reviewableStatuses);

        $this->label = $label;
        $this->url = $url;
        $this->customProperties = $customProperties;
    }

    public function getReviewableStatuses(): array
    {
        return $this->reviewableStatuses;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setReviewableStatuses(array $reviewableStatuses): LineItemSubmissionReviewInterface
    {
        foreach ($reviewableStatuses as $reviewableStatus) {
            if (!in_array($reviewableStatus, self::SUPPORTED_REVIEWABLE_STATUSES)) {
                throw new InvalidArgumentException(
                    sprintf('Line item reviewable status %s is not supported', $reviewableStatus)
                );
            }
        }

        $this->reviewableStatuses = $reviewableStatuses;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): LineItemSubmissionReviewInterface
    {
        $this->label = $label;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): LineItemSubmissionReviewInterface
    {
        $this->url = $url;

        return $this;
    }

    public function getCustomProperties(): array
    {
        return $this->customProperties;
    }

    public function setCustomProperties(array $customProperties): LineItemSubmissionReviewInterface
    {
        $this->customProperties = $customProperties;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return array_filter(
            [
                'reviewableStatus' => $this->reviewableStatuses,
                'label' => $this->label,
                'url' => $this->url,
                'custom' => $this->customProperties,
            ]
        );
    }
}
