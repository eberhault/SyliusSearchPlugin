<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\AutoMapper\Mapper;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

readonly class Mapper
{
    public function __construct(
        #[AutowireIterator(tag: 'monsieurbiz.search.mapper')]
        private iterable $mappers,
    ) {
    }

    public function map(object $entity, string $targetClass): object
    {
        foreach ($this->mappers as $mapper) {
            if ($mapper->supports($entity, $targetClass)) {
                return $mapper->map($entity, $targetClass);
            }
        }

        throw new \RuntimeException('No mapper found for class ' . $targetClass);
    }
}
