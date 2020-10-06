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

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Service\LineItem;

use OAT\Library\Lti1p3Ags\Exception\AgsHttpException;
use OAT\Library\Lti1p3Ags\Repository\LineItemRepositoryInterface;
use OAT\Library\Lti1p3Ags\Service\LineItem\LineItemGetService;
use OAT\Library\Lti1p3Ags\Service\LineItem\Query\LineItemQuery;
use PHPUnit\Framework\TestCase;

class LineItemGetServiceTest extends TestCase
{
    /** @var LineItemGetService  */
    private $subject;

    /** @var LineItemRepositoryInterface */
    private $repository;

    public function setUp(): void
    {
        $this->repository = $this->createMock(LineItemRepositoryInterface::class);
        $this->subject = new LineItemGetService($this->repository);
    }

    public function testFindOne(): void
    {
        $contextId = 'context-id';
        $lineItemId = 'lineItem-id';

        $this->repository
            ->expects($this->once())
            ->method('findOne')
            ->with($contextId, $lineItemId);

        $this->subject->findOne($contextId, $lineItemId);
    }

    public function testFindAll(): void
    {
        $contextId = 'context-id';
        $page = 1;
        $limit = 100;

        $this->repository
            ->expects($this->once())
            ->method('findAll')
            ->with($contextId, $page, $limit);

        $this->subject->findAll($contextId, $page, $limit);
    }
}
