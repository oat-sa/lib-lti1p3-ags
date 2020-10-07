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

use OAT\Library\Lti1p3Ags\Service\Server\RequestValidator\RequestValidatorException;
use OAT\Library\Lti1p3Ags\Service\Server\RequestValidator\RequiredContextIdValidator;
use OAT\Library\Lti1p3Ags\Tests\Unit\Traits\ServerRequestPathTestingTrait;
use PHPUnit\Framework\TestCase;

class RequiredContextIdValidatorTest extends TestCase
{
    use ServerRequestPathTestingTrait;

    /** @var RequiredContextIdValidator  */
    private $subject;

    public function setUp(): void
    {
        $this->subject = new RequiredContextIdValidator();
    }

    /**
     * @dataProvider validateProvider
     */
    public function testValidate($path): void
    {
        $this->subject->validate(
            $this->getMockForServerRequestWithPath($path)
        );

        $this->assertTrue(true);
    }

    /**
     * @dataProvider invalidateProvider
     */
    public function testInvalidate($path): void
    {
        $this->expectException(RequestValidatorException::class);
        $this->expectExceptionMessage('Url path must contain contextId as first uri path part.');
        $this->expectExceptionCode(400);

        $this->subject->validate(
            $this->getMockForServerRequestWithPath($path)
        );
    }

    public function validateProvider(): array
    {
        return [
            ['/toto/lineItem/id'],
            ['/123/lineItem/345'],
            ['toto/lineItem/345/'],
            ['/toto'],
            ['toto'],
            ['/toto/lineItem/'],
            ['/toto/lineItem'],
        ];
    }

    public function invalidateProvider(): array
    {
        return [
            ['/'],
            [''],
        ];
    }
}
