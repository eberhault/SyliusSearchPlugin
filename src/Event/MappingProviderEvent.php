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

namespace MonsieurBiz\SyliusSearchPlugin\Event;

use ArrayObject;
use Symfony\Contracts\EventDispatcher\Event;

class MappingProviderEvent extends Event
{
    public const string EVENT_NAME = 'monsieurbiz.search.mapping.provider';

    /**
     * @param ArrayObject<string, array>|null $mapping
     */
    public function __construct(
        private readonly string $indexCode,
        private readonly ?ArrayObject $mapping,
        private readonly array $context = [],
    ) {
    }

    public function getIndexCode(): string
    {
        return $this->indexCode;
    }

    /**
     * @return ArrayObject<string, array>|null
     */
    public function getMapping(): ?ArrayObject
    {
        return $this->mapping;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
