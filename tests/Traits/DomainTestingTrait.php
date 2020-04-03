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

namespace OAT\Library\Lti1p3Ags\Tests\Traits;

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
            $platformKeyChain,
            $toolKeyChain,
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
        string $oidcLoginInitiationUrl = 'http://tool.com/oidc-init',
        string $launchUrl = 'http://tool.com/launch',
        string $deepLaunchUrl = 'http://tool.com/deep-launch'
    ): Tool {
        return new Tool($identifier, $name, $oidcLoginInitiationUrl, $launchUrl, $deepLaunchUrl);
    }
}
