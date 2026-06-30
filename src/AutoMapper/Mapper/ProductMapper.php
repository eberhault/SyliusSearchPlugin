<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\AutoMapper\Mapper;

use AutoMapper\AutoMapper;
use AutoMapper\AutoMapperInterface;
use MonsieurBiz\SyliusSearchPlugin\AutoMapper\ConfigurationInterface;
use MonsieurBiz\SyliusSearchPlugin\Context\ChannelSimulationContext;
use MonsieurBiz\SyliusSearchPlugin\Entity\Product\SearchableInterface;
use MonsieurBiz\SyliusSearchPlugin\Generated\Model\TaxonDTO;
use MonsieurBiz\SyliusSearchPlugin\Model\Product\ProductDTO;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductTaxonInterface;
use Sylius\Component\Core\Model\ProductVariantInterface as ModelProductVariantInterface;
use Sylius\Component\Inventory\Checker\AvailabilityCheckerInterface;
use Sylius\Component\Inventory\Model\StockableInterface;
use Sylius\Component\Product\Model\ProductVariantInterface;
use Sylius\Component\Product\Resolver\ProductVariantResolverInterface;
use Webmozart\Assert\Assert;

class ProductMapper implements MapperInterface
{
    private AutoMapperInterface $autoMapper;

    public function __construct(
        private readonly ConfigurationInterface $configuration,
        private readonly AvailabilityCheckerInterface $availabilityChecker,
        private readonly ProductVariantResolverInterface $productVariantResolver,
        private readonly ChannelSimulationContext $channelSimulationContext,
        private readonly Mapper $mapper,
    ) {
        $this->autoMapper = AutoMapper::create();
    }

    public function supports(object $entity, string $targetClass): bool
    {
        return $entity instanceof ProductInterface && ProductDTO::class === $targetClass;
    }

    public function map(object $entity, string $targetClass): object
    {
        Assert::isInstanceOf(value: $entity, class: ProductInterface::class);

        $productDto = $this->autoMapper->map(source: $entity, target: $targetClass);

        Assert::isInstanceOf(value: $productDto, class: ProductDTO::class);

        $productDto->setData(
            name: 'images',
            value: $this->autoMapper->mapCollection(
                collection: $entity->getImages()->toArray(),
                target: $this->configuration->getTargetClass(identifier: 'image'),
            ),
        );

        $productDto->setData(
            name: 'main_taxon',
            value: $this->mapMainTaxon(product: $entity),
        );

        $productDto->setData(
            name: 'product_taxons',
            value: $this->mapProductTaxon(product: $entity),
        );

        $productDto->setData(
            name: 'channels',
            value: $this->autoMapper->mapCollection(
                collection: $entity->getChannels()->toArray(),
                target: $this->configuration->getTargetClass(identifier: 'channel'),
            ),
        );

        $productDto->setData(
            name: 'attributes',
            value: $this->mapAttributes(product: $entity),
        );

        $productDto->setData(
            name: 'options',
            value: $this->mapOptions(product: $entity),
        );

        $productDto->setData(
            name: 'variants',
            value: $this->mapVariants(product: $entity),
        );

        $productDto->setData(
            name: 'prices',
            value: $this->mapPrices(product: $entity),
        );

        return $productDto;
    }

    private function mapMainTaxon(ProductInterface $product): ?TaxonDTO
    {
        $mainTaxon = $product->getMainTaxon();
        if (null === $mainTaxon) {
            return null;
        }

        $currentLocale = $product->getTranslation()->getLocale();
        if (null !== $currentLocale) {
            $mainTaxon->setCurrentLocale($currentLocale);
        }

        /** @var ?TaxonDTO */
        return $this->autoMapper->map(
            source: $mainTaxon,
            target: $this->configuration->getTargetClass(identifier: 'taxon'),
        );
    }

    private function mapProductTaxon(ProductInterface $product): array
    {
        return array_map(
            callback: function (ProductTaxonInterface $productTaxon) use ($product) {
                $taxon = $productTaxon->getTaxon();

                $currentLocale = $product->getTranslation()->getLocale();

                if (null !== $currentLocale && null !== $taxon) {
                    $taxon->setCurrentLocale($currentLocale);
                }

                return $this->autoMapper->map(
                    source: $productTaxon,
                    target: $this->configuration->getTargetClass(identifier: 'product_taxon'),
                );
            },
            array: $product->getProductTaxons()->toArray(),
        );
    }

