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

use OAT\Library\Lti1p3Ags\Service\Server\RequestValidator\CreateLineItemValidator;
use OAT\Library\Lti1p3Ags\Service\Server\RequestValidator\RequestValidatorException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class CreateLineItemValidatorTest extends TestCase
{
    /** @var CreateLineItemValidator */
    private $validator;

    public function setUp(): void
    {
        $this->validator = new CreateLineItemValidator();
    }

    public function testValidateWithCorrectDataDoesNotThrowException(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getBody')
            ->willReturn(
                json_encode(
                    [
                        'scoreMaximum' => 100,
                        'label' => 'My Label',
                        'id' => 'myId',
                        'startDateTime' => '2010-10-10T00:00:00+00:00',
                        'endDateTime' => '2010-10-10T00:59:59+00:00',
                        'tag' => 'My tag',
                        'resourceId' => 'myResourceId',
                        'resourceLinkId' => 'myResourceLinkId',
                    ]
                )
            );

        $this->assertNull($this->validator->validate($request));
    }

    public function testValidateInvalidJson(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getBody')
            ->willReturn('{"": }');

        $this->expectException(RequestValidatorException::class);
        $this->expectExceptionMessage('Invalid json: Syntax error');

        $this->validator->validate($request);
    }

    public function testValidateMissingParameters(): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getBody')
            ->willReturn('{}');

        $this->expectException(RequestValidatorException::class);
        $this->expectExceptionMessage('All required fields were not provided');

        $this->validator->validate($request);
    }

    /**
     * @dataProvider getNotEmptyValidators
     */
    public function testValidateEmptyParameters(string $emptyField): void
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->once())
            ->method('getBody')
            ->willReturn(
                json_encode(
                    [
                        'scoreMaximum' => 100,
                        'label' => 'My Label',
                        $emptyField => ''
                    ]
                )
            );

        $this->expectException(RequestValidatorException::class);
        $this->expectExceptionMessage(sprintf('Field %s cannot have an empty value', $emptyField));

        $this->validator->validate($request);
    }

    public function getNotEmptyValidators(): array
    {
        return [
            ['startDateTime'],
            ['endDateTime'],
            ['tag'],
            ['resourceId'],
            ['resourceLinkId'],
        ];
    }
}
