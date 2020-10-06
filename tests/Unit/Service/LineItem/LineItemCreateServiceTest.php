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

use DateTimeImmutable;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItem;
use OAT\Library\Lti1p3Ags\Repository\LineItemRepositoryInterface;
use OAT\Library\Lti1p3Ags\Service\LineItem\LineItemCreateService;
use OAT\Library\Lti1p3Ags\Service\Server\RequestValidator\RequestValidatorException;
use PHPUnit\Framework\TestCase;

class LineItemCreateServiceTest extends TestCase
{
    /** @var LineItemCreateService */
    private $subject;

    /** @var LineItemRepositoryInterface */
    private $repository;

    public function setUp(): void
    {
        $this->repository = $this->createMock(LineItemRepositoryInterface::class);
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

    public function testCreateWithInvalidDates(): void
    {
        $lineItem = new LineItem(
            'someId',
            100,
            'label',
            null,
            new DateTimeImmutable('tomorrow'),
            new DateTimeImmutable('today')
        );

        $this->expectException(RequestValidatorException::class);
        $this->expectExceptionMessage(
            sprintf(
                'Value of startDateTime (%s) time should be lower or equal than endDateTime (%s)',
                $lineItem->getStartDateTime()->format('Y-m-d H:i:s'),
                $lineItem->getEndDateTime()->format('Y-m-d H:i:s')
            )
        );

        $this->subject->create($lineItem);
    }
}
