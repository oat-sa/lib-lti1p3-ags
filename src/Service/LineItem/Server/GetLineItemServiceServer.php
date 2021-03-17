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
 * @see https://www.imsglobal.org/spec/lti-ags/v2p0#example-getting-a-single-line-item
 */
class GetLineItemServiceServer extends AbstractLineItemServiceServer
{
    protected function getSupportedHttpMethods(): array
    {
        return [
            'GET'
        ];
    }

    protected function getSupportedContentType(): string
    {
        return static::CONTENT_TYPE_LINE_ITEM;
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

        if (!$parsingResult->hasLineItemIdentifier()) {
            $message = 'Missing line item identifier';
            $this->logger->error($message);

            return $this->factory->createResponse(400, null, [], $message);
        }

        $lineItem = $this->repository->find($parsingResult->getLineItemIdentifier());

        if (null === $lineItem) {
            $message = sprintf('Cannot find line item with id %s', $parsingResult->getLineItemIdentifier());
            $this->logger->error($message);

            return $this->factory->createResponse(404, null, [], $message);
        }

        $responseBody = $this->serializer->serialize($lineItem);
        $responseHeaders = [
            'Content-Type' => static::CONTENT_TYPE_LINE_ITEM,
            'Content-Length' => strlen($responseBody),
        ];

        return $this->factory->createResponse(200, null, $responseHeaders, $responseBody);
    }
}
