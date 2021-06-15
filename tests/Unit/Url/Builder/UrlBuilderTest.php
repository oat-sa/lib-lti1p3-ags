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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Url\Builder;

use InvalidArgumentException;
use OAT\Library\Lti1p3Ags\Url\Builder\UrlBuilder;
use OAT\Library\Lti1p3Ags\Url\Builder\UrlBuilderInterface;
use PHPUnit\Framework\TestCase;

class UrlBuilderTest extends TestCase
{
    /** @var UrlBuilderInterface */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new UrlBuilder();
    }

    public function testBuild(): void
    {
        $this->assertEquals(
            'https://user:pass@example.com?a=b#e',
            $this->subject->build('https://user:pass@example.com?a=b#e')
        );

        $this->assertEquals(
            'https://user:pass@example.com/scores?a=b#e',
            $this->subject->build(
                'https://user:pass@example.com?a=b#e',
                'scores'
            )
        );

        $this->assertEquals(
            'https://user:pass@example.com/scores?a=b#e',
            $this->subject->build(
                'https://user:pass@example.com?a=b#e',
                '/scores'
            )
        );

        $this->assertEquals(
            'https://user:pass@example.com?a=b&c=d#e',
            $this->subject->build(
                'https://user:pass@example.com?a=b#e',
                null,
                [
                    'c' => 'd',
                ]
            )
        );

        $this->assertEquals(
            'https://user:pass@example.com/line-items/lineItemIdentifier/scores?a=b&c=d#e',
            $this->subject->build(
                'https://user:pass@example.com/line-items/lineItemIdentifier?a=b#e',
                'scores',
                [
                    'c' => 'd',
                ]
            )
        );

        $this->assertEquals(
            'https://user:pass@example.com/line-items/lineItemIdentifier/scores?a=b&c=d#e',
            $this->subject->build(
                'https://user:pass@example.com/line-items/lineItemIdentifier?a=b#e',
                '/scores',
                [
                    'c' => 'd',
                ]
            )
        );

        $this->assertEquals(
            'https://user:pass@example.com/line-items/lineItemIdentifier/sub-path/scores?a=b&c=d#e',
            $this->subject->build(
                'https://user:pass@example.com/line-items/lineItemIdentifier/sub-path?a=b#e',
                'scores',
                [
                    'c' => 'd',
                ]
            )
        );

        $this->assertEquals(
            'https://user:pass@example.com/line-items/lineItemIdentifier/sub-path/scores?a=b&c=d#e',
            $this->subject->build(
                'https://user:pass@example.com/line-items/lineItemIdentifier/sub-path?a=b#e',
                '/scores',
                [
                    'c' => 'd',
                ]
            )
        );

        $this->assertEquals(
            'https://user:pass@example.com/line-items/lineItemIdentifier/sub-path/scores?a=b&c=d#e',
            $this->subject->build(
                'https://user:pass@example.com/line-items/lineItemIdentifier?a=b#e',
                'sub-path/scores',
                [
                    'c' => 'd',
                ]
            )
        );

        $this->assertEquals(
            'https://user:pass@example.com/line-items/lineItemIdentifier/sub-path/scores?a=b&c=d#e',
            $this->subject->build(
                'https://user:pass@example.com/line-items/lineItemIdentifier?a=b#e',
                '/sub-path/scores',
                [
                    'c' => 'd',
                ]
            )
        );
    }

    public function testBuildFailure(): void
    {
        $invalidUrl = 'invalid';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Malformed url %s', $invalidUrl));

        $this->subject->build($invalidUrl);
    }
}
