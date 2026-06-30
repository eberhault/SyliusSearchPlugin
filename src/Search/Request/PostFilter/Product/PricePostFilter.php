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
use MonsieurBiz\SyliusSearchPlugin\Search\Request\PostFilter\PostFilterInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use Sylius\Component\Channel\Context\ChannelContextInterface;

use function count;

final readonly class PricePostFilter implements PostFilterInterface
{
    public function __construct(
        private ChannelContextInterface $channelContext,
    ) {
    }

    public function apply(BoolQuery $boolQuery, RequestConfiguration $requestConfiguration): void
    {
        $queryBuilder = new QueryBuilder();

        $priceValue = $requestConfiguration->getAppliedFilters(type: 'price');

        if (0 !== count(value: $priceValue)) {
            $channelPriceFilter = $queryBuilder
                ->query()
                ->term(
                    term: ['prices.channel_code' => $this->channelContext->getChannel()->getCode()],
                );

            $conditions = [];

            if (\array_key_exists(key: 'min', array: $priceValue)) {
                $conditions['gte'] = $priceValue['min'] * 100;
            }

            if (\array_key_exists(key: 'max', array: $priceValue)) {
                $conditions['lte'] = $priceValue['max'] * 100;
            }

            $priceQuery = $queryBuilder
                ->query()
                ->range(fieldName: 'prices.price', args: $conditions);

            $boolQuery->addMust(
                args: $queryBuilder->query()
                    ->nested()
                    ->setPath(path: 'prices')
                    ->setQuery(
                        query: $queryBuilder
                            ->query()
                            ->bool()
                            ->addMust(args: $channelPriceFilter)
                            ->addMust(args: $priceQuery),
                    ),
            );
        }
    }
}