    private function mapAttributes(ProductInterface $product): array
    {
        $currentLocale = $product->getTranslation()->getLocale();

        if (null === $currentLocale) {
            return [];
        }

        $attributeValues = $product->getAttributesByLocale($currentLocale, $currentLocale);

        $attributes = [];

        foreach ($attributeValues as $attributeValue) {
            $attribute = $attributeValue->getAttribute();

            $currentLocale = $product->getTranslation()->getLocale();

            if (null !== $currentLocale && null !== $attribute) {
                $attribute->setCurrentLocale($currentLocale);
            }

            if (null === $attributeValue->getName() || null === $attributeValue->getValue()) {
                continue;
            }

            $attribute = $attributeValue->getAttribute();

            if (!$attribute instanceof SearchableInterface || (!$attribute->isSearchable() && !$attribute->isFilterable())) {
                continue;
            }

            $attributeDto = $this->autoMapper->map(
                source: $attributeValue,
                target: $this->configuration->getTargetClass(identifier: 'product_attribute'),
            );

            $attributes[$attributeValue->getCode()] = $attributeDto;
        }

        return $attributes;
    }

    private function mapOptions(ProductInterface $product): array
    {
        $currentLocale = $product->getTranslation()->getLocale();

        $options = [];

        foreach ($product->getVariants() as $variant) {
            foreach ($variant->getOptionValues() as $optionValue) {
                if (null === $optionValue->getOption()) {
                    continue;
                }

                if (!isset($options[$optionValue->getOptionCode()])) {
                    $options[$optionValue->getOptionCode()] = [
                        'name' => $optionValue->getOption()->getTranslation($currentLocale)->getName(),
                        'values' => [],
                    ];
                }

                $isEnabled = ($options[$optionValue->getOptionCode()]['values'][$optionValue->getCode()]['enabled'] ?? false)
                    || $variant->isEnabled();

                // A variant option is considered to be in stock if the current option is enabled and is in stock
                $isInStock = ($options[$optionValue->getOptionCode()]['values'][$optionValue->getCode()]['is_in_stock'] ?? false)
                    || ($variant->isEnabled() && $this->isProductVariantInStock($variant));

                $options[$optionValue->getOptionCode()]['values'][$optionValue->getCode()] = [
                    'value' => $optionValue->getTranslation($currentLocale)->getValue(),
                    'enabled' => $isEnabled,
                    'is_in_stock' => $isInStock,
                ];
            }
        }

        foreach ($options as $optionCode => $optionValues) {
            $options[$optionCode]['values'] = array_values($optionValues['values']);
        }

        return $options;
    }

    private function isProductVariantInStock(ProductVariantInterface $productVariant): bool
    {
        if (!$productVariant instanceof StockableInterface) {
            return true;
        }

        return $this->availabilityChecker->isStockAvailable($productVariant);
    }

    private function mapVariants(ProductInterface $product): array
    {
        $variants = [];

        foreach ($product->getEnabledVariants() as $variant) {
            $variants[] = $this->mapper->map(
                entity: $variant,
                targetClass: $this->configuration->getTargetClass(identifier: 'product_variant'),
            );
        }

        return $variants;
    }

    public function mapPrices(ProductInterface $product): array
    {
        $prices = [];

        foreach ($product->getChannels() as $channel) {
            $this->channelSimulationContext->setChannel($channel);

            /** @var ?ModelProductVariantInterface $variant */
            $variant = $this->productVariantResolver->getVariant(subject: $product);

            $channelPricing = $variant->getChannelPricingForChannel(channel: $channel);

            $this->channelSimulationContext->setChannel(channel: null);

            if (
                !$variant instanceof ModelProductVariantInterface || null === $channelPricing
            ) {
                continue;
            }

            $prices[] = $this->autoMapper->map(
                source: $channelPricing,
                target: $this->configuration->getTargetClass(identifier: 'pricing')
            );
        }

        return $prices;
    }
}
