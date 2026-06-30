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

final class MainTaxonPostFilter implements PostFilterInterface
{
    public function apply(BoolQuery $boolQuery, RequestConfiguration $requestConfiguration): void
    {
        $queryBuilder = new QueryBuilder();

        foreach ($requestConfiguration->getAppliedFilters(type: 'taxon') as $field => $values) {
            $mainTaxonQuery = $queryBuilder->query()->bool();

            $values = array_filter(array: $values) ?? [];

            foreach ($values as $value) {
                $mainTaxonQuery->addShould(
                    args: $queryBuilder
                        ->query()
                        ->term()
                        ->setTerm(
                            key: \sprintf('%s.code', $field),
                            value: SlugHelper::toLabel($value),
                        ),
                );
            }
            $boolQuery->addMust(
                args: $queryBuilder
                    ->query()
                    ->nested()
                    ->setPath(path: $field)
                    ->setQuery(query: $mainTaxonQuery),
            );
        }
    }
}
