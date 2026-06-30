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

namespace MonsieurBiz\SyliusSearchPlugin\Search;

use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use Pagerfanta\Adapter\AdapterInterface;

readonly class ResponseFactory
{
    public function __construct(
        private iterable $filterBuilders,
    ) {
    }

    public function build(
        RequestConfiguration $requestConfiguration,
        AdapterInterface $adapter,
        DocumentableInterface $documentable,
    ): ResponseInterface {
        return new Response(
            requestConfiguration: $requestConfiguration,
            adapter: $adapter,
            documentable: $documentable,
            filterBuilders: $this->filterBuilders,
        );
    }
}
