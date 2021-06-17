# Library interfaces

> Depending on the AGS services you want to provide as a platform, you have to provide your own implementations of the following interfaces.

## Table of contents

- [Line item repository interface](#line-item-repository-interface)
- [Score repository interface](#score-repository-interface)
- [Result repository interface](#result-repository-interface)


### Line item repository interface

**Required by**:
- [line item service](../../src/Service/LineItem/Server/Handler)
- [score service](../../src/Service/Score/Server/Handler)
- [result service](../../src/Service/Result/Server/Handler)

In order to manage your line items, you need to provide an implementation of the [LineItemRepositoryInterface](../../src/Repository/LineItemRepositoryInterface.php).

For example:

```php
<?php

use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemInterface;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemCollectionInterface;
use OAT\Library\Lti1p3Ags\Repository\LineItemRepositoryInterface;

$lineItemRepository = new class implements LineItemRepositoryInterface
{
    public function find(string $lineItemIdentifier): ?LineItemInterface
    {
        // TODO: Implement find() method.
    }
    
    public function findCollection(
        ?string $resourceIdentifier = null,
        ?string $resourceLinkIdentifier = null,
        ?string $tag = null,
        ?int $limit = null,
        ?int $offset = null
    ): LineItemCollectionInterface {
        // TODO: Implement findCollection() method.
    }
    
    public function save(LineItemInterface $lineItem): LineItemInterface
    {
        // TODO: Implement save() method.
    }
    
    public function delete(string $lineItemIdentifier): void
    {
        // TODO: Implement delete() method.
    }
};
```
**Notes**: 
- the `save()` method will be called by the LTI service handlers for **both line items creation and update**, up to you to handle line items identifier generation the way you want in case of creation
- a simple implementation example can be found in the [library tests](../../tests/Traits/AgsDomainTestingTrait.php)

### Score repository interface

**Required by**:
- [score service](../../src/Service/Score/Server/Handler)

In order to manage your scores, you need to provide an implementation of the [ScoreRepositoryInterface](../../src/Repository/ScoreRepositoryInterface.php).

For example:

```php
<?php

use OAT\Library\Lti1p3Ags\Model\Score\ScoreInterface;
use OAT\Library\Lti1p3Ags\Repository\ScoreRepositoryInterface;

$scoreRepository = new class implements ScoreRepositoryInterface
{
    public function save(ScoreInterface $score): ScoreInterface
    {
        // TODO: Implement save() method.
    }
};
```
**Notes**:
- the interface does not provide methods to find scores, up to you to add dedicated methods in your repository implementation to handle this the way you want
- a simple implementation example can be found in the [library tests](../../tests/Traits/AgsDomainTestingTrait.php)

### Result repository interface

**Required by**:
- [result service](../../src/Service/Result/Server/Handler)

In order to be able to manage your results, you need to provide an implementation of the [ResultRepositoryInterface](../../src/Repository/ResultRepositoryInterface.php).

For example:

```php
<?php

use OAT\Library\Lti1p3Ags\Model\Result\ResultInterface;
use OAT\Library\Lti1p3Ags\Model\Result\ResultCollectionInterface;
use OAT\Library\Lti1p3Ags\Repository\ResultRepositoryInterface;

$resultRepository = new class implements ResultRepositoryInterface
{
    public function findCollectionByLineItemIdentifier(
        string $lineItemIdentifier,
        ?int $limit = null,
        ?int $offset = null
    ): ResultCollectionInterface {
        // TODO: Implement findCollectionByLineItemIdentifier() method.
    }
    
    public function findByLineItemIdentifierAndUserIdentifier(
        string $lineItemIdentifier,
        string $userIdentifier
    ): ?ResultInterface {
        // TODO: Implement findByLineItemIdentifierAndUserIdentifier() method.
    }
};
```
**Notes**:
- the interface does not provide methods to persist results, p to you to add dedicated methods in your repository implementation to handle this the way you want
- as per [AGS specifications](https://www.imsglobal.org/spec/lti-ags/v2p0#container-request-filters-0)  , the `findByLineItemIdentifierAndUserIdentifier()` method must return the most relevant result for a given line item and  user
- a simple implementation example can be found in the [library tests](../../tests/Traits/AgsDomainTestingTrait.php)
