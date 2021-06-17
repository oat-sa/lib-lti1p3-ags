# AGS Tool - Line Item service client

> How to use the [LineItemServiceClient](../../src/Service/LineItem/Client/LineItemServiceClient.php) to perform authenticated AGS line item service calls as a tool.

## Table of contents

- [Features](#features)
- [Usage](#usage)
    - [Get a line item](#get-a-line-item)
    - [List line items](#list-line-items)
    - [Create a line item](#create-a-line-item)
    - [Update a line item](#update-a-line-item)
    - [Delete a line item](#delete-a-line-item)

## Features

This library provides a [LineItemServiceClient](../../src/Service/LineItem/Client/LineItemServiceClient.php) (based on the [core LtiServiceClient](https://github.com/oat-sa/lib-lti1p3-core/blob/master/doc/service/service-client.md)) that allow line items management as a tool on AGS service endpoints exposed by a platform.

## Usage

You can find below how to use the [LineItemServiceClient](../../src/Service/LineItem/Client/LineItemServiceClient.php) methods to manage line items.

### Get a line item

To get a line item:

```php
<?php

use OAT\Library\Lti1p3Ags\Service\LineItem\Client\LineItemServiceClient;
use OAT\Library\Lti1p3Ags\Service\LineItem\LineItemServiceInterface;
use OAT\Library\Lti1p3Core\Registration\RegistrationRepositoryInterface;

// Related registration
/** @var RegistrationRepositoryInterface $registrationRepository */
$registration = $registrationRepository->find(...);

$lineItemClient = new LineItemServiceClient();

$lineItem = $lineItemClient->getLineItem(
    $registration,                                             // [required] as the tool, it will call the platform of this registration
    'https://example.com/ags/contexts/1/lineitems/1',          // [required] AGS line item url
    [LineItemServiceInterface::AUTHORIZATION_SCOPE_LINE_ITEM]  // [optional] scopes to use (default both read only and regular line item scopes)
);

// Line item identifier
echo $lineItem->getIdentifier();

// Line item max score
echo $lineItem->getScoreMaximum();
```

### List line items

To list line items:

```php
<?php

use OAT\Library\Lti1p3Ags\Service\LineItem\Client\LineItemServiceClient;
use OAT\Library\Lti1p3Ags\Service\LineItem\LineItemServiceInterface;
use OAT\Library\Lti1p3Core\Registration\RegistrationRepositoryInterface;

// Related registration
/** @var RegistrationRepositoryInterface $registrationRepository */
$registration = $registrationRepository->find(...);

$lineItemClient = new LineItemServiceClient();

$lineItemContainer = $lineItemClient->listLineItems(
    $registration,                                             // [required] as the tool, it will call the platform of this registration
    'https://example.com/ags/contexts/1/lineitems',            // [required] AGS line item container url
    'resource_id',                                             // [optional] line item resource identifier filter (default none)
    'resource_link_id',                                        // [optional] line item resource link identifier filter (default none)
    'tag',                                                     // [optional] line item tag filter (default none)
    1,                                                         // [optional] pagination limit to return (default none)
    1,                                                         // [optional] pagination offset (default none)
    [LineItemServiceInterface::AUTHORIZATION_SCOPE_LINE_ITEM]  // [optional] scopes to use (default both read only and regular line item scopes)
);

// Iterate on returned line items
foreach ($lineItemContainer->getLineItems() as $lineItem) {
    echo $lineItem->getIdentifier();
}

// Line item container relation link (to know presence of next or not)
echo $lineItemContainer->getRelationLinkUrl();

if ($lineItemContainer->hasNext()) {
    // Handle retrieval of the next line items
}
...
```

### Create a line item

To create a line item:

```php
<?php

use OAT\Library\Lti1p3Ags\Model\LineItem\LineItem;
use OAT\Library\Lti1p3Ags\Service\LineItem\Client\LineItemServiceClient;
use OAT\Library\Lti1p3Core\Registration\RegistrationRepositoryInterface;

// Related registration
/** @var RegistrationRepositoryInterface $registrationRepository */
$registration = $registrationRepository->find(...);

$lineItemClient = new LineItemServiceClient();

$lineItem = new LineItem(...);

$createdLineItem = $lineItemClient->createLineItem(
    $registration,                                             // [required] as the tool, it will call the platform of this registration
    $lineItem,                                                 // [required] AGS line item to create
    'https://example.com/ags/contexts/1/lineitems'             // [required] AGS line item container url
);

// Created line item identifier (given by the platform)
echo $createdLineItem->getIdentifier();
```

**Note**: you can also use the [LineItemFactory](../../src/Factory/LineItem/LineItemFactory.php) to help your line item creation.

### Update a line item

To update a line item:

```php
<?php

use OAT\Library\Lti1p3Ags\Service\LineItem\Client\LineItemServiceClient;
use OAT\Library\Lti1p3Core\Registration\RegistrationRepositoryInterface;

// Related registration
/** @var RegistrationRepositoryInterface $registrationRepository */
$registration = $registrationRepository->find(...);

$lineItemClient = new LineItemServiceClient();

$lineItem = $lineItemClient->getLineItem(...);

$lineItem->setScoreMaximum(100);

$updatedLineItem = $lineItemClient->updateLineItem(
    $registration,                                             // [required] as the tool, it will call the platform of this registration
    $lineItem                                                  // [required] AGS line item to update
);

// Updated line item max score (given by the platform)
echo $updatedLineItem->getScoreMaximum();
```

### Delete a line item

To delete a line item:

```php
<?php

use OAT\Library\Lti1p3Ags\Service\LineItem\Client\LineItemServiceClient;
use OAT\Library\Lti1p3Core\Registration\RegistrationRepositoryInterface;

// Related registration
/** @var RegistrationRepositoryInterface $registrationRepository */
$registration = $registrationRepository->find(...);

$lineItemClient = new LineItemServiceClient();

$isDeleted = $lineItemClient->deleteLineItem(
    $registration,                                             // [required] as the tool, it will call the platform of this registration
    'https://example.com/ags/contexts/1/lineitems/1'           // [required] AGS line item url
);

// Check line item deletion success
if ($isDeleted) {
    // Deletion success
}
```
