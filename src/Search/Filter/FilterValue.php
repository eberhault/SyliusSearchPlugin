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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Filter;

use MonsieurBiz\SyliusSearchPlugin\Helper\SlugHelper;

class FilterValue
{
    /**
     * Filter constructor.
     */
    public function __construct(
        private readonly string $label,
        private readonly int $count,
        private ?string $value = null,
        private readonly bool $isApplied = false,
    ) {
        $this->value = $this->value ?? $label;
    }

    public function getSlug(): string
    {
        return SlugHelper::toSlug(label: $this->value);
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function isApplied(): bool
    {
        return $this->isApplied;
    }
}
