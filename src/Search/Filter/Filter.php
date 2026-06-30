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
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use MonsieurBiz\SyliusSearchPlugin\Search\Response\FilterInterface;

class Filter implements FilterInterface
{
    /** @var FilterValue[] */
    private array $values = [];

    /**
     * Filter constructor.
     */
    public function __construct(
        private readonly RequestConfiguration $requestConfiguration,
        private readonly string $code,
        private readonly string $label,
        private readonly int $count,
        private string $type = '',
    ) {
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    /** @return FilterValue[] */
    public function getValues(): array
    {
        return $this->values;
    }

    public function addValue(string $label, int $count, ?string $value = null): void
    {
        $this->values[] = new FilterValue(
            label: $label,
            count: $count,
            value: $value,
            isApplied: in_array(
                needle: SlugHelper::toSlug(label: $value ?? $label),
                haystack: $this->getCurrentValues(),
                strict: true,
            )
        );
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getAppliedValues(): array
    {
        return array_filter(
            array: $this->getValues(),
            callback: static fn(FilterValue $filterValue): bool => $filterValue->isApplied(),
        );
    }

    protected function getCurrentValues(): array
    {
        $appliedFilters = $this->requestConfiguration->getAppliedFilters();

        return $appliedFilters[$this->getType()][$this->getCode()] ?? $appliedFilters[$this->getCode()] ?? [];
    }
}
