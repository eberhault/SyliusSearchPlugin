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

namespace MonsieurBiz\SyliusSearchPlugin\Message;

class ProductReindexFromIds
{
    public function __construct(
        private array $productIds = [],
    ) {
    }

    public function getProductIds(): array
    {
        return array_unique(array: $this->productIds);
    }

    public function addProductId(int $productIds): void
    {
        $this->productIds[] = $productIds;
    }
}
