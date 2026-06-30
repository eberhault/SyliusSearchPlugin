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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Request\ProductRequest;

use Elastica\Query;
use Elastica\Query\BoolQuery;
use MonsieurBiz\SyliusSearchPlugin\Repository\ProductAttributeRepositoryInterface;
use MonsieurBiz\SyliusSearchPlugin\Repository\ProductOptionRepositoryInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\AggregationBuilder;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\Search as SearchRequest;
use Sylius\Component\Registry\ServiceRegistryInterface;

final class Search extends SearchRequest
{
    public function __construct(
        ServiceRegistryInterface $documentableRegistry,
        private readonly AggregationBuilder $aggregationBuilder,
        string $documentType,
        iterable $queryFilters,
        iterable $postFilters,
        iterable $sorters,
        iterable $functionScores,
        private readonly ProductAttributeRepositoryInterface $productAttributeRepository,
        private readonly ProductOptionRepositoryInterface $productOptionRepository
    ) {
        parent::__construct(
            $documentableRegistry,
            $aggregationBuilder,
            $documentType,
            $queryFilters,
            $postFilters,
            $sorters,
            $functionScores
        );
    }

    protected function addAggregations(Query $query, BoolQuery $postFilter): void
    {
        /** @var array $mustParam */
        $mustParam = $postFilter->hasParam(key: 'must') ? $postFilter->getParam(key: 'must') : [];

        $aggregations = $this->aggregationBuilder->buildAggregations(
            aggregations: [
                'main_taxon',
                'price',
                $this->productAttributeRepository->findIsSearchableOrFilterable(),
                $this->productOptionRepository->findIsSearchableOrFilterable(),
            ],
            filters: $mustParam,
        );

        foreach ($aggregations as $aggregation) {
            $query->addAggregation(agg: $aggregation);
        }
    }
}
