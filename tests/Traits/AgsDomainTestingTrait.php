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
 * Copyright (c) 2021 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace OAT\Library\Lti1p3Ags\Tests\Traits;

use DateTimeInterface;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItem;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemCollection;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemCollectionInterface;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemInterface;
use OAT\Library\Lti1p3Ags\Model\LineItem\LineItemSubmissionReviewInterface;
use OAT\Library\Lti1p3Ags\Model\Result\Result;
use OAT\Library\Lti1p3Ags\Model\Result\ResultCollection;
use OAT\Library\Lti1p3Ags\Model\Result\ResultCollectionInterface;
use OAT\Library\Lti1p3Ags\Model\Result\ResultInterface;
use OAT\Library\Lti1p3Ags\Model\Score\Score;
use OAT\Library\Lti1p3Ags\Model\Score\ScoreInterface;
use OAT\Library\Lti1p3Ags\Repository\LineItemRepositoryInterface;
use OAT\Library\Lti1p3Ags\Repository\ResultRepositoryInterface;
use OAT\Library\Lti1p3Ags\Repository\ScoreRepositoryInterface;
use OAT\Library\Lti1p3Core\Util\Collection\Collection;
use OAT\Library\Lti1p3Core\Util\Collection\CollectionInterface;
use OAT\Library\Lti1p3Core\Util\Generator\IdGenerator;
use OAT\Library\Lti1p3Core\Util\Generator\IdGeneratorInterface;

trait AgsDomainTestingTrait
{
    private function createTestLineItem(
        float $scoreMaximum = 100,
        string $label = 'lineItemLabel',
        string $identifier = 'https://example.com/line-items/lineItemIdentifier',
        string $resourceIdentifier = 'lineItemResourceIdentifier',
        string $resourceLinkIdentifier = 'lineItemResourceLinkIdentifier',
        string $tag = 'lineItemTag',
        ?DateTimeInterface $startDateTime = null,
        ?DateTimeInterface $endDateTime = null,
        ?LineItemSubmissionReviewInterface $submissionReview = null,
        array $additionalProperties = ['key' => 'value']
    ): LineItemInterface {
        return new LineItem(
            $scoreMaximum,
            $label,
            $identifier,
            $resourceIdentifier,
            $resourceLinkIdentifier,
            $tag,
            $startDateTime,
            $endDateTime,
            $submissionReview,
            $additionalProperties
        );
    }

    private function createTestLineItemCollection(
        array $lineItems = [],
        bool $hasNext = false
    ): LineItemCollectionInterface {
        $lineItems = !empty($lineItems)
            ? $lineItems
            : [
                $this->createTestLineItem(),
                $this->createTestLineItem(
                    110,
                    'lineItemLabel2',
                    'https://example.com/line-items/lineItemIdentifier2',
                    'lineItemResourceIdentifier2',
                    'lineItemResourceLinkIdentifier2',
                    'lineItemTag2'
                ),
                $this->createTestLineItem(
                    120,
                    'lineItemLabel3',
                    'https://example.com/line-items/lineItemIdentifier3',
                    'lineItemResourceIdentifier3',
                    'lineItemResourceLinkIdentifier3',
                    'lineItemTag3'
                ),
            ];

        return new LineItemCollection($lineItems, $hasNext);
    }

    private function createTestLineItemRepository(
        array $lineItems = [],
        ?IdGeneratorInterface $generator = null
    ): LineItemRepositoryInterface {

        $lineItems = !empty($lineItems) ? $lineItems : $this->createTestLineItemCollection()->all();
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

            public function findCollection(
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
                    $limit && (($limit + $offset) < sizeof($foundLineItems))
                );
            }

