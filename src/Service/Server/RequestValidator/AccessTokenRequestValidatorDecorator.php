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

namespace OAT\Library\Lti1p3Ags\Service\Server\RequestValidator;

use OAT\Library\Lti1p3Core\Service\Server\Validator\AccessTokenRequestValidator;
use Psr\Http\Message\ServerRequestInterface;

class AccessTokenRequestValidatorDecorator implements RequestValidatorInterface
{
    public const SCOPE_LINE_ITEM = 'https://purl.imsglobal.org/spec/lti-ags/scope/lineitem';

    /** @var AccessTokenRequestValidator */
    private $validator;

    /** @var string */
    private $allowedScope;

    public function __construct(AccessTokenRequestValidator $validator, string $allowedScope = null)
    {
        $this->validator = $validator;
        $this->allowedScope = $allowedScope;
    }

    /**
     * @throws RequestValidatorException
     */
    public function validate(ServerRequestInterface $request): void
    {
        $validationResult = $this->validator->validate($request);

        if ($validationResult->hasError()) {
            throw new RequestValidatorException($validationResult->getError(), 401);
        }

        if ($this->allowedScope !== null && !in_array($this->allowedScope, $validationResult->getScopes(), true)) {
            throw new RequestValidatorException(
                sprintf('Only allowed for scope %s', $this->allowedScope),
                403
            );
        }
    }
}
