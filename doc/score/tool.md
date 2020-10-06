# AGS Tool - Score service client

> How to use the [ScoreServiceClient](../../src/Service/Score/Client/ScoreServiceClient.php) to perform authenticated AGS score service calls as a tool.

## Table of contents

- [Features](#features)
- [Usage](#usage)

## Features

This library provides a [ScoreServiceClient](../../src/Service/Score/Client/ScoreServiceClient.php) (based on the [core service client](https://github.com/oat-sa/lib-lti1p3-core/blob/master/doc/service/service-client.md)) that allow publishing AGS scores to a platform.

You can use:
- `publishForPayload()` to [publish a score](https://www.imsglobal.org/spec/lti-ags/v2p0#score-publish-service) for a received LTI message payload (will use AGS claim)
- `publish()` to [publish a score](https://www.imsglobal.org/spec/lti-ags/v2p0#score-publish-service) to a given line item url, with given scopes

## Usage

To publish a score:

```php
<?php

use OAT\Library\Lti1p3Ags\Factory\Score\ScoreFactory;
use OAT\Library\Lti1p3Ags\Service\Score\ScoreServiceInterface;
use OAT\Library\Lti1p3Ags\Service\Score\Client\ScoreServiceClient;
use OAT\Library\Lti1p3Core\Message\Payload\LtiMessagePayloadInterface;
use OAT\Library\Lti1p3Core\Registration\RegistrationRepositoryInterface;

// Related registration
/** @var RegistrationRepositoryInterface $registrationRepository */
$registration = $registrationRepository->find(...);

// Related LTI 1.3 message payload
/** @var LtiMessagePayloadInterface $payload */
$payload  = ...;

// Build the score service client
$scoreServiceClient = new ScoreServiceClient();

// Build score to publish
$score = (new ScoreFactory())->create(...);

$response = $scoreServiceClient->publishForPayload(
    $registration, // [required] as the tool, it will call the platform of this registration
    $payload,      // [required] from the LTI message payload containing the AGS claim (got at LTI launch)
    $score         // [required] with a given score
);

// or you also can directly publish to an given URL (avoid claim construction)
$response = $scoreServiceClient->publish(
    $registration,                                      // [required] as the tool, it will call the platform of this registration
    $score,                                             // [required] with a given score    
    'https://example.com/2344/lineitems/1234/scores',   // [required] to a given score service url
    [ScoreServiceInterface::AUTHORIZATION_SCOPE_SCORE]  // [optional] with given scopes
);
```
