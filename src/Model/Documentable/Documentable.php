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

namespace MonsieurBiz\SyliusSearchPlugin\Model\Documentable;

use Sylius\Component\Resource\Model\TranslatableInterface as OldTranslatableInterface;
use Sylius\Resource\Model\TranslatableInterface;

class Documentable implements PrefixedDocumentableInterface
{
    use DocumentableDatasourceTrait;
    use DocumentableMappingProviderTrait;

    private ?string $prefix = null;

    /**
     * @param array<string, string> $templates
     */
    public function __construct(
        private readonly string $indexCode,
        private readonly string $sourceClass,
        private readonly string $targetClass,
        private readonly array $templates,
        private readonly array $limits
    ) {
    }

    public function getIndexCode(): string
    {
        return $this->indexCode;
    }

    public function getSourceClass(): string
    {
        return $this->sourceClass;
    }

    public function getTargetClass(): string
    {
        return $this->targetClass;
    }

    public function isTranslatable(): bool
    {
        $interface = (array)class_implements(object_or_class: $this->getSourceClass());

        return \in_array(needle: TranslatableInterface::class, haystack: $interface, strict: true)
            || \in_array(needle: OldTranslatableInterface::class, haystack: $interface, strict: true);
    }

    public function getTemplate(string $type): ?string
    {
        return $this->templates[$type] ?? null;
    }

    public function getLimits(?string $queryType = null): array
    {
        if (null == $queryType) {
            return $this->limits;
        }

        return $this->limits[$queryType] ?? [];
    }

    public function getPrefix(): string
    {
        return $this->prefix ?? '';
    }

    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }
}
