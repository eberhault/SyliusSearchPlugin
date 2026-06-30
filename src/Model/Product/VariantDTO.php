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

namespace MonsieurBiz\SyliusSearchPlugin\Model\Product;

use Jacquesbh\Eater\Eater;

final class VariantDTO extends Eater
{
    public function getCode(): ?string
    {
        return $this->getData(name: 'code');
    }

    public function setCode(string $code): void
    {
        $this->setData(name: 'code', value: $code);
    }

    public function isEnabled(): bool
    {
        return (bool) $this->getData(name: 'enabled');
    }

    public function setEnabled(bool $enabled): void
    {
        $this->setData(name: 'enabled', value: $enabled);
    }

    public function isInStock(): bool
    {
        return (bool) $this->getData(name: 'is_in_stock');
    }

    public function setIsInStock(bool $isInStock): void
    {
        $this->setData(name: 'is_in_stock', value: $isInStock);
    }
}
