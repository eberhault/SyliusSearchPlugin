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
use MonsieurBiz\SyliusSearchPlugin\Search\Request\QueryFilter\QueryFilterInterface;
use RuntimeException;
use Sylius\Component\Registry\ServiceRegistryInterface;

class InstantSearch implements InstantSearchInterface
{
    protected ?RequestConfiguration $configuration;

    /**
     * @param ServiceRegistryInterface $documentableRegistry
     * @param string $documentType
     * @param iterable<QueryFilterInterface> $queryFilters
     * @param iterable<FunctionScoreInterface> $functionScores
     */
    public function __construct(
        protected readonly ServiceRegistryInterface $documentableRegistry,
        protected readonly string $documentType,
        protected readonly iterable $queryFilters,
        protected readonly iterable $functionScores
    ) {
    }

    public function getType(): string
    {
        return RequestInterface::INSTANT_TYPE;
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
            throw new RuntimeException(message: 'missing configuration');
        }

        $queryBuilder = new QueryBuilder();

        $boolQuery = $queryBuilder->query()->bool();

        foreach ($this->queryFilters as $queryFilter) {
            $queryFilter->apply(boolQuery: $boolQuery, requestConfiguration: $this->configuration);
        }

        $query = Query::create(query: $boolQuery);

        /** @var Query\AbstractQuery $queryObject */
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
        return RequestInterface::INSTANT_TYPE == $type && $this->getDocumentable()->getIndexCode() == $documentableCode;
    }

    public function setConfiguration(RequestConfiguration $configuration): void
    {
        $this->configuration = $configuration;
    }
}
