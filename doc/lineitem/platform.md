# AGS Platform - Line Item service server

> How to use the [line item service server handlers](../../src/Service/LineItem/Server/Handler) (with the core [LtiServiceServer](https://github.com/oat-sa/lib-lti1p3-core/blob/master/src/Service/Server/LtiServiceServer.php)) to provide authenticated AGS endpoints for line item management as a platform.

## Table of contents

- [Features](#features)
- [Usage](#usage)
    - [Get line item service endpoint](#get-line-item-service-endpoint)
    - [List line item service endpoint](#list-line-items-service-endpoint)
    - [Create line item service endpoint](#create-line-item-service-endpoint)
    - [Update line item service endpoint](#update-line-item-service-endpoint)
    - [Delete line item service endpoint](#delete-line-item-service-endpoint)

## Features

This library provides a set of [line item service server handlers](../../src/Service/LineItem/Server/Handler) ready to be use with the core [LtiServiceServer](https://github.com/oat-sa/lib-lti1p3-core/blob/master/src/Service/Server/LtiServiceServer.php) to handle line item management requests.

- they accept a [PSR7 ServerRequestInterface](https://www.php-fig.org/psr/psr-7/#321-psrhttpmessageserverrequestinterface),
- leverages the [required IMS LTI 1.3 service authentication](https://www.imsglobal.org/spec/security/v1p0/#securing_web_services),
- and returns a [PSR7 ResponseInterface](https://www.php-fig.org/psr/psr-7/#33-psrhttpmessageresponseinterface) containing the related line item service response

They allow you to provide line item management service endpoints as specified in [AGS openapi documentation](https://www.imsglobal.org/spec/lti-ags/v2p0/openapi/#/default).

## Usage

You can find below how to use each AGS service server request handlers to provide line item service endpoints.

### Get line item service endpoint

First, you need to provide a [LineItemRepositoryInterface](../../src/Repository/LineItemRepositoryInterface.php) implementation, in charge to handle line items, as explained [in the interfaces library documentation](../quickstart/interfaces.md). 


Then:
- you can construct the [GetLineItemServiceServerRequestHandler](../../src/Service/LineItem/Server/Handler/GetLineItemServiceServerRequestHandler.php) (constructed with your [LineItemRepositoryInterface](../../src/Repository/LineItemRepositoryInterface.php) implementation)
- to finally expose it to requests using the core [LtiServiceServer](https://github.com/oat-sa/lib-lti1p3-core/blob/master/src/Service/Server/LtiServiceServer.php) (constructed with the [RequestAccessTokenValidator](https://github.com/oat-sa/lib-lti1p3-core/blob/master/src/Security/OAuth2/Validator/RequestAccessTokenValidator.php), from core library)

```php
<?php

use OAT\Library\Lti1p3Ags\Repository\LineItemRepositoryInterface;
use OAT\Library\Lti1p3Ags\Service\LineItem\Server\Handler\GetLineItemServiceServerRequestHandler;
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

$validator = new RequestAccessTokenValidator($registrationRepository);

$handler = new GetLineItemServiceServerRequestHandler($lineItemRepository);

$server = new LtiServiceServer($validator, $handler);

// Generates an authenticated response containing the requested line item representation
$response = $server->handle($request);
```

### List line items service endpoint

First, you need to provide a [LineItemRepositoryInterface](../../src/Repository/LineItemRepositoryInterface.php) implementation, in charge to handle line items, as explained [in the interfaces library documentation](../quickstart/interfaces.md).


Then:
- you can construct the [ListLineItemsServiceServerRequestHandler](../../src/Service/LineItem/Server/Handler/ListLineItemsServiceServerRequestHandler.php) (constructed with your [LineItemRepositoryInterface](../../src/Repository/LineItemRepositoryInterface.php) implementation)
- to finally expose it to requests using the core [LtiServiceServer](https://github.com/oat-sa/lib-lti1p3-core/blob/master/src/Service/Server/LtiServiceServer.php) (constructed with the [RequestAccessTokenValidator](https://github.com/oat-sa/lib-lti1p3-core/blob/master/src/Security/OAuth2/Validator/RequestAccessTokenValidator.php), from core library)

```php
<?php

use OAT\Library\Lti1p3Ags\Repository\LineItemRepositoryInterface;
use OAT\Library\Lti1p3Ags\Service\LineItem\Server\Handler\ListLineItemsServiceServerRequestHandler;
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

$validator = new RequestAccessTokenValidator($registrationRepository);

$handler = new ListLineItemsServiceServerRequestHandler($lineItemRepository);

$server = new LtiServiceServer($validator, $handler);

// Generates an authenticated response containing the requested line item list representation
$response = $server->handle($request);
```

### Create line item service endpoint

First, you need to provide a [LineItemRepositoryInterface](../../src/Repository/LineItemRepositoryInterface.php) implementation, in charge to handle line items, as explained [in the interfaces library documentation](../quickstart/interfaces.md).


Then:
- you can construct the [CreateLineItemServiceServerRequestHandler](../../src/Service/LineItem/Server/Handler/CreateLineItemServiceServerRequestHandler.php) (constructed with your [LineItemRepositoryInterface](../../src/Repository/LineItemRepositoryInterface.php) implementation)
- to finally expose it to requests using the core [LtiServiceServer](https://github.com/oat-sa/lib-lti1p3-core/blob/master/src/Service/Server/LtiServiceServer.php) (constructed with the [RequestAccessTokenValidator](https://github.com/oat-sa/lib-lti1p3-core/blob/master/src/Security/OAuth2/Validator/RequestAccessTokenValidator.php), from core library)

```php
<?php

use OAT\Library\Lti1p3Ags\Repository\LineItemRepositoryInterface;
use OAT\Library\Lti1p3Ags\Service\LineItem\Server\Handler\CreateLineItemServiceServerRequestHandler;
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

$validator = new RequestAccessTokenValidator($registrationRepository);

$handler = new CreateLineItemServiceServerRequestHandler($lineItemRepository);

$server = new LtiServiceServer($validator, $handler);

// Generates an authenticated response containing the line item creation response
$response = $server->handle($request);
```

### Update line item service endpoint

First, you need to provide a [LineItemRepositoryInterface](../../src/Repository/LineItemRepositoryInterface.php) implementation, in charge to handle line items, as explained [in the interfaces library documentation](../quickstart/interfaces.md).


Then:
- you can construct the [UpdateLineItemServiceServerRequestHandler](../../src/Service/LineItem/Server/Handler/UpdateLineItemServiceServerRequestHandler.php) (constructed with your [LineItemRepositoryInterface](../../src/Repository/LineItemRepositoryInterface.php) implementation)
- to finally expose it to requests using the core [LtiServiceServer](https://github.com/oat-sa/lib-lti1p3-core/blob/master/src/Service/Server/LtiServiceServer.php) (constructed with the [RequestAccessTokenValidator](https://github.com/oat-sa/lib-lti1p3-core/blob/master/src/Security/OAuth2/Validator/RequestAccessTokenValidator.php), from core library)

```php
<?php

use OAT\Library\Lti1p3Ags\Repository\LineItemRepositoryInterface;
use OAT\Library\Lti1p3Ags\Service\LineItem\Server\Handler\UpdateLineItemServiceServerRequestHandler;
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

$validator = new RequestAccessTokenValidator($registrationRepository);

$handler = new UpdateLineItemServiceServerRequestHandler($lineItemRepository);

$server = new LtiServiceServer($validator, $handler);

// Generates an authenticated response containing the line item update response
$response = $server->handle($request);
```

### Delete line item service endpoint

First, you need to provide a [LineItemRepositoryInterface](../../src/Repository/LineItemRepositoryInterface.php) implementation, in charge to handle line items, as explained [in the interfaces library documentation](../quickstart/interfaces.md).


Then:
- you can construct the [DeleteLineItemServiceServerRequestHandler](../../src/Service/LineItem/Server/Handler/DeleteLineItemServiceServerRequestHandler.php) (constructed with your [LineItemRepositoryInterface](../../src/Repository/LineItemRepositoryInterface.php) implementation)
- to finally expose it to requests using the core [LtiServiceServer](https://github.com/oat-sa/lib-lti1p3-core/blob/master/src/Service/Server/LtiServiceServer.php) (constructed with the [RequestAccessTokenValidator](https://github.com/oat-sa/lib-lti1p3-core/blob/master/src/Security/OAuth2/Validator/RequestAccessTokenValidator.php), from core library)

```php
<?php

use OAT\Library\Lti1p3Ags\Repository\LineItemRepositoryInterface;
use OAT\Library\Lti1p3Ags\Service\LineItem\Server\Handler\DeleteLineItemServiceServerRequestHandler;
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

$validator = new RequestAccessTokenValidator($registrationRepository);

$handler = new DeleteLineItemServiceServerRequestHandler($lineItemRepository);

$server = new LtiServiceServer($validator, $handler);

// Generates an authenticated response containing the line item deletion response
$response = $server->handle($request);
```
