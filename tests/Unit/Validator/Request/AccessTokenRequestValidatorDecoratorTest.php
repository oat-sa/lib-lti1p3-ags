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

use Exception;
use OAT\Library\Lti1p3Ags\Service\LineItem\LineItemServiceInterface;
use OAT\Library\Lti1p3Ags\Validator\Request\AccessTokenRequestValidatorDecorator;
use OAT\Library\Lti1p3Core\Service\Server\Validator\AccessTokenRequestValidationResult;
use OAT\Library\Lti1p3Core\Service\Server\Validator\AccessTokenRequestValidator;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class AccessTokenRequestValidatorDecoratorTest extends TestCase
{
    /** @var AccessTokenRequestValidatorDecorator */
    private $subject;

    /** @var AccessTokenRequestValidator */
    private $validator;

    public function setUp(): void
    {
        $this->validator = $this->createMock(AccessTokenRequestValidator::class);
        $this->subject = new AccessTokenRequestValidatorDecorator($this->validator, LineItemServiceInterface::SCOPE_LINE_ITEM);
    }

    public function testValidateWithNoErrorOnAccessTokenValidator(): void
    {
        $this->setupAccessTokenValidator(false);

        $this->subject->validate($this->createMock(ServerRequestInterface::class));
    }

    public function testValidateWithUnAllowedScoreWillThrowException(): void
    {
        $this->setupAccessTokenValidator(false, null, ['invalidScope']);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Only allowed for scope ' . LineItemServiceInterface::SCOPE_LINE_ITEM);

        $this->subject->validate($this->createMock(ServerRequestInterface::class));
    }

    public function testValidateWithErrorOnAccessTokenValidator(): void
    {
        $error = 'Not allowed at all';

        $this->setupAccessTokenValidator(true, $error);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage($error);

        $this->subject->validate($this->createMock(ServerRequestInterface::class));
    }

    private function setupAccessTokenValidator(bool $hasError, ?string $error = null, array $scopes = []): void
    {
        $validatorResult = $this->createMock(AccessTokenRequestValidationResult::class);

        $validatorResult
            ->expects($this->once())
            ->method('hasError')
            ->willReturn($hasError);

        if ($error !== null) {
            $validatorResult
                ->expects($this->once())
                ->method('getError')
                ->willReturn($error);
        }

        $validatorResult
            ->method('getScopes')
            ->willReturn(empty($scopes) ? [LineItemServiceInterface::SCOPE_LINE_ITEM] : $scopes);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->willReturn($validatorResult);
    }
}
