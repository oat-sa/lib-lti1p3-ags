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

namespace OAT\Library\Lti1p3Ags\Service\LineItem\Query;

class LineItemQuery
{
    /** @var string */
    private $contextId;

    /** @var string|null */
    private $lineItemId;

    /** @var integer */
    private $page;

    /** @var integer */
    private $limit;

    public function __construct(string $contextId, string $lineItemId = null, int $page = null, int $limit = null)
    {
        $this->contextId = $contextId;
        $this->lineItemId = $lineItemId;
        $this->page = $page;
        $this->limit = $limit;
    }

    public function getContextId(): string
    {
        return $this->contextId;
    }

    public function getLineItemId(): ?string
    {
        return $this->lineItemId;
    }

    public function hasLineItemId(): bool
    {
        return $this->lineItemId !== null;
    }

    public function getPage(): ?int
    {
        return $this->page;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }
}