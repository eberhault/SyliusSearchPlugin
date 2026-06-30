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

use Elastica\Query;
use Elastica\QueryBuilder;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\FunctionScore\FunctionScoreInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\PostFilter\PostFilterInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\QueryFilter\QueryFilterInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\Sorting\SorterInterface;
use RuntimeException;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;

class Taxon implements TaxonInterface
{
    protected ?RequestConfiguration $configuration;

    /**
     * @param ServiceRegistryInterface $documentableRegistry
     * @param ChannelContextInterface $channelContext
     * @param AggregationBuilder $aggregationBuilder
     * @param string $documentType
     * @param iterable<QueryFilterInterface> $queryFilters
     * @param iterable<PostFilterInterface> $postFilters
     * @param iterable<SorterInterface> $sorters
     * @param iterable<FunctionScoreInterface> $functionScores
     */
    public function __construct(
        private readonly ServiceRegistryInterface $documentableRegistry,
        private readonly ChannelContextInterface $channelContext,
        private readonly AggregationBuilder $aggregationBuilder,
        private readonly string $documentType,
        private readonly iterable $queryFilters,
        private readonly iterable $postFilters,
        private readonly iterable $sorters,
        private readonly iterable $functionScores
    ) {
    }

    public function getType(): string
    {
        return RequestInterface::TAXON_TYPE;
    }

    public function getDocumentable(): DocumentableInterface
    {
        return $this->documentableRegistry->get(
            identifier: sprintf('search.documentable.%s', $this->documentType),
        );
    }

    public function getQuery(): Query
    {
        if (!$this->configuration instanceof RequestConfiguration) {
            throw new RuntimeException(message: 'Configuration is not set');
        }

        $queryBuilder = new QueryBuilder();

        $boolQuery = $queryBuilder->query()->bool();

        foreach ($this->queryFilters as $queryFilter) {
            $queryFilter->apply(
                boolQuery: $boolQuery,
                requestConfiguration: $this->configuration,
            );
        }

        $query = Query::create(query: $boolQuery);

        $postFilter = new Query\BoolQuery();

        foreach ($this->postFilters as $postFilterApplier) {
            $postFilterApplier->apply(boolQuery: $postFilter, requestConfiguration: $this->configuration);
        }

        $query->setPostFilter(filter: $postFilter);

        $this->addAggregations(query: $query, postFilter: $postFilter);

        foreach ($this->sorters as $sorter) {
            $sorter->apply(query: $query, requestConfiguration: $this->configuration);
        }

        $queryObject = $query->getQuery();

        $functionScore = $queryBuilder
            ->query()
            ->function_score()
            ->setQuery(query: $queryObject)
            ->setBoostMode()
            ->setScoreMode()
        ;

        foreach ($this->functionScores as $functionScoreClass) {
            $functionScoreClass->addFunctionScore(
                functionScore: $functionScore,
                requestConfiguration: $this->configuration,
            );
        }

        $query->setQuery(query: $functionScore);

        return $query;
    }

    public function supports(string $type, string $documentableCode): bool
    {
        return $type == $this->getType() && $this->getDocumentable()->getIndexCode() == $documentableCode;
    }

    public function setConfiguration(RequestConfiguration $configuration): void
    {
        $this->configuration = $configuration;
    }

    protected function addAggregations(Query $query, Query\BoolQuery $postFilter): void
    {
        // Used by children classes
    }
}
