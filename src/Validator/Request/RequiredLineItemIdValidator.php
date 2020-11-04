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

namespace OAT\Library\Lti1p3Ags\Validator\Request;

use InvalidArgumentException;
use OAT\Library\Lti1p3Ags\Parser\UrlParser;
use OAT\Library\Lti1p3Ags\Parser\UrlParserInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequiredLineItemIdValidator implements RequestValidatorInterface
{
    private $parser;

    public function __construct(UrlParserInterface $parser = null)
    {
        $this->parser = $parser ?? new UrlParser();
    }

    public function validate(ServerRequestInterface $request): void
    {
        $data = $this->parser->parse($request);

        if ($data['lineItemId'] === null) {
            throw new InvalidArgumentException('Url path must contain lineItemId as third uri path part.');
        }
    }
}
