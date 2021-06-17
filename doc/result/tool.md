# AGS Tool - Result service client

> How to use the [ResultServiceClient](../../src/Service/Result/Client/ResultServiceClient.php) to perform authenticated AGS results retrieval as a tool.

## Table of contents

- [Features](#features)
- [Usage](#usage)

## Features

This library provides a [ResultServiceClient](../../src/Service/Result/Client/ResultServiceClient.php)  (based on the [core LtiServiceClient](https://github.com/oat-sa/lib-lti1p3-core/blob/master/doc/service/service-client.md)) that allow results retrieval as a tool.

## Usage

To list results:

```php
<?php

use OAT\Library\Lti1p3Ags\Service\Result\Client\ResultServiceClient;
use OAT\Library\Lti1p3Core\Registration\RegistrationRepositoryInterface;

// Related registration
/** @var RegistrationRepositoryInterface $registrationRepository */
$registration = $registrationRepository->find(...);

$resultClient = new ResultServiceClient();

$resultContainer = $resultClient->listResults(
    $registration,                                      // [required] as the tool, it will call the platform of this registration
    'https://example.com/ags/contexts/1/lineitems/1',   // [required] AGS line item url to list the results from
    'user_id',                                          // [optional] user identifier (default none)
    1,                                                  // [optional] pagination limit to return (default none)
    1                                                   // [optional] pagination offset (default none)
);

// Iterate on returned results
foreach ($resultContainer->getResults() as $result) {
    echo $result->getResultScore();
}

// Results container relation link (to know presence of next or not)
echo $resultContainer->getRelationLinkUrl();

if ($resultContainer->hasNext()) {
    // Handle retrieval of the next results
}
```
