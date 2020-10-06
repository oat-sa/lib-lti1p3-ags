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
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemContainer;
use OAT\Library\Lti1p3Ags\Repository\LineItemRepositoryInterface;

class LineItemGetService implements LineItemGetServiceInterface
{
    /** @var LineItemRepositoryInterface */
    private $repository;

    public function __construct(LineItemRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function findAll(string $contextId, int $page = null, int $limit = null): LineItemContainer
    {
        return $this->repository->findAll($contextId, $page, $limit);
    }

    public function findOne(string $contextId, string $lineItemId): LineItem
    {
        return $this->repository->findOne($contextId, $lineItemId);
    }
}
