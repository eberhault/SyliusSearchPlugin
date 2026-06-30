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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Request\QueryFilter;

use Elastica\Query\BoolQuery;
use Elastica\Query\MultiMatch;
use Elastica\QueryBuilder;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;

class SearchTermFilter implements QueryFilterInterface
{
    public function __construct(
        private readonly array $fieldsToSearch,
        private readonly array $nestedFieldsToSearch = []
    ) {
    }

    public function apply(BoolQuery $boolQuery, RequestConfiguration $requestConfiguration): void
    {
        $queryBuilder = new QueryBuilder();

        $searchCode = $queryBuilder
            ->query()
            ->term(
                term: ['code' => $requestConfiguration->getQueryText()],
            );

        $searchQuery = $queryBuilder->query()->bool();

        $searchQuery->addShould(args: $searchCode);

        $this->addFieldsToSearchCondition(searchQuery: $searchQuery, requestConfiguration: $requestConfiguration);

        $this->addNestedFieldsToSearchCondition(searchQuery: $searchQuery, requestConfiguration: $requestConfiguration);

        $this->addCustomFilters(searchQuery: $searchQuery, requestConfiguration: $requestConfiguration);

        $boolQuery->addMust(args: $searchQuery);
    }

    protected function addFieldsToSearchCondition(BoolQuery $searchQuery, RequestConfiguration $requestConfiguration): void
    {
        if (0 === count(value: $this->fieldsToSearch)) {
            return;
        }

        $queryBuilder = new QueryBuilder();

        $nameAndDescriptionQuery = $queryBuilder->query()->multi_match();

        $nameAndDescriptionQuery->setFields(fields: $this->fieldsToSearch);

        $nameAndDescriptionQuery->setQuery(query: $requestConfiguration->getQueryText());

        $nameAndDescriptionQuery->setType(type: MultiMatch::TYPE_MOST_FIELDS);

        $nameAndDescriptionQuery->setFuzziness(fuzziness: MultiMatch::FUZZINESS_AUTO);

        $searchQuery->addShould(args: $nameAndDescriptionQuery);
    }

    protected function addNestedFieldsToSearchCondition(BoolQuery $searchQuery, RequestConfiguration $requestConfiguration): void
    {
        if (0 === count(value: $this->nestedFieldsToSearch)) {
            return;
        }

        $nestedFields = [];

        // Group nested fields by nested path
        foreach ($this->nestedFieldsToSearch as $nestedFieldToSearch) {
            $nestedFieldExpression = explode(separator: ':', string: $nestedFieldToSearch);

            if (2 !== count($nestedFieldExpression)) {
                continue;
            }

            $nestedFields[$nestedFieldExpression[0]][] = str_replace(search: ':', replace: '.', subject: $nestedFieldToSearch);
        }

        // Create queries by nested path and nested values
        $queryBuilder = new QueryBuilder();

        foreach ($nestedFields as $nestedField => $nestedFieldExpressions) {
            $nestedFieldValueQuery = $queryBuilder->query()->multi_match();

            $nestedFieldValueQuery->setFields(fields: $nestedFieldExpressions);

            $nestedFieldValueQuery->setQuery(query: $requestConfiguration->getQueryText());

            $nestedFieldValueQuery->setType(type: MultiMatch::TYPE_MOST_FIELDS);

            $nestedFieldValueQuery->setFuzziness(fuzziness: MultiMatch::FUZZINESS_AUTO);

            $nestedFieldQuery = $queryBuilder->query()->nested();

            $nestedFieldQuery
                ->setPath(path: $nestedField)
                ->setQuery(query: $nestedFieldValueQuery);

            $searchQuery->addShould(args: $nestedFieldQuery);
        }
    }

    protected function addCustomFilters(BoolQuery $searchQuery, RequestConfiguration $requestConfiguration): void
    {
        // Used by children classes
    }
}
