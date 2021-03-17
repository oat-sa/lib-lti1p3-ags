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

namespace OAT\Library\Lti1p3Ags\Parser;

class RequestUrlParserResult
{
    /** @var string|null */
    private $contextIdentifier;

    /** @var string|null */
    private $lineItemIdentifier;

    public function __construct(?string $contextIdentifier = null, ?string $lineItemIdentifier = null)
    {
        $this->contextIdentifier = $contextIdentifier;
        $this->lineItemIdentifier = $lineItemIdentifier;
    }

    public function getContextIdentifier(): ?string
    {
        return $this->contextIdentifier;
    }

    public function getLineItemIdentifier(): ?string
    {
        return $this->lineItemIdentifier;
    }

    public function hasContextIdentifier(): bool
    {
        return !empty($this->contextIdentifier);
    }

    public function hasLineItemIdentifier(): bool
    {
        return !empty($this->lineItemIdentifier);
    }
}
