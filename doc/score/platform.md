# AGS Platform - Score service server

> How to use the [ScoreServiceServerRequestHandler](../../src/Service/Score/Server/Handler/ScoreServiceServerRequestHandler.php) (with the core [LtiServiceServer](https://github.com/oat-sa/lib-lti1p3-core/blob/master/src/Service/Server/LtiServiceServer.php)) to provide authenticated AGS endpoint for score publications as a platform.

## Table of contents

- [Features](#features)
- [Usage](#usage)

## Features

This library provides a [ScoreServiceServerRequestHandler](../../src/Service/Score/Server/Handler/ScoreServiceServerRequestHandler.php) ready to be use with the core [LtiServiceServer](https://github.com/oat-sa/lib-lti1p3-core/blob/master/src/Service/Server/LtiServiceServer.php) to accept scores publications as a platform.

- it accepts a [PSR7 ServerRequestInterface](https://www.php-fig.org/psr/psr-7/#321-psrhttpmessageserverrequestinterface),
- leverages the [required IMS LTI 1.3 service authentication](https://www.imsglobal.org/spec/security/v1p0/#securing_web_services),
- and returns a [PSR7 ResponseInterface](https://www.php-fig.org/psr/psr-7/#33-psrhttpmessageresponseinterface) containing the score publication response

It allows you to provide a score service endpoint as specified in [AGS openapi documentation](https://www.imsglobal.org/spec/lti-ags/v2p0/openapi/#/default).

## Usage

First, you need to provide:
- a [LineItemRepositoryInterface](../../src/Repository/LineItemRepositoryInterface.php) implementation, in charge to handle line items, as explained [in the interfaces library documentation](../quickstart/interfaces.md)
- a [ScoreRepositoryInterface](../../src/Repository/ScoreRepositoryInterface.php) implementation, in charge to handle scores, as explained [in the interfaces library documentation](../quickstart/interfaces.md)

Then:
- you can construct the [ScoreServiceServerRequestHandler](../../src/Service/Score/Server/Handler/ScoreServiceServerRequestHandler.php) (constructed with your [LineItemRepositoryInterface](../../src/Repository/LineItemRepositoryInterface.php) and [ScoreRepositoryInterface](../../src/Repository/ScoreRepositoryInterface.php) implementations)
- to finally expose it to requests using the core [LtiServiceServer](https://github.com/oat-sa/lib-lti1p3-core/blob/master/src/Service/Server/LtiServiceServer.php) (constructed with the [RequestAccessTokenValidator](https://github.com/oat-sa/lib-lti1p3-core/blob/master/src/Security/OAuth2/Validator/RequestAccessTokenValidator.php), from core library)

```php
<?php

use OAT\Library\Lti1p3Ags\Repository\LineItemRepositoryInterface;
use OAT\Library\Lti1p3Ags\Repository\ScoreRepositoryInterface;
use OAT\Library\Lti1p3Ags\Service\Score\Server\Handler\ScoreServiceServerRequestHandler;
use OAT\Library\Lti1p3Core\Registration\RegistrationRepositoryInterface;
use OAT\Library\Lti1p3Core\Security\OAuth2\Validator\RequestAccessTokenValidator;
use OAT\Library\Lti1p3Core\Service\Server\LtiServiceServer;
use Psr\Http\Message\ServerRequestInterface;

/** @var ServerRequestInterface $request */
$request = ...

/** @var RegistrationRepositoryInterface $registrationRepository */
$registrationRepository = ...

/** @var LineItemRepositoryInterface $lineItemRepository */
$lineItemRepository = ...

/** @var ScoreRepositoryInterface $scoreRepository */
$scoreRepository = ...

$validator = new RequestAccessTokenValidator($registrationRepository);

$handler = new ScoreServiceServerRequestHandler($lineItemRepository, $scoreRepository);

$server = new LtiServiceServer($validator, $handler);

// Generates an authenticated response containing the score publication result
$response = $server->handle($request);
```
