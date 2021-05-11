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

namespace OAT\Library\Lti1p3Ags\Url\Extractor;

use InvalidArgumentException;

class UrlParameterExtractor implements UrlParameterExtractorInterface
{
    /**
     * @throw InvalidArgumentException
     */
    public function extract(string $url, string $splitPattern = self::DEFAULT_SPLIT_PATTERN): UrlParameterExtractorResult
    {
        $parsedUrl = parse_url($url);
        if (false === $parsedUrl) {
            throw new InvalidArgumentException(sprintf('Malformed url %s', $url));
        }
        $path = trim($parsedUrl['path'], '/');

        if (false === strpos($path, $splitPattern)){
            return new UrlParameterExtractorResult();
        }

        $parts = explode($splitPattern, $path);

        $contextParts = explode('/', $parts[0]);
        $lineItemParts = explode('/', trim($parts[1] ?? '', '/'));

        return new UrlParameterExtractorResult(
            end($contextParts),
            current($lineItemParts)
        );
    }
}
