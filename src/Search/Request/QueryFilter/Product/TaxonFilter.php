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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Request\QueryFilter\Product;

use Elastica\Query\BoolQuery;
use Elastica\QueryBuilder;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\QueryFilter\QueryFilterInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;

final class TaxonFilter implements QueryFilterInterface
{
    public function apply(BoolQuery $boolQuery, RequestConfiguration $requestConfiguration): void
    {
        $queryBuilder = new QueryBuilder();

        $searchQuery = $queryBuilder
            ->query()
            ->nested()
            ->setPath(path: 'product_taxons')
            ->setQuery(
                query: $queryBuilder
                    ->query()
                    ->nested()
                    ->setPath(path: 'product_taxons.taxon')
                    ->setQuery(
                        query: $queryBuilder
                            ->query()
                            ->term(
                                term: ['product_taxons.taxon.code' => ['value' => $requestConfiguration->getTaxon()->getCode()]],
                            ),
                    ),
            )
        ;

        if ($requestConfiguration->getTaxon()->isRoot()) {
            $searchQuery = $queryBuilder->query()->bool();
        }

        $boolQuery->addMust(args: $searchQuery);
    }
}
