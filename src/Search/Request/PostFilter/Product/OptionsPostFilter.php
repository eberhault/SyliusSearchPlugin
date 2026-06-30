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

final readonly class OptionsPostFilter implements PostFilterInterface
{
    public function __construct(
        private bool $enableStockFilter,
    ) {
    }

    public function apply(BoolQuery $boolQuery, RequestConfiguration $requestConfiguration): void
    {
        $queryBuilder = new QueryBuilder();

        foreach ($requestConfiguration->getAppliedFilters(type: 'options') as $field => $values) {
            $optionValueQuery = $queryBuilder->query()->bool();

            foreach ($values as $value) {
                $termQuery = $queryBuilder
                    ->query()
                    ->term(
                        term: [sprintf('options.%s.values.value.keyword', $field) => SlugHelper::toLabel($value)],
                    );

                $optionValueQuery->addShould(args: $termQuery); // todo configure the "and" or "or"
            }

            $optionQuery = $queryBuilder->query()->nested();

            $condition = $queryBuilder->query()->bool()
                ->addMust(
                    args: $queryBuilder
                        ->query()
                        ->term(
                            term: [sprintf('options.%s.values.enabled', $field) => true],
                        ),
                );
            if ($this->enableStockFilter) {
                $condition->addMust(
                    args: $queryBuilder
                        ->query()
                        ->term(
                            term: [sprintf('options.%s.values.is_in_stock', $field) => true],
                        ),
                );
            }

            $condition->addMust(args: $optionValueQuery);

            $optionQuery
                ->setPath(
                    path: sprintf('options.%s.values', $field),
                )
                ->setQuery(query: $condition);

            $boolQuery->addMust(args: $optionQuery);
        }
    }
}
