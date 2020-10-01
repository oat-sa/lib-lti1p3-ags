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

use OAT\Library\Lti1p3Ags\Exception\AgsHttpException;
use OAT\Library\Lti1p3Ags\Model\LineItem;
use OAT\Library\Lti1p3Ags\Model\LineItemContainer;
use OAT\Library\Lti1p3Ags\Repository\LineItemRepository;
use OAT\Library\Lti1p3Ags\Service\LineItem\Query\GetLineItemQuery;

class LineItemGetService implements LineItemGetServiceInterface
{
    /** @var LineItemRepository */
    private $repository;

    public function __construct(LineItemRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @throws AgsHttpException
     */
    public function getOne(GetLineItemQuery $query): LineItem
    {
        $this->assertQueryHasContextId($query);

        return $this->repository->findOne($query);
    }

    /**
     * @throws AgsHttpException
     */
    public function getAll(GetLineItemQuery $query): LineItemContainer
    {
        $this->assertQueryHasContextId($query);

        return $this->repository->findAll($query);
    }

    /**
     * @throws AgsHttpException
     */
    private function assertQueryHasContextId(GetLineItemQuery $query)
    {
        if (!$query->hasContextId()) {
            throw new AgsHttpException('Missing "contextId" parameter.', 400);
        }
    }
}
