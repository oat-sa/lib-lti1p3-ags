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

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Service\Score\Query;

use OAT\Library\Lti1p3Ags\Model\Score;
use OAT\Library\Lti1p3Ags\Service\LineItem\Query\ScoreCreateQuery;
use PHPUnit\Framework\TestCase;

class ScoreCreateQueryTest extends TestCase
{
    public function testScoreCreateQueryGetter()
    {
        $contextId = 'context-id';
        $lineItemId = 'line-item-id';
        $score = $this->createMock(Score::class);

        $lineItemQuery = new ScoreCreateQuery($contextId, $lineItemId, $score);

        $this->assertSame($contextId, $lineItemQuery->getContextId());
        $this->assertSame($lineItemId, $lineItemQuery->getLineItemId());
        $this->assertSame($score, $lineItemQuery->getScore());
    }
}
