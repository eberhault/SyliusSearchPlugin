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

final class AttributesPostFilter implements PostFilterInterface
{
    public function apply(BoolQuery $boolQuery, RequestConfiguration $requestConfiguration): void
    {
        $queryBuilder = new QueryBuilder();

        foreach ($requestConfiguration->getAppliedFilters(type: 'attributes') as $field => $values) {
            $attributeValueQuery = $queryBuilder->query()->bool();

            foreach ($values as $value) {
                $termQuery = $queryBuilder
                    ->query()
                    ->term(
                        term: [sprintf('attributes.%s.value.keyword', $field) => SlugHelper::toLabel($value)],
                    );

                $attributeValueQuery->addShould(args: $termQuery); // todo configure the "and" or "or"
            }

            $attributeQuery = $queryBuilder->query()->nested();

            $attributeQuery
                ->setPath(sprintf('attributes.%s', $field))
                ->setQuery(query: $attributeValueQuery);

            $boolQuery->addMust(args: $attributeQuery);
        }
    }
}
