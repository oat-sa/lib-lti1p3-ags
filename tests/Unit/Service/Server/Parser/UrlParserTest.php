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

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Service\Server\Parser;

use OAT\Library\Lti1p3Ags\Service\Server\Parser\UrlParser;
use OAT\Library\Lti1p3Ags\Tests\Unit\Traits\ServerRequestPathTestingTrait;
use PHPUnit\Framework\TestCase;

class UrlParserTest extends TestCase
{
    use ServerRequestPathTestingTrait;

    private $subject;

    public function setUp(): void
    {
        $this->subject = new UrlParser();
    }

    /**
     * @dataProvider parseUrlProvider
     */
    public function testParseUrl($path, $expected): void
    {
        $this->assertSame(
            $expected,
            $this->subject->parse(
                $this->getMockForServerRequest($path)
            )
        );
    }

    public function parseUrlProvider(): array
    {
        return [
            [
                '/toto/lineItem/id', ['contextId' => 'toto', 'lineItemId' => 'id']
            ],
            [
                '/123/lineItem/345', ['contextId' => '123', 'lineItemId' => '345']
            ],
            [
                'toto/lineItem/345/', ['contextId' => 'toto', 'lineItemId' => '345']
            ],
            [
                '/toto', ['contextId' => 'toto', 'lineItemId' => null]
            ],
            [
                'toto', ['contextId' => 'toto', 'lineItemId' => null]
            ],
            [
                '/toto/lineItem/', ['contextId' => 'toto', 'lineItemId' => null]
            ],
            [
                '/toto/lineItem', ['contextId' => 'toto', 'lineItemId' => null]
            ],
            [
                'toto/lineItem/abcdef/another/action', ['contextId' => 'toto', 'lineItemId' => 'abcdef']
            ],
        ];
    }
}
