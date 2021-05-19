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

namespace OAT\Library\Lti1p3Ags\Url\Builder;

use InvalidArgumentException;

class UrlBuilder implements UrlBuilderInterface
{
    /**
     * @throw InvalidArgumentException
     */
    public function build(
        string $url,
        ?string $additionalUrlPathSuffix = null,
        array $additionalUrlQueryParameters = []
    ): string {
        $parsedUrl = parse_url($url);

        if (false === $parsedUrl) {
            throw new InvalidArgumentException(sprintf('Malformed url %s', $url));
        }

        parse_str($parsedUrl['query'] ?? '', $parsedQueryParameters);
        $queryString = http_build_query(array_merge($parsedQueryParameters, $additionalUrlQueryParameters));

        $username = $parsedUrl['user'] ?? '';
        $password = isset($parsedUrl['pass']) ? ':' . $parsedUrl['pass']  : '';

        return sprintf(
            '%s%s%s%s%s%s%s%s',
            isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '',
            $username !== '' ? $username . $password . '@' : '',
            $parsedUrl['host'],
            isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '',
            $parsedUrl['path'],
            !empty($additionalUrlPathSuffix) ? '/' . $additionalUrlPathSuffix : '',
            !empty($queryString) ? '?' . $queryString : '',
            isset($parsedUrl['fragment']) ? '#' . $parsedUrl['fragment'] : ''
        );
    }
}
