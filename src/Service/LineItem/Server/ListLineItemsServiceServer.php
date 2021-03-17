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

namespace OAT\Library\Lti1p3Ags\Service\LineItem\Server;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @see https://www.imsglobal.org/spec/lti-ags/v2p0#example-getting-all-line-items-for-a-given-container
 */
class ListLineItemsServiceServer extends AbstractLineItemServiceServer
{
    protected function getSupportedHttpMethods(): array
    {
        return [
            'GET'
        ];
    }

    protected function getSupportedContentType(): string
    {
        return static::CONTENT_TYPE_LINE_ITEM_CONTAINER;
    }

    protected function getSupportedScopes(): array
    {
        return [
            static::AUTHORIZATION_SCOPE_LINE_ITEM,
            static::AUTHORIZATION_SCOPE_LINE_ITEM_READ_ONLY
        ];
    }

    protected function processRequest(ServerRequestInterface $request): ResponseInterface
    {
        $parsingResult = $this->parser->parse($request);

        parse_str($request->getUri()->getQuery(), $parameters);

        $lineItemCollection = $this->repository->findBy(
            $parsingResult->getContextIdentifier(),
            $parameters['resource_link_id'] ?? null,
            $parameters['resource_id'] ?? null,
            $parameters['tag'] ?? null,
            array_key_exists('limit', $parameters) ? intval($parameters['limit']) : null,
            array_key_exists('offset', $parameters) ? intval($parameters['offset']) : null
        );

        $responseBody = $this->serializer->serializeCollection($lineItemCollection);
        $responseHeaders = [
            'Content-Type' => static::CONTENT_TYPE_LINE_ITEM_CONTAINER,
            'Content-Length' => strlen($responseBody),
        ];

        if ($lineItemCollection->hasNext()) {
            $responseHeaders['Link'] = 'todo';
        }

        return $this->factory->createResponse(200, null, $responseHeaders, $responseBody);
    }
}
