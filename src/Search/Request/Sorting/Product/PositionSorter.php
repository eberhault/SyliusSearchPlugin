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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Request\Sorting\Product;

use Elastica\Query;
use Elastica\QueryBuilder;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\Sorting\SorterBuilderTrait;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\Sorting\SorterInterface;

final class PositionSorter implements SorterInterface
{
    use SorterBuilderTrait;

    public function apply(Query $query, RequestConfiguration $requestConfiguration): void
    {
        $sorting = $requestConfiguration->getSorting();

        if (!array_key_exists(key: 'position', array: $sorting) && 0 !== count(value: $sorting)) {
            return;
        }

        $query->addSort(
            sort: $this->buildSort(field: '_score', order: 'desc'),
        );

        if (RequestInterface::TAXON_TYPE === $requestConfiguration->getType()) {
            $queryBuilder = new QueryBuilder();

            $filter = $queryBuilder
                ->query()
                ->nested()
                ->setPath(path: 'product_taxons.taxon')
                ->setQuery(
                    query: $queryBuilder
                        ->query()
                        ->term(
                            term: ['product_taxons.taxon.code' => ['value' => $requestConfiguration->getTaxon()->getCode()]],
                        ),
                )
            ;

            $query->addSort(
                sort: $this->buildSort(
                    field: 'product_taxons.position',
                    order: 'asc',
                    nestedPath: 'product_taxons',
                    sortFilterValue: $filter,
                ),
            );
        }
    }
}
