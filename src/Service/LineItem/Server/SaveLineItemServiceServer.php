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
 * @see https://www.imsglobal.org/spec/lti-ags/v2p0#creating-a-new-line-item
 * @see https://www.imsglobal.org/spec/lti-ags/v2p0#updating-a-line-item
 */
class SaveLineItemServiceServer extends AbstractLineItemServiceServer
{
    protected function getSupportedHttpMethods(): array
    {
        return [
            'POST',
            'PUT'
        ];
    }

    protected function getSupportedContentType(): string
    {
        return static::CONTENT_TYPE_LINE_ITEM;
    }

    protected function getSupportedScopes(): array
    {
        return [
            static::AUTHORIZATION_SCOPE_LINE_ITEM
        ];
    }

    protected function processRequest(ServerRequestInterface $request): ResponseInterface
    {
        $lineItem = $this->serializer->deserialize((string)$request->getBody());

        $parsingResult = $this->parser->parse($request);

        if ($parsingResult->hasContextIdentifier()) {
            $lineItem->setContextIdentifier($parsingResult->getContextIdentifier());
        }

        $lineItem = $this->repository->save($lineItem);

        $responseBody = $this->serializer->serialize($lineItem);
        $responseHeaders = [
            'Content-Type' => static::CONTENT_TYPE_LINE_ITEM,
            'Content-Length' => strlen($responseBody),
        ];

        return $this->factory->createResponse(201, null, $responseHeaders, $responseBody);
    }
}