            public function save(LineItemInterface $lineItem): LineItemInterface
            {
                if (null === $lineItem->getIdentifier()) {
                    $lineItem->setIdentifier($this->generator->generate());
                }

                $this->lineItems->set($lineItem->getIdentifier(), $lineItem);

                return $lineItem;
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

    private function createTestScore(
        string $userIdentifier = 'scoreUserIdentifier',
        string $activityProgressStatus = ScoreInterface::ACTIVITY_PROGRESS_STATUS_INITIALIZED,
        string $gradingProgressStatus = ScoreInterface::GRADING_PROGRESS_STATUS_NOT_READY,
        string $lineItemIdentifier = 'https://example.com/line-items/lineItemIdentifier',
        float $scoreGiven = 10,
        float $scoreMaximum = 100,
        string $comment = 'scoreComment',
        ?DateTimeInterface $timestamp = null,
        array $additionalProperties = ['key' => 'value']
    ): ScoreInterface {
        return new Score(
            $userIdentifier,
            $activityProgressStatus,
            $gradingProgressStatus,
            $lineItemIdentifier,
            $scoreGiven,
            $scoreMaximum,
            $comment,
            $timestamp,
            $additionalProperties
        );
    }

    private function createTestScoreRepository(array $scores = []): ScoreRepositoryInterface
    {
        return new class ($scores) implements ScoreRepositoryInterface
        {
            /** @var ScoreInterface[] */
            private $scores;

            public function __construct(array $scores)
            {
                $this->scores = [];

                foreach ($scores as $score) {
                    $this->save($score);
                }
            }

            public function save(ScoreInterface $score): ScoreInterface
            {
                $this->scores[$score->getLineItemIdentifier()][] = $score;

                return $score;
            }

            public function findByLineItemIdentifier(string $lineItemIdentifier): array
            {
                return $this->scores[$lineItemIdentifier] ?? [];
            }
        };
    }

    private function createTestResult(
        string $userIdentifier = 'resultUserIdentifier',
        string $lineItemIdentifier = 'https://example.com/line-items/lineItemIdentifier',
        string $identifier = 'https://example.com/line-items/lineItemIdentifier/results/resultIdentifier',
        float $resultScore = 10,
        float $resultMaximum = 100,
        string $comment = 'resultComment',
        array $additionalProperties = ['key' => 'value']
    ): ResultInterface {
        return new Result(
            $userIdentifier,
            $lineItemIdentifier,
            $identifier,
            $resultScore,
            $resultMaximum,
            $comment,
            $additionalProperties
        );
    }

    private function createTestResultCollection(
        array $results = [],
        bool $hasNext = false
    ): ResultCollectionInterface {
        $results = !empty($results)
            ? $results
            : [
                $this->createTestResult(),
                $this->createTestResult(
                    'resultUserIdentifier',
                    'https://example.com/line-items/lineItemIdentifier',
                    'https://example.com/line-items/lineItemIdentifier/results/resultIdentifier2',
                    20
                ),
                $this->createTestResult(
                    'resultUserIdentifier',
                    'https://example.com/line-items/lineItemIdentifier',
                    'https://example.com/line-items/lineItemIdentifier/results/resultIdentifier3',
                    30
                ),
            ];

        return new ResultCollection($results, $hasNext);
    }

    private function createTestResultRepository(array $results = []): ResultRepositoryInterface
    {
        $results = !empty($results) ? $results : $this->createTestResultCollection()->all();

        return new class ($results) implements ResultRepositoryInterface
        {
            /** @var ResultInterface[] */
            private $results;

            public function __construct(array $results)
            {
                $this->results = [];

                foreach ($results as $result) {
                    $this->save($result);
                }
            }

            public function save(ResultInterface $result): ResultInterface
            {
                $this->results[$result->getLineItemIdentifier()][] = $result;

                return $result;
            }

            public function findCollectionByLineItemIdentifier(
                string $lineItemIdentifier,
                ?int $limit = null,
                ?int $offset = null
            ): ResultCollectionInterface {
                $lineItemResults = $this->results[$lineItemIdentifier] ?? [];

                return new ResultCollection(
                    array_slice($lineItemResults, $offset ?: 0, $limit),
                    $limit && ($limit + $offset) < sizeof($lineItemResults)
                );
            }

            public function findByLineItemIdentifierAndUserIdentifier(
                string $lineItemIdentifier,
                string $userIdentifier
            ): ?ResultInterface {
                $foundResults = [];

                foreach ($this->results[$lineItemIdentifier] ?? [] as $result) {
                    if ($result->getUserIdentifier() === $userIdentifier) {
                        $foundResults[] = $result;
                    }
                }

                return !empty($foundResults) ? end($foundResults) : null;
            }
        };
    }
}
