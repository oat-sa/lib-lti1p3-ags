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

namespace OAT\Library\Lti1p3Ags\Extractor;

use Psr\Http\Message\ServerRequestInterface;

class RequestUriParameterExtractor implements RequestUriParameterExtractorInterface
{
    private const SPLIT_PATTERN = '/lineitems';

    /** @var string */
    private $splitPattern;

    public function __construct(string $splitPattern = self::SPLIT_PATTERN)
    {
        $this->splitPattern = $splitPattern;
    }

    public function extract(ServerRequestInterface $request): RequestUriParameterExtractorResult
    {
        $path = trim($request->getUri()->getPath(), '/');

        if (false === strpos($path, $this->splitPattern)){
            return new RequestUriParameterExtractorResult();
        }

        $parts = explode($this->splitPattern, $path);

        $contextParts = explode('/', $parts[0]);
        $lineItemParts = explode('/', trim($parts[1] ?? '', '/'));

        return new RequestUriParameterExtractorResult(
            end($contextParts),
            current($lineItemParts)
        );
    }
}