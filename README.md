# LTI 1.3 AGS Library

> PHP library for [LTI 1.3 Assignment and Grade Services](https://www.imsglobal.org/spec/lti-ags/v2p0) implementations as platforms and / or as tools.

# Table of contents

- [Specifications](#specifications)
- [Installation](#installation)
- [Tutorials](#tutorials)
- [Tests](#tests)

## Specifications

- [IMS LTI Assignment and Grade Services](https://www.imsglobal.org/spec/lti-ags/v2p0)
- [IMS Security](https://www.imsglobal.org/spec/security/v1p0)

## Installation

```console
$ composer require oat-sa/lib-lti1p3-ags
```

## Tutorials

You can then find below usage tutorials, presented by topics.

### Configuration

- how to [configure the underlying LTI 1.3 Core library](https://github.com/oat-sa/lib-lti1p3-core#quick-start).

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
