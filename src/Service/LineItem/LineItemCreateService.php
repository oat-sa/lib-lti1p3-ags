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

namespace OAT\Library\Lti1p3Ags\Service\LineItem;

use OAT\Library\Lti1p3Ags\Model\LineItem\LineItem;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemInterface;
use OAT\Library\Lti1p3Ags\Repository\LineItemRepositoryInterface;
use OAT\Library\Lti1p3Ags\Service\Server\RequestValidator\RequestValidatorException;

class LineItemCreateService implements LineItemCreateServiceInterface
{
    /** @var LineItemRepositoryInterface */
    private $repository;

    public function __construct(LineItemRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @inheritDoc
     */
    public function create(LineItemInterface $lineItem): void //@TODO Use LineItemInterface instead
    {
        //@TODO Add extra domain validations here (check specs)

        /*
        {
          "startDateTime": "2020-10-06T13:59:10.213Z",
          "endDateTime": "2020-10-06T13:59:10.213Z",
          "scoreMaximum": 0,
          "label": "string",
          "tag": "string",
          "resourceId": "string",
          "resourceLinkId": "string"
        }
        */

        if ($lineItem->getStartDateTime() > $lineItem->getEndDateTime()) {
            throw new RequestValidatorException(
                sprintf(
                    'Value of startDateTime (%s) time should be lower or equal than endDateTime (%s)',
                    $lineItem->getStartDateTime()->format('Y-m-d H:i:s'),
                    $lineItem->getEndDateTime()->format('Y-m-d H:i:s')
                )
            );
        }

        $this->repository->create($lineItem);
    }
}
