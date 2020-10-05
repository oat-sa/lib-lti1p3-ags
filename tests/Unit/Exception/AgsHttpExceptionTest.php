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

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Exception;

use OAT\Library\Lti1p3Ags\Exception\AgsHttpException;
use PHPUnit\Framework\TestCase;

class AgsHttpExceptionTest extends TestCase
{
    /**
     * @dataProvider getReasonPhraseFor401ExceptionProvider
     */
    public function testGetReasonPhrase(int $code, ?string $reasonPhrase): void
    {
        $exception = new AgsHttpException('some message', $code);
        $this->assertSame($reasonPhrase, $exception->getReasonPhrase());
    }

    public function getReasonPhraseFor401ExceptionProvider(): array
    {
        return [
            [400, 'Bad Request'],
            [401, 'Unauthorized'],
            [405, 'Method not allowed'],
            [422, 'Unprocessable Entity'],
            [500, 'Internal Error'],
            [999, null],
        ];
    }
}