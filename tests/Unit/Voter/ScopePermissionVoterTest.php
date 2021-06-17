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

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Voter;

use OAT\Library\Lti1p3Ags\Service\LineItem\LineItemServiceInterface;
use OAT\Library\Lti1p3Ags\Service\Result\ResultServiceInterface;
use OAT\Library\Lti1p3Ags\Service\Score\ScoreServiceInterface;
use OAT\Library\Lti1p3Ags\Voter\ScopePermissionVoter;
use PHPUnit\Framework\TestCase;

class ScopePermissionVoterTest extends TestCase
{
    public function testVoter(): void
    {
        $this->assertEquals(
            [
                'canReadLineItem' => true,
                'canWriteLineItem' => true,
                'canWriteScore' => true,
                'canReadResult' => true,
            ],
            ScopePermissionVoter::getPermissions(
                [
                    LineItemServiceInterface::AUTHORIZATION_SCOPE_LINE_ITEM_READ_ONLY,
                    LineItemServiceInterface::AUTHORIZATION_SCOPE_LINE_ITEM,
                    ScoreServiceInterface::AUTHORIZATION_SCOPE_SCORE,
                    ResultServiceInterface::AUTHORIZATION_SCOPE_RESULT_READ_ONLY,
                ]
            )
        );

        $this->assertEquals(
            [
                'canReadLineItem' => true,
                'canWriteLineItem' => false,
                'canWriteScore' => true,
                'canReadResult' => true,
            ],
            ScopePermissionVoter::getPermissions(
                [
                    LineItemServiceInterface::AUTHORIZATION_SCOPE_LINE_ITEM_READ_ONLY,
                    ScoreServiceInterface::AUTHORIZATION_SCOPE_SCORE,
                    ResultServiceInterface::AUTHORIZATION_SCOPE_RESULT_READ_ONLY,
                ]
            )
        );

        $this->assertEquals(
            [
                'canReadLineItem' => false,
                'canWriteLineItem' => false,
                'canWriteScore' => true,
                'canReadResult' => true,
            ],
            ScopePermissionVoter::getPermissions(
                [
                    ScoreServiceInterface::AUTHORIZATION_SCOPE_SCORE,
                    ResultServiceInterface::AUTHORIZATION_SCOPE_RESULT_READ_ONLY,
                ]
            )
        );

        $this->assertEquals(
            [
                'canReadLineItem' => true,
                'canWriteLineItem' => true,
                'canWriteScore' => false,
                'canReadResult' => true,
            ],
            ScopePermissionVoter::getPermissions(
                [
                    LineItemServiceInterface::AUTHORIZATION_SCOPE_LINE_ITEM_READ_ONLY,
                    LineItemServiceInterface::AUTHORIZATION_SCOPE_LINE_ITEM,
                    ResultServiceInterface::AUTHORIZATION_SCOPE_RESULT_READ_ONLY,
                ]
            )
        );

        $this->assertEquals(
            [
                'canReadLineItem' => true,
                'canWriteLineItem' => true,
                'canWriteScore' => true,
                'canReadResult' => false,
            ],
            ScopePermissionVoter::getPermissions(
                [
                    LineItemServiceInterface::AUTHORIZATION_SCOPE_LINE_ITEM_READ_ONLY,
                    LineItemServiceInterface::AUTHORIZATION_SCOPE_LINE_ITEM,
                    ScoreServiceInterface::AUTHORIZATION_SCOPE_SCORE,
                ]
            )
        );
    }
}
