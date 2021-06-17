# AGS Tool - Score service client

> How to use the [ScoreServiceClient](../../src/Service/Score/Client/ScoreServiceClient.php) to perform authenticated AGS scores publications as a tool.

## Table of contents

- [Features](#features)
- [Usage](#usage)

## Features

This library provides a [ScoreServiceClient](../../src/Service/Score/Client/ScoreServiceClient.php) (based on the [core LtiServiceClient](https://github.com/oat-sa/lib-lti1p3-core/blob/master/doc/service/service-client.md)) that allow scores publications as a tool.

## Usage

To publish a score:

```php
<?php

use OAT\Library\Lti1p3Ags\Model\Score\Score;
use OAT\Library\Lti1p3Ags\Service\Score\Client\ScoreServiceClient;
use OAT\Library\Lti1p3Core\Registration\RegistrationRepositoryInterface;

// Related registration
/** @var RegistrationRepositoryInterface $registrationRepository */
$registration = $registrationRepository->find(...);

$scoreClient = new ScoreServiceClient();

$score = new Score(...);

$isPublished = $scoreClient->publishScore(
    $registration,                                      // [required] as the tool, it will call the platform of this registration
    $score,                                             // [required] AGS score to publish
    'https://example.com/ags/contexts/1/lineitems/1'    // [required] AGS line item url to publish the score to
);

// Check score publication success
if ($isPublished) {
    // Publication success
}
```

**Note**: you can use the [ScoreFactory](../../src/Factory/Score/ScoreFactory.php) to ease your score creation.
