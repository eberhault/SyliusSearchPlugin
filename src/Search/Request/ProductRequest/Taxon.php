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
use MonsieurBiz\SyliusSearchPlugin\Repository\ProductAttributeRepositoryInterface;
use MonsieurBiz\SyliusSearchPlugin\Repository\ProductOptionRepositoryInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\AggregationBuilder;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\Taxon as TaxonRequest;
use RuntimeException;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;

final class Taxon extends TaxonRequest
{
    public function __construct(
        ServiceRegistryInterface $documentableRegistry,
        ChannelContextInterface $channelContext,
        private AggregationBuilder $aggregationBuilder,
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
            $channelContext,
            $aggregationBuilder,
            $documentType,
            $queryFilters,
            $postFilters,
            $sorters,
            $functionScores
        );
    }

    protected function addAggregations(Query $query, Query\BoolQuery $postFilter): void
    {
        if (!$this->configuration instanceof RequestConfiguration) {
            throw new RuntimeException(message: 'Missing request configuration');
        }

        /** @var array $mustParam */
        $mustParam = $postFilter->hasParam(key: 'must') ? $postFilter->getParam(key: 'must') : [];

        $aggregations = $this->aggregationBuilder->buildAggregations(
            aggregations: [
                ['taxons' => $this->configuration->getTaxon()],
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
