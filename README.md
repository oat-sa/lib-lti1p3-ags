# LTI 1.3 AGS Library

> PHP library for [LTI 1.3 Assignment and Grade Services](https://www.imsglobal.org/spec/lti-ags/v2p0) implementations as platforms and / or as tools.

# Table of contents

- [Specifications](#specifications)
- [Installation](#installation)
- [Concepts](#concepts)
- [Tests](#tests)

## Installation

```console
$ composer require oat-sa/lib-lti1p3-ags
```

## Specifications

- [IMS LTI Assignment and Grade Services](https://www.imsglobal.org/spec/lti-ags/v2p0)
- [IMS Security](https://www.imsglobal.org/spec/security/v1p0)

## Concepts

You can find below the implementations of the main concepts of the [LTI Assignment and Grade Services](https://www.imsglobal.org/spec/lti-ags/v2p0) specification.

###  Models

- [LineItem](src/Model/LineItem.php)
- [Score](src/Model/Score.php)

### Service

#### Tool

##### ScoreServiceClient
- Code: [ScoreServiceClient](src/Service/Client/ScoreServiceClient.php)
- Documentation: [Score publish service documentation ](https://www.imsglobal.org/spec/lti-ags/v2p0#score-publish-service)
- Openapi POST score contract: [openapi](https://www.imsglobal.org/spec/lti-ags/v2p0/openapi/#/default/Scores.POST)

## Tests

To run tests:

```console
$ vendor/bin/phpunit
```
**Note**: see [phpunit.xml.dist](phpunit.xml.dist) for available test suites.
