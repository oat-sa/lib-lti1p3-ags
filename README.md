# <img src="doc/images/logo/logo.png" width="40" height="40"> [TAO](https://www.taotesting.com/) - LTI 1.3 AGS Library

[![Latest Version](https://img.shields.io/github/tag/oat-sa/lib-lti1p3-ags.svg?style=flat&label=release)](https://github.com/oat-sa/lib-lti1p3-ags/tags)
[![License GPL2](http://img.shields.io/badge/licence-GPL%202.0-blue.svg)](http://www.gnu.org/licenses/gpl-2.0.html)
[![Build Status](https://github.com/oat-sa/lib-lti1p3-ags/actions/workflows/build.yaml/badge.svg?branch=main)](https://github.com/oat-sa/lib-lti1p3-ags/actions)
[![Tests Coverage Status](https://coveralls.io/repos/github/oat-sa/lib-lti1p3-ags/badge.svg?branch=main)](https://coveralls.io/github/oat-sa/lib-lti1p3-ags?branch=main)
[![Psalm Level Status](https://shepherd.dev/github/oat-sa/lib-lti1p3-ags/level.svg)](https://shepherd.dev/github/oat-sa/lib-lti1p3-ags)
[![Packagist Downloads](http://img.shields.io/packagist/dt/oat-sa/lib-lti1p3-ags.svg)](https://packagist.org/packages/oat-sa/lib-lti1p3-ags)
[![IMS Certified](https://img.shields.io/badge/IMS-certified-brightgreen)](https://site.imsglobal.org/certifications/open-assessment-technologies-sa/tao-lti-13-devkit)

> [IMS certified](https://site.imsglobal.org/certifications/open-assessment-technologies-sa/tao-lti-13-devkit) PHP library for [LTI 1.3 Assignment and Grade Services](https://www.imsglobal.org/spec/lti-ags/v2p0) implementations as [platforms and / or as tools](http://www.imsglobal.org/spec/lti/v1p3/#platforms-and-tools), based on [LTI 1.3 Core library](https://github.com/oat-sa/lib-lti1p3-core).

# Table of contents

- [TAO LTI 1.3 PHP framework](#tao-lti-13-php-framework)
- [IMS](#ims)
- [Installation](#installation)
- [Documentation](#documentation)
- [Tests](#tests)

## TAO LTI 1.3 PHP framework

This library is part of the [TAO LTI 1.3 PHP framework](https://oat-sa.github.io/doc-lti1p3/).

## IMS

You can find below [IMS](https://www.imsglobal.org/) related information.

### Related certifications

- [LTI 1.3 advantage complete](https://site.imsglobal.org/certifications/open-assessment-technologies-sa/tao-lti-13-devkit)

### Related specifications

- [IMS LTI Assignment and Grade Services](https://www.imsglobal.org/spec/lti-ags/v2p0)
- [IMS LTI 1.3 Core](http://www.imsglobal.org/spec/lti/v1p3)
- [IMS Security](https://www.imsglobal.org/spec/security/v1p0)

## Installation

```console
$ composer require oat-sa/lib-lti1p3-ags
```

## Documentation

You can then find below the library documentation, presented by topics.

### Quick start

- how to [configure the underlying LTI 1.3 Core library](https://github.com/oat-sa/lib-lti1p3-core#quick-start)
- how to [implement the AGS library interfaces](doc/quickstart/interfaces.md)
- how to [check AGS scopes permissions](doc/quickstart/voter.md)

### Line Item

- how to [use the AGS library for line items as a platform](doc/lineitem/platform.md)
- how to [use the AGS library for line items as a tool](doc/lineitem/tool.md)

### Result

- how to [use the AGS library for results as a platform](doc/result/platform.md)
- how to [use the AGS library for results as a tool](doc/result/tool.md)

### Score

- how to [use the AGS library for scores as a platform](doc/score/platform.md)
- how to [use the AGS library for scores as a tool](doc/score/tool.md)

## Tests

To run tests:

```console
$ vendor/bin/phpunit
```
**Note**: see [phpunit.xml.dist](phpunit.xml.dist) for available test suites.
