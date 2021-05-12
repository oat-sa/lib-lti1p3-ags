<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace OAT\Library\Lti1p3Ags\Tests\Traits;

use DateTimeInterface;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItem;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemCollection;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemCollectionInterface;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemInterface;
use OAT\Library\Lti1p3Ags\Repository\LineItemRepositoryInterface;
use OAT\Library\Lti1p3Core\Util\Collection\Collection;
use OAT\Library\Lti1p3Core\Util\Collection\CollectionInterface;
use OAT\Library\Lti1p3Core\Util\Generator\IdGenerator;
use OAT\Library\Lti1p3Core\Util\Generator\IdGeneratorInterface;

trait AgsDomainTestingTrait
{
    private function createTestLineItem(
        float $scoreMaximum = 100,
        string $label = 'lineItemLabel',
        ?string $identifier = 'lineItemIdentifier',
        ?string $contextIdentifier = 'contextIdentifier',
        ?string $resourceIdentifier = 'resourceIdentifier',
        ?string $resourceLinkIdentifier = 'resourceLinkIdentifier',
        ?string $tag = 'tag',
        ?DateTimeInterface $startDateTime = null,
        ?DateTimeInterface $endDateTime = null,
        array $additionalProperties = []
    ): LineItemInterface {
        return new LineItem(
            $scoreMaximum,
            $label,
            $identifier,
            $contextIdentifier,
            $resourceIdentifier,
            $resourceLinkIdentifier,
            $tag,
            $startDateTime,
            $endDateTime,
            $additionalProperties
        );
    }

    private function createTestLineItemRepository(
        array $lineItems = [],
        IdGeneratorInterface $generator = null
    ): LineItemRepositoryInterface{

        $lineItems = !empty($lineItems) ? $lineItems : [$this->createTestLineItem()];
        $generator = $generator ?? new IdGenerator();

        return new class ($lineItems, $generator) implements LineItemRepositoryInterface
        {
            /** @var LineItemInterface[]|CollectionInterface */
            private $lineItems;

            /** @var IdGeneratorInterface */
            private $generator;

            /** @var LineItemInterface[] $lineItems */
            public function __construct(array $lineItems, IdGeneratorInterface $generator)
            {
                $this->lineItems = new Collection();
                $this->generator = $generator;

                foreach ($lineItems as $lineItem) {
                    $this->lineItems->set($lineItem->getIdentifier(), $lineItem);
                }
            }

            public function find(string $lineItemIdentifier): ?LineItemInterface
            {
                return $this->lineItems->get($lineItemIdentifier);
            }

            public function findBy(
                ?string $resourceIdentifier = null,
                ?string $resourceLinkIdentifier = null,
                ?string $tag = null,
                ?int $limit = null,
                ?int $offset = null
            ): LineItemCollectionInterface {
               $foundLineItems = [];

               foreach ($this->lineItems as $lineItem) {
                   $found = true;

                   if (null !== $resourceIdentifier) {
                       $found = $found && $lineItem->getResourceIdentifier() === $resourceIdentifier;
                   }

                   if (null !== $resourceLinkIdentifier) {
                       $found = $found && $lineItem->getResourceLinkIdentifier() === $resourceLinkIdentifier;
                   }

                   if (null !== $tag) {
                       $found = $found && $lineItem->getTag() === $tag;
                   }

                   if ($found) {
                       $foundLineItems[] = $lineItem;
                   }
               }

                return new LineItemCollection(
                    array_slice($foundLineItems, $offset ?: 0, $limit),
                    ($limit ?: 0) >= $this->lineItems->count()
                );
            }

            public function save(LineItemInterface $lineItem): LineItemInterface
            {
                if (null === $lineItem->getIdentifier()) {
                    $lineItem->setIdentifier($this->generator->generate());
                }

                $this->lineItems->set($lineItem->getIdentifier(), $lineItem);
            }

            public function delete(string $lineItemIdentifier): void
            {
                $lineItem = $this->find($lineItemIdentifier);

                if (null !== $lineItem) {
                    $this->lineItems->remove($lineItem->getIdentifier());
                }
            }
        };
    }
}
