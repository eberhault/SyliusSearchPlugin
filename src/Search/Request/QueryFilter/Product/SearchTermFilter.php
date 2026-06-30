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
use Elastica\Query\MultiMatch;
use Elastica\QueryBuilder;
use MonsieurBiz\SyliusSearchPlugin\Repository\ProductAttributeRepositoryInterface;
use MonsieurBiz\SyliusSearchPlugin\Repository\ProductOptionRepositoryInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\QueryFilter\SearchTermFilter as BaseSearchTermFilter;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use Sylius\Component\Product\Model\ProductAttributeInterface;

final class SearchTermFilter extends BaseSearchTermFilter
{
    public function __construct(
        private readonly ProductAttributeRepositoryInterface $productAttributeRepository,
        private readonly ProductOptionRepositoryInterface $productOptionRepository,
        array $fieldsToSearch,
        array $nestedFieldsToSearch = []
    ) {
        parent::__construct($fieldsToSearch, $nestedFieldsToSearch);
    }

    protected function addCustomFilters(BoolQuery $searchQuery, RequestConfiguration $requestConfiguration): void
    {
        $this->addAttributesQueries(searchQuery: $searchQuery, requestConfiguration: $requestConfiguration);
        $this->addOptionsQueries(searchQuery: $searchQuery, requestConfiguration: $requestConfiguration);
    }

    private function addAttributesQueries(BoolQuery $searchQuery, RequestConfiguration $requestConfiguration): void
    {
        $queryBuilder = new QueryBuilder();

        /** @var ProductAttributeInterface $productAttribute */
        foreach ($this->productAttributeRepository->findIsSearchableOrFilterable() as $productAttribute) {
            if (!$productAttribute->isSearchable()) {
                continue;
            }

            $attributeValueQuery = $queryBuilder->query()->multi_match();

            $attributeValueQuery->setFields(
                fields: [
                    \sprintf(
                        'attributes.%s.value^%d',
                        $productAttribute->getCode(),
                        $productAttribute->getSearchWeight(),
                    ),
                ],
            );

            $attributeValueQuery->setQuery(query: $requestConfiguration->getQueryText());

            $attributeValueQuery->setFuzziness(fuzziness: MultiMatch::FUZZINESS_AUTO);

            $attributeQuery = $queryBuilder->query()->nested();

            $attributeQuery
                ->setPath(
                    path: sprintf('attributes.%s', $productAttribute->getCode()),
                )
                ->setQuery($attributeValueQuery);

            $searchQuery->addShould(args: $attributeQuery);
        }
    }

    private function addOptionsQueries(BoolQuery $searchQuery, RequestConfiguration $requestConfiguration): void
    {
        $qb = new QueryBuilder();
        foreach ($this->productOptionRepository->findIsSearchableOrFilterable() as $productOption) {
            if (!$productOption->isSearchable()) {
                continue;
            }

            $attributeValueQuery = $qb->query()->multi_match();
            $attributeValueQuery->setFields([
                \sprintf('options.%s.values.value^%d', $productOption->getCode(), $productOption->getSearchWeight()),
            ]);
            $attributeValueQuery->setQuery($requestConfiguration->getQueryText());
            $attributeValueQuery->setFuzziness(MultiMatch::FUZZINESS_AUTO);

            $attributeQuery = $qb->query()->nested();
            $attributeQuery->setPath(\sprintf('options.%s.values', $productOption->getCode()))->setQuery($attributeValueQuery);

            $searchQuery->addShould($attributeQuery);
        }
    }
}
