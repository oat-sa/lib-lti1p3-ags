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

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Model;

use OAT\Library\Lti1p3Ags\Model\Result;
use PHPUnit\Framework\TestCase;

class ResultTest extends TestCase
{
    /** @var Result */
    private $subject;

    public function setUp(): void
    {
        $this->subject = new Result(
            'id',
            'userId',
            0.5,
            10,
            'comment',
            'math'
        );
    }

    public function testGetId(): void
    {
        $this->assertSame('id', $this->subject->getId());
    }

    public function testGetUserId(): void
    {
        $this->assertSame('userId', $this->subject->getUserId());
    }

    public function testGetResultScore(): void
    {
        $this->assertSame(0.5, $this->subject->getResultScore());
    }

    public function testGetResultMaximum(): void
    {
        $this->assertSame(10, $this->subject->getResultMaximum());
    }

    public function testGetComment(): void
    {
        $this->assertSame('comment', $this->subject->getComment());
    }

    public function testGetScoreOf(): void
    {
        $this->assertSame('math', $this->subject->getScoreOf());
    }
}
