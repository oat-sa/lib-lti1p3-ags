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

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Service\Server\RequestValidator;

use OAT\Library\Lti1p3Ags\Service\Server\RequestValidator\RequestMethodValidator;
use OAT\Library\Lti1p3Ags\Service\Server\RequestValidator\RequestValidatorException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class RequestMethodValidatorTest extends TestCase
{
    public function testValidHttpRequestMethod(): void
    {
        $httpMethod = 'titi';

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects($this->once())
            ->method('getMethod')
            ->willReturn($httpMethod);

        $subject = new RequestMethodValidator($httpMethod);

        $subject->validate($request);
    }

    public function testValidCaseInsensitiveHttpRequestMethod(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects($this->once())
            ->method('getMethod')
            ->willReturn('InSenSiTIve');

        $subject = new RequestMethodValidator('inSENsitivE');

        $subject->validate($request);
    }

    public function testInvalidHttpRequestMethod(): void
    {
        $httpMethod = 'titi';

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects($this->once())
            ->method('getMethod')
            ->willReturn('toto');

        $this->expectException(RequestValidatorException::class);
        $this->expectExceptionMessage(sprintf('Expected http method is "%s".', $httpMethod));
        $this->expectExceptionCode(405);

        $subject = new RequestMethodValidator($httpMethod);

        $subject->validate($request);
    }
}
