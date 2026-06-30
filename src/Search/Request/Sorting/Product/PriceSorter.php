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
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\Sorting\SorterBuilderTrait;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\Sorting\SorterInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;

final class PriceSorter implements SorterInterface
{
    use SorterBuilderTrait;

    public function __construct(
        private readonly ChannelContextInterface $channelContext,
    ) {
    }

    public function apply(Query $query, RequestConfiguration $requestConfiguration): void
    {
        $sorting = $requestConfiguration->getSorting();

        if (!array_key_exists(key: 'price', array: $sorting)) {
            return;
        }

        $query->addSort(
            sort: $this->buildSort(
                field: 'prices.price',
                order: $sorting['price'],
                nestedPath: 'prices',
                sortFilterField: 'prices.channel_code',
                sortFilterValue: $this->channelContext->getChannel()->getCode(),
            )
        );
    }
}
