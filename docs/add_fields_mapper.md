With complexe data:
```php
<?php

namespace App\Search\Automapper\Mapper;

use App\Entity\Product\ProductVariant;
use MonsieurBiz\SyliusSearchPlugin\AutoMapper\Mapper\MapperInterface;
use MonsieurBiz\SyliusSearchPlugin\AutoMapper\Mapper\ProductVariantMapper as BaseProductVariantMapper;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Webmozart\Assert\Assert;

#[AsDecorator(decorates: BaseProductVariantMapper::class)]
final readonly class ProductVariantMapper implements MapperInterface
{
    public function __construct(
        #[AutowireDecorated]
        private BaseProductVariantMapper $inner,
    ) {
    }

    public function supports(object $entity, string $targetClass): bool
    {
        return $this->inner->supports(entity: $entity, targetClass: $targetClass);
    }

    public function map(object $entity, string $targetClass): object
    {
        Assert::isInstanceOf(value: $entity, class: ProductVariant::class);

        $productVariantDto = $this->inner->map(entity: $entity, targetClass: $targetClass);

        $productVariantDto->setData(
            name: 'foo',
            value: $entity->getFoo(),
        );

        return $productVariantDto;
    }
}
```

OR

With simple data:
```php
#[MapTo(property: 'foo')]
public function isFoo(): bool
{
    return $this->bar > 0;
}
```