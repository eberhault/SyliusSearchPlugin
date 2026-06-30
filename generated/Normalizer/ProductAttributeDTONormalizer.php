<?php

namespace MonsieurBiz\SyliusSearchPlugin\Generated\Normalizer;

use MonsieurBiz\SyliusSearchPlugin\Generated\Model\ProductAttributeDTO;
use MonsieurBiz\SyliusSearchPlugin\Generated\Runtime\Normalizer\CheckArray;
use MonsieurBiz\SyliusSearchPlugin\Generated\Runtime\Normalizer\ValidatorTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

class ProductAttributeDTONormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;
    use CheckArray;
    use ValidatorTrait;

    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []) : bool
    {
        return $type === ProductAttributeDTO::class;
    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = []) : bool
    {
        return $data instanceof ProductAttributeDTO;
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = []) : ProductAttributeDTO
    {
        $object = new ProductAttributeDTO();

        if (null === $data || false === \is_array($data)) {
            return $object;
        }

        $object
            ->setCode($data['code'] ?? null)
            ->setName($data['name'] ?? null)
            ->setValue($data['value'] ?? null)
        ;

        return $object;
    }
    public function normalize(mixed $data, string $format = null, array $context = []) : array|string|int|float|bool|\ArrayObject|null
    {
        $productAttributeDto = $data;
        Assert::isInstanceOf($productAttributeDto, ProductAttributeDTO::class);

        $normalizedData = [];
        if ($productAttributeDto->isInitialized('code') && null !== $productAttributeDto->getCode()) {
            $normalizedData['code'] = $productAttributeDto->getCode();
        }

        if ($productAttributeDto->isInitialized('name') && null !== $productAttributeDto->getName()) {
            $normalizedData['name'] = $productAttributeDto->getName();
        }

        if ($productAttributeDto->isInitialized('value') && null !== $productAttributeDto->getValue()) {
            $normalizedData['value'] = $productAttributeDto->getValue();
        }

        return $normalizedData;
    }

    public function getSupportedTypes(?string $format = null) : array
    {
        return [ProductAttributeDTO::class => false];
    }
}