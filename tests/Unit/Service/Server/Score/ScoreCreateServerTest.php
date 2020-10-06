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

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Service\Server\Score;

use OAT\Library\Lti1p3Ags\Service\Server\Score\ScoreCreateServer;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class ScoreCreateServerTest extends TestCase
{
    /** @var ScoreCreateServer */
    private $subject;

    public function setUp()
    {
        $this->subject = new ScoreCreateServer();
    }

    public function testEndpointIsNotImplemented()
    {
        $response = $this->subject->handle(
            $this->createMock(ServerRequestInterface::class)
        );

        $this->assertSame(501, $response->getStatusCode());
    }
}
