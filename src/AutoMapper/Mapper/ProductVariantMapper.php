<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\AutoMapper\Mapper;

use AutoMapper\AutoMapper;
use AutoMapper\AutoMapperInterface;
use MonsieurBiz\SyliusSearchPlugin\Model\Product\VariantDTO;
use Sylius\Component\Inventory\Checker\AvailabilityCheckerInterface;
use Sylius\Component\Inventory\Model\StockableInterface;
use Sylius\Component\Product\Model\ProductVariantInterface;
use Webmozart\Assert\Assert;

class ProductVariantMapper implements MapperInterface
{
    protected AutoMapperInterface $autoMapper;

    public function __construct(
        private readonly AvailabilityCheckerInterface $availabilityChecker,
    ) {
        $this->autoMapper = AutoMapper::create();
    }

    public function supports(object $entity, string $targetClass): bool
    {
        return $entity instanceof ProductVariantInterface && VariantDTO::class === $targetClass;
    }

    public function map(object $entity, string $targetClass): object
    {
        Assert::isInstanceOf(value: $entity, class: ProductVariantInterface::class);

        /** @var VariantDTO $productVariantDto */
        $productVariantDto = $this->autoMapper->map(source: $entity, target: $targetClass);

        $productVariantDto->setIsInStock(
            isInStock: $this->isProductVariantInStock(productVariant: $entity),
        );

        return $productVariantDto;
    }

    private function isProductVariantInStock(ProductVariantInterface $productVariant): bool
    {
        if (!$productVariant instanceof StockableInterface) {
            return true;
        }

        return $this->availabilityChecker->isStockAvailable($productVariant);
    }
}
