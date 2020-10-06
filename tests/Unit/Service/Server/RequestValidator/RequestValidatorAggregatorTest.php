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

use OAT\Library\Lti1p3Ags\Service\Server\RequestValidator\RequestValidatorAggregator;
use OAT\Library\Lti1p3Ags\Service\Server\RequestValidator\RequestValidatorInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class RequestValidatorAggregatorTest extends TestCase
{
    public function testAllValidatorsAreAggregated(): void
    {
        $validator = $this->createMock(RequestValidatorInterface::class);
        $validator
            ->expects($this->once())
            ->method('validate');

        $validator1 = $this->createMock(RequestValidatorInterface::class);
        $validator1
            ->expects($this->once())
            ->method('validate');


        $validator2 = $this->createMock(RequestValidatorInterface::class);
        $validator2
            ->expects($this->once())
            ->method('validate');

        $validator3 = $this->createMock(RequestValidatorInterface::class);
        $validator3
            ->expects($this->once())
            ->method('validate');

        $validators = [
            $validator,
            $validator1,
            $validator2,
            $validator3,
        ];

        $subject = new RequestValidatorAggregator(...$validators);
        $subject->validate($this->createMock(ServerRequestInterface::class));
    }
}
