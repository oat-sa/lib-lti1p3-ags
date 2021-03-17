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

namespace OAT\Library\Lti1p3Ags\Service\LineItem;

interface LineItemServiceInterface
{
    public const AUTHORIZATION_SCOPE_LINE_ITEM = 'https://purl.imsglobal.org/spec/lti-ags/scope/lineitem';
    public const AUTHORIZATION_SCOPE_LINE_ITEM_READ_ONLY = 'https://purl.imsglobal.org/spec/lti-ags/scope/lineitem.readonly';

    public const CONTENT_TYPE_LINE_ITEM = 'application/vnd.ims.lis.v2.lineitem+json';
    public const CONTENT_TYPE_LINE_ITEM_CONTAINER = 'application/vnd.ims.lis.v2.lineitemcontainer+json';

    public const HEADER_LINK = 'Link';
}
