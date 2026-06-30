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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Request\PostFilter\Product;

use Elastica\Query\BoolQuery;
use Elastica\QueryBuilder;
use MonsieurBiz\SyliusSearchPlugin\Helper\SlugHelper;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\PostFilter\PostFilterInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;

final class ProductTaxonPostFilter implements PostFilterInterface
{
    public function apply(BoolQuery $boolQuery, RequestConfiguration $requestConfiguration): void
    {
        $queryBuilder = new QueryBuilder();

        $taxonsSelected = $requestConfiguration->getAppliedFilters(type: 'taxons');

        if (0 !== count(value: $taxonsSelected)) {
            $taxonQuery = $queryBuilder->query()->bool();

            foreach ($taxonsSelected as $value) {
                $taxonQuery->addShould(
                    args: $queryBuilder
                        ->query()
                        ->term()
                        ->setTerm(key: 'product_taxons.taxon.code', value: SlugHelper::toLabel(slug: $value)),
                );
            }

            $boolQuery->addMust(
                args: $queryBuilder
                    ->query()
                    ->nested()
                    ->setPath(path: 'product_taxons')
                    ->setQuery(
                        $queryBuilder
                            ->query()
                            ->nested()
                            ->setPath(path: 'product_taxons.taxon')
                            ->setQuery(query: $taxonQuery)
                    ),
            );
        }
    }
}
