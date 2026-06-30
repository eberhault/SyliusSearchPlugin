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

namespace MonsieurBiz\SyliusSearchPlugin\EventSubscriber;

use MonsieurBiz\SyliusSearchPlugin\Entity\Product\SearchableInterface;
use MonsieurBiz\SyliusSearchPlugin\Event\MappingProviderEvent;
use MonsieurBiz\SyliusSearchPlugin\Repository\ProductAttributeRepositoryInterface;
use MonsieurBiz\SyliusSearchPlugin\Repository\ProductOptionRepositoryInterface;
use Sylius\Component\Product\Model\ProductAttributeInterface;
use Sylius\Component\Product\Model\ProductOptionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

readonly class AppendProductAttributeMappingSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ProductAttributeRepositoryInterface $productAttributeRepository,
        private ProductOptionRepositoryInterface $productOptionRepository,
        private string $fieldAnalyzer
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MappingProviderEvent::EVENT_NAME => 'onMappingProvider',
        ];
    }

    public function onMappingProvider(MappingProviderEvent $event): void
    {
        if (false === str_ends_with(haystack: $event->getIndexCode(), needle: 'monsieurbiz_product')) {
            return;
        }

        $mapping = $event->getMapping();

        if (null === $mapping || !$mapping->offsetExists(key: 'mappings')) {
            return;
        }

        /** @var array $mappings */
        $mappings = $mapping->offsetGet('mappings');
        $mappings = $this->appendAttributesMapping(mappings: $mappings);
        $mappings = $this->appendOptionsMapping(mappings: $mappings);

        $mapping->offsetSet('mappings', $mappings);
    }

    private function appendAttributesMapping(array $mappings): array
    {
        $attributesMapping = [];

        /** @var ProductAttributeInterface $productAttribute */
        foreach ($this->productAttributeRepository->findIsSearchableOrFilterable() as $productAttribute) {
            $attributesMapping[$productAttribute->getCode()] = $this->getProductAttributeProperties(productAttribute: $productAttribute);
        }

        if (0 < count($attributesMapping)) {
            $mappings['properties']['attributes'] = [
                'type' => 'nested',
                'properties' => $attributesMapping,
            ];
        }

        return $mappings;
    }

    private function appendOptionsMapping(array $mappings): array
    {
        $optionsMapping = [];

        /** @var ProductOptionInterface $productOption */
        foreach ($this->productOptionRepository->findIsSearchableOrFilterable() as $productOption) {
            $optionsMapping[$productOption->getCode()] = $this->getProductOptionProperties($productOption);
        }

        if (0 < count($optionsMapping)) {
            $mappings['properties']['options'] = [
                'type' => 'nested',
                'properties' => $optionsMapping,
            ];
        }

        return $mappings;
    }

    private function getProductAttributeProperties(SearchableInterface $productAttribute): array
    {
        $properties = [
            'type' => 'nested',
            'properties' => [
                'code' => ['type' => 'keyword'],
                'name' => ['type' => 'keyword'],
                'value' => ['type' => 'text'],
            ],
        ];

        if ($productAttribute->isFilterable()) {
            $properties['properties']['value']['fields'] = [
                'keyword' => ['type' => 'keyword'],
            ];
        }

        if ($productAttribute->isSearchable()) {
            $properties['properties']['value']['analyzer'] = $this->fieldAnalyzer;
        }

        return $properties;
    }

    private function getProductOptionProperties(SearchableInterface $productOption): array
    {
        $properties = [
            'type' => 'nested',
            'properties' => [
                'code' => ['type' => 'keyword'],
                'name' => ['type' => 'keyword'],
                'values' => [
                    'type' => 'nested',
                    'properties' => [
                        'value' => ['type' => 'text'],
                        'enabled' => ['type' => 'boolean'],
                        'is_in_stock' => ['type' => 'boolean'],
                    ],
                ],
            ],
        ];

        if ($productOption->isFilterable()) {
            $properties['properties']['values']['properties']['value']['fields'] = [
                'keyword' => ['type' => 'keyword'],
            ];
        }

        if ($productOption->isSearchable()) {
            $properties['properties']['values']['properties']['value']['analyzer'] = $this->fieldAnalyzer;
        }

        return $properties;
    }
}
