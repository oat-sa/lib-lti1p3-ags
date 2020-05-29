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

namespace OAT\Library\Lti1p3Ags\Tests\Unit\Traits;

use DateTimeInterface;
use OAT\Library\Lti1p3Ags\Factory\ScoreFactory;
use OAT\Library\Lti1p3Ags\Model\Score;
use OAT\Library\Lti1p3Core\Platform\Platform;
use OAT\Library\Lti1p3Core\Platform\PlatformInterface;
use OAT\Library\Lti1p3Core\Registration\Registration;
use OAT\Library\Lti1p3Core\Security\Key\KeyChainInterface;
use OAT\Library\Lti1p3Core\Tool\Tool;
use OAT\Library\Lti1p3Core\Tool\ToolInterface;

trait DomainTestingTrait
{
    private function createTestRegistration(
        string $identifier = 'registrationIdentifier',
        string $clientId = 'registrationClientId',
        PlatformInterface $platform = null,
        ToolInterface $tool = null,
        array $deploymentIds = ['deploymentIdentifier'],
        KeyChainInterface $platformKeyChain = null,
        KeyChainInterface $toolKeyChain = null,
        string $platformJwksUrl = null,
        string $toolJwksUrl = null
    ): Registration {
        return new Registration(
            $identifier,
            $clientId,
            $platform ?? $this->createTestPlatform(),
            $tool ?? $this->createTestTool(),
            $deploymentIds,
            $platformKeyChain ,
            $toolKeyChain ,
            $platformJwksUrl,
            $toolJwksUrl
        );
    }

    private function createTestPlatform(
        string $identifier = 'platformIdentifier',
        string $name = 'platformName',
        string $audience = 'platformAudience',
        string $oidcAuthenticationUrl = 'http://platform.com/oidc-auth',
        string $oauth2AccessTokenUrl = 'http://platform.com/access-token'
    ): Platform {
        return new Platform($identifier, $name, $audience, $oidcAuthenticationUrl, $oauth2AccessTokenUrl);
    }

    private function createTestTool(
        string $identifier = 'toolIdentifier',
        string $name = 'toolName',
        string $audience = 'platformAudience',
        string $oidcLoginInitiationUrl = 'http://tool.com/oidc-init',
        string $launchUrl = 'http://tool.com/launch',
        string $deepLaunchUrl = 'http://tool.com/deep-launch'
    ): Tool {
        return new Tool($identifier, $name, $audience, $oidcLoginInitiationUrl, $launchUrl, $deepLaunchUrl);
    }

    private function createScore(
        string $userId = 'userId',
        string $contextId = 'contextId',
        string $lineItemId = 'lineItemId',
        ?string $id = null,
        ?float $scoreGiven = 0.2,
        ?float $scoreMaximum = 0.3,
        ?string $comment = null,
        ?DateTimeInterface $timestamp = null,
        string $activityProgressStatus = Score::ACTIVITY_PROGRESS_STATUS_INITIALIZED,
        string $gradingProgressStatus = Score::GRADING_PROGRESS_STATUS_NOT_READY
    ): Score {
        return (new ScoreFactory())->create(
            $userId,
            $contextId,
            $lineItemId,
            $id,
            $scoreGiven,
            $scoreMaximum,
            $comment,
            $timestamp,
            $activityProgressStatus,
            $gradingProgressStatus
        );
    }
}
