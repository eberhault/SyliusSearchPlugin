<?php

/*
 * This file is part of Monsieur Biz' Search plugin for Sylius.
 *
 * (c) Monsieur Biz <sylius@monsieurbiz.com>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Search\Request;

use Elastica\Aggregation\AbstractAggregation;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\Aggregation\AggregationBuilderInterface;
use RuntimeException;

readonly class AggregationBuilder
{
    /**
     * @param iterable<AggregationBuilderInterface> $aggregationBuilders
     */
    public function __construct(
        private iterable $aggregationBuilders,
    ) {
    }

    public function buildAggregations(array $aggregations, array $filters): array
    {
        $buckets = [];

        foreach ($aggregations as $aggregation) {
            $aggregationQuery = $this->buildAggregation(aggregation: $aggregation, filters: $filters);

            if (false === $aggregationQuery) {
                continue;
            }

            $buckets[] = $aggregationQuery;
        }

        return array_filter(array: $buckets);
    }

    private function buildAggregation(string|array $aggregation, array $filters): AbstractAggregation|bool
    {
        // Don't build aggregation if the given one is empty
        if (empty($aggregation)) {
            return false;
        }

        foreach ($this->aggregationBuilders as $aggregationBuilder) {
            $aggregationQuery = $aggregationBuilder->build(aggregation: $aggregation, filters: $filters);

            if (null !== $aggregationQuery) {
                return $aggregationQuery;
            }
        }

        throw new RuntimeException(message: 'Aggregation cannot be build'); // it's throw an exception if we have not filtreable attribute
    }
}
