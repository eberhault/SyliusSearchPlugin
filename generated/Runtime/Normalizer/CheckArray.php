<?php

namespace MonsieurBiz\SyliusSearchPlugin\Generated\Runtime\Normalizer;

trait CheckArray
{
    public function isOnlyNumericKeys(array $array): bool
    {
        $numericOnly = array_filter(
            $array,
            static fn(mixed $key): bool => is_numeric($key),
            ARRAY_FILTER_USE_KEY);

        return count($numericOnly) === count($array);
    }
}