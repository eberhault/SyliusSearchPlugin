<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\AutoMapper\Mapper;

interface MapperInterface
{
    public function supports(object $entity, string $targetClass): bool;

    public function map(object $entity, string $targetClass): object;
}
