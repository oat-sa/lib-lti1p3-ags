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

use OAT\Library\Lti1p3Ags\Model\LineItem;
use OAT\Library\Lti1p3Ags\Repository\LineItemRepository;
use OAT\Library\Lti1p3Ags\Service\LineItem\LineItemCreateService;
use PHPUnit\Framework\TestCase;

class LineItemCreateServiceTest extends TestCase
{
    /** @var LineItemCreateService */
    private $subject;

    /** @var LineItemRepository */
    private $repository;

    public function setUp()
    {
        $this->repository = $this->createMock(LineItemRepository::class);
        $this->subject = new LineItemCreateService($this->repository);
    }

    public function testCreate(): void
    {
        $lineItem = $this->createMock(LineItem::class);

        $this->repository
            ->expects($this->once())
            ->method('create')
            ->with($lineItem);

        $this->subject->create($lineItem);
    }
}