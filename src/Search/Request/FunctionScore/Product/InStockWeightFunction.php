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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Request\FunctionScore\Product;

use Elastica\Query\FunctionScore;
use Elastica\QueryBuilder;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\FunctionScore\FunctionScoreInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;

readonly class InStockWeightFunction implements FunctionScoreInterface
{
    public function __construct(
        private bool $enableStockFilter,
        private int $inStockWeight,
        private array $applyOnRequestTypes
    ) {
    }

    public function addFunctionScore(FunctionScore $functionScore, RequestConfiguration $requestConfiguration): void
    {
        if (
            $this->enableStockFilter
            || 1 > $this->inStockWeight
            || !in_array(needle: $requestConfiguration->getType(), haystack: $this->applyOnRequestTypes, strict: true)
        ) {
            return;
        }

        $queryBuilder = new QueryBuilder();

        $filter = $queryBuilder
            ->query()
            ->nested()
            ->setPath(path: 'variants')
            ->setQuery(
                query: $queryBuilder->query()->term(term: ['variants.is_in_stock' => true]),
            );

        $functionScore->addWeightFunction(
            weight: $this->inStockWeight,
            filter: $filter,
        );
    }
}
