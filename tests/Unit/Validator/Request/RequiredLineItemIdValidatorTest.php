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

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Validator\Request;

use InvalidArgumentException;
use OAT\Library\Lti1p3Ags\Validator\Request\RequiredLineItemIdValidator;
use OAT\Library\Lti1p3Core\Tests\Traits\NetworkTestingTrait;
use PHPUnit\Framework\TestCase;

class RequiredLineItemIdValidatorTest extends TestCase
{
    use NetworkTestingTrait;

    /** @var RequiredLineItemIdValidator  */
    private $subject;

    public function setUp(): void
    {
        $this->subject = new RequiredLineItemIdValidator();
    }

    /**
     * @dataProvider validateProvider
     */
    public function testValidate($path): void
    {
        $this->subject->validate(
            $this->createServerRequest('GET', $path)
        );

        $this->assertTrue(true);
    }

    /**
     * @dataProvider invalidateProvider
     */
    public function testInvalidate($path): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Url path must contain lineItemId as third uri path part.');

        $this->subject->validate(
            $this->createServerRequest('GET', $path)
        );
    }

    public function validateProvider(): array
    {
        return [
            ['/toto/lineItem/id'],
            ['/123/lineItem/345'],
            ['toto/lineItem/345/'],
            ['toto/lineItem/345/antoher/action'],
        ];
    }

    public function invalidateProvider(): array
    {
        return [
            ['/'],
            [''],
            ['/toto'],
            ['toto'],
            ['/toto/lineItem/'],
            ['/toto/lineItem'],
        ];
    }
}
