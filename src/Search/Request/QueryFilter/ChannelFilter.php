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
use Elastica\QueryBuilder;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use Sylius\Component\Channel\Context\ChannelContextInterface;

final readonly class ChannelFilter implements QueryFilterInterface
{
    public function __construct(
        private ChannelContextInterface $channelContext,
    ) {
    }

    public function apply(BoolQuery $boolQuery, RequestConfiguration $requestConfiguration): void
    {
        $queryBuilder = new QueryBuilder();

        $boolQuery->addFilter(
            filter: $queryBuilder
                ->query()
                ->nested()
                ->setPath(path: 'channels')
                ->setQuery(
                    query: $queryBuilder
                        ->query()
                        ->term(
                            term: ['channels.code' => ['value' => $this->channelContext->getChannel()->getCode()]],
                        ),
                ),
        );
    }
}
