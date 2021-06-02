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

namespace OAT\Library\Lti1p3Ags\Voter;

use OAT\Library\Lti1p3Ags\Service\LineItem\LineItemServiceInterface;
use OAT\Library\Lti1p3Ags\Service\Result\ResultServiceInterface;
use OAT\Library\Lti1p3Ags\Service\Score\ScoreServiceInterface;

class ScopePermissionVoter implements ScopePermissionVoterInterface
{
    public static function canReadLineItem(array $scopes = []): bool
    {
        return in_array(LineItemServiceInterface::AUTHORIZATION_SCOPE_LINE_ITEM_READ_ONLY, $scopes)
            || in_array(LineItemServiceInterface::AUTHORIZATION_SCOPE_LINE_ITEM, $scopes);
    }

    public static function canWriteLineItem(array $scopes = []): bool
    {
        return in_array(LineItemServiceInterface::AUTHORIZATION_SCOPE_LINE_ITEM, $scopes);
    }

    public static function canWriteScore(array $scopes = []): bool
    {
        return in_array(ScoreServiceInterface::AUTHORIZATION_SCOPE_SCORE, $scopes);
    }

    public static function canReadResult(array $scopes = []): bool
    {
        return in_array(ResultServiceInterface::AUTHORIZATION_SCOPE_RESULT_READ_ONLY, $scopes);
    }
}
