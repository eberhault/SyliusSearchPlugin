<?php

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\AutoMapper\Mapper;

use AutoMapper\AutoMapper;
use AutoMapper\AutoMapperInterface;
use MonsieurBiz\SyliusSearchPlugin\AutoMapper\ProductAttributeValueReader\ReaderInterface;
use MonsieurBiz\SyliusSearchPlugin\Generated\Model\ProductAttributeDTO;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Sylius\Component\Product\Model\ProductAttributeValueInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Traversable;
use Webmozart\Assert\Assert;

class ProductAttributeValueMapper implements MapperInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var ReaderInterface[] */
    private array $productAttributeValueReaders;

    private AutoMapperInterface $autoMapper;

    public function __construct(
        #[AutowireIterator(
            tag: 'monsieurbiz.search.automapper.product_attribute_value_reader',
            defaultIndexMethod: 'getReaderCode',
        )]
        iterable $productAttributeValueReaders,
    ) {
        $this->autoMapper = AutoMapper::create();

        $this->logger = new NullLogger();

        $this->productAttributeValueReaders = $productAttributeValueReaders instanceof Traversable
            ? iterator_to_array($productAttributeValueReaders)
            : $productAttributeValueReaders;
    }

    public function supports(object $entity, string $targetClass): bool
    {
        return $entity instanceof ProductAttributeValueInterface && ProductAttributeDTO::class === $targetClass;
    }

    public function map(object $entity, string $targetClass): object
    {
        Assert::isInstanceOf(value: $entity, class: ProductAttributeValueInterface::class);

        /** @var ProductAttributeDTO $productAttributeDto */
        $productAttributeDto = $this->autoMapper->map(source: $entity, target: $targetClass);

        if ([] === $this->productAttributeValueReaders) {
            throw new \RuntimeException(message: 'Undefined product attribute value reader');
        }

        $productAttributeDto->setValue(
            value: $this->mapProductAttributeValue(productAttributeValue: $entity),
        );

        return $productAttributeDto;
    }

    public function mapProductAttributeValue(ProductAttributeValueInterface $productAttributeValue): mixed
    {
        if (null === $productAttributeValue->getType()) {
            return null;
        }

        if (!array_key_exists(key: $productAttributeValue->getType(), array: $this->productAttributeValueReaders)) {
            $this->logger->alert(
                message: sprintf(
                    'Missing product attribute value reader for "%s" type',
                    $productAttributeValue->getType(),
                ),
            );

            return null;
        }

        $reader = $this->productAttributeValueReaders[$productAttributeValue->getType()];

        return $reader->getValue(productAttribute: $productAttributeValue);
    }
}
