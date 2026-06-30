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

use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use MonsieurBiz\SyliusSearchPlugin\Search\Response\FilterInterface;

class RangeFilter implements FilterInterface
{
    private array $values = [];

    /**
     * Filter constructor.
     */
    public function __construct(
        private readonly RequestConfiguration $requestConfiguration,
        private readonly string $code,
        private readonly string $label,
        private readonly string $minLabel,
        private readonly string $maxLabel,
        private readonly int $min,
        private readonly int $max,
    ) {
        $this->addValue(label: $minLabel, count: 0, value: (string) $min);
        $this->addValue(label: $maxLabel, count: 0, value: (string) $max);
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function addValue(string $label, int $count, ?string $value = null): void
    {
        $currentValueType = $this->getValueType(valueLabel: $label);

        $currentValues = $this->getCurrentValues();

        $isApplied = array_key_exists(key: $currentValueType, array: $currentValues);

        $value = $isApplied ? $currentValues[$currentValueType] : $value;

        $this->values[] = new FilterValue(
            label: $label,
            count: $count,
            value: $value,
            isApplied: $isApplied,
        );
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function getType(): string
    {
        return 'range';
    }

    public function getAppliedValues(): array
    {
        return array_filter(
            array: $this->values,
            callback: static fn (FilterValue $filterValue): bool => $filterValue->isApplied(),
        );
    }

    public function getDefaultValue(string $type): int
    {
        if ('min' === $type) {
            return $this->min;
        }

        return $this->max;
    }

    public function getValueType(string $valueLabel): string
    {
        if ($valueLabel === $this->minLabel) {
            return 'min';
        }

        return 'max';
    }

    protected function getCurrentValues(): array
    {
        $appliedFilters = $this->requestConfiguration->getAppliedFilters();

        return $appliedFilters[$this->getType()] ?? $appliedFilters[$this->getCode()] ?? $appliedFilters;
    }
}
