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

namespace MonsieurBiz\SyliusSearchPlugin\Search;

use Elastica\ResultSet;
use JoliCode\Elastically\Result;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use MonsieurBiz\SyliusSearchPlugin\Search\Response\FilterBuilders\FilterBuilderInterface;
use Pagerfanta\Adapter\AdapterInterface;
use Pagerfanta\Pagerfanta;
use Traversable;

class Response implements ResponseInterface
{
    /**
     * @var Pagerfanta<Result>|null
     */
    private ?Pagerfanta $paginator = null;

    private array $filters = [];

    public function __construct(
        private readonly RequestConfiguration $requestConfiguration,
        private readonly AdapterInterface $adapter,
        private readonly DocumentableInterface $documentable,
        private readonly iterable $filterBuilders
    ) {
        $this->buildFilters();
    }

    public function getIterator(): Traversable
    {
        return $this->getPaginator();
    }

    public function count(): int
    {
        return $this->getPaginator()->getNbResults();
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getPaginator(): Pagerfanta
    {
        if (!$this->paginator instanceof Pagerfanta) {
            $this->paginator = (new Pagerfanta(adapter: $this->adapter))
                ->setMaxPerPage(maxPerPage: $this->requestConfiguration->getLimit())
                ->setCurrentPage(currentPage: $this->requestConfiguration->getPage());
        }

        return $this->paginator;
    }

    public function getDocumentable(): DocumentableInterface
    {
        return $this->documentable;
    }

    private function buildFilters(): void
    {
        /** @var ResultSet $results */
        $results = $this->getPaginator()->getCurrentPageResults();

        $aggregations = $results->getAggregations();

        // No aggregation so don't perform filters
        if (0 === count(value: $aggregations)) {
            return;
        }

        array_map(
            function ($aggregationCode, $aggregationData): void {
                /** @var FilterBuilderInterface $filterBuilder */
                foreach ($this->filterBuilders as $filterBuilder) {
                    $filter = $filterBuilder->build(
                        documentable: $this->getDocumentable(),
                        requestConfiguration: $this->requestConfiguration,
                        aggregationCode: $aggregationCode,
                        aggregationData: $aggregationData,
                    );

                    if (null !== $filter) {
                        $this->filters[$filterBuilder->getPosition()][] = $filter;
                    }
                }
            },
            array_keys(array: $aggregations),
            $aggregations,
        );

        $result = [];

        ksort($this->filters);

        foreach ($this->filters as $filters) {
            foreach ($filters as $filter) {
                $result[] = $filter;
            }
        }

        $this->filters = array_merge(...$result);
    }
}
