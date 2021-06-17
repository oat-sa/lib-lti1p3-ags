# AGS Platform - Result service server

> How to use the [ResultServiceServerRequestHandler](../../src/Service/Result/Server/Handler/ResultServiceServerRequestHandler.php) (with the core [LtiServiceServer](https://github.com/oat-sa/lib-lti1p3-core/blob/master/src/Service/Server/LtiServiceServer.php)) to provide authenticated AGS endpoint for results retrieval as a platform.

## Table of contents

- [Features](#features)
- [Usage](#usage)

## Features

This library provides a [ResultServiceServerRequestHandler](../../src/Service/Result/Server/Handler/ResultServiceServerRequestHandler.php) ready to be use with the core [LtiServiceServer](https://github.com/oat-sa/lib-lti1p3-core/blob/master/src/Service/Server/LtiServiceServer.php) to expose results to tools, as a platform.

- it accepts a [PSR7 ServerRequestInterface](https://www.php-fig.org/psr/psr-7/#321-psrhttpmessageserverrequestinterface),
- leverages the [required IMS LTI 1.3 service authentication](https://www.imsglobal.org/spec/security/v1p0/#securing_web_services),
- and returns a [PSR7 ResponseInterface](https://www.php-fig.org/psr/psr-7/#33-psrhttpmessageresponseinterface) containing the result list representation.

It allows you to provide a result service endpoint as specified in [AGS openapi documentation](https://www.imsglobal.org/spec/lti-ags/v2p0/openapi/#/default).

## Usage

First, you need to provide:
- a [LineItemRepositoryInterface](../../src/Repository/LineItemRepositoryInterface.php) implementation, in charge to handle line items, as explained [in the interfaces library documentation](../quickstart/interfaces.md)
- a [ResultRepositoryInterface](../../src/Repository/ResultRepositoryInterface.php) implementation, in charge to handle results, as explained [in the interfaces library documentation](../quickstart/interfaces.md)

Then:
- you can construct the [ResultServiceServerRequestHandler](../../src/Service/Result/Server/Handler/ResultServiceServerRequestHandler.php) (constructed with your [LineItemRepositoryInterface](../../src/Repository/LineItemRepositoryInterface.php) and [ResultRepositoryInterface](../../src/Repository/ResultRepositoryInterface.php) implementations)
- to finally expose it to requests using the core [LtiServiceServer](https://github.com/oat-sa/lib-lti1p3-core/blob/master/src/Service/Server/LtiServiceServer.php) (constructed with the [RequestAccessTokenValidator](https://github.com/oat-sa/lib-lti1p3-core/blob/master/src/Security/OAuth2/Validator/RequestAccessTokenValidator.php), from core library)

```php
<?php

use OAT\Library\Lti1p3Ags\Repository\LineItemRepositoryInterface;
use OAT\Library\Lti1p3Ags\Repository\ResultRepositoryInterface;
use OAT\Library\Lti1p3Ags\Service\Result\Server\Handler\ResultServiceServerRequestHandler;
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

/** @var ResultRepositoryInterface $scoreRepository */
$resultRepository = ...

$validator = new RequestAccessTokenValidator($registrationRepository);

$handler = new ResultServiceServerRequestHandler($lineItemRepository, $resultRepository);

$server = new LtiServiceServer($validator, $handler);

// Generates an authenticated response containing the result list representation
$response = $server->handle($request);
```

