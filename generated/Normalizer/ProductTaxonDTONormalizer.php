<?php

namespace MonsieurBiz\SyliusSearchPlugin\Generated\Normalizer;

use MonsieurBiz\SyliusSearchPlugin\Generated\Model\ProductTaxonDTO;
use MonsieurBiz\SyliusSearchPlugin\Generated\Model\TaxonDTO;
use MonsieurBiz\SyliusSearchPlugin\Generated\Runtime\Normalizer\CheckArray;
use MonsieurBiz\SyliusSearchPlugin\Generated\Runtime\Normalizer\ValidatorTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

class ProductTaxonDTONormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;
    use CheckArray;
    use ValidatorTrait;

    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []) : bool
    {
        return $type === ProductTaxonDTO::class;
    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = []) : bool
    {
        return $data instanceof ProductTaxonDTO;
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = []) : ProductTaxonDTO
    {
        $object = new ProductTaxonDTO();

        if (null === $data || false === \is_array($data)) {
            return $object;
        }

        if (\array_key_exists('taxon', $data)) {
            $taxon = $this->denormalizer->denormalize($data['taxon'], TaxonDTO::class, 'json', $context);

            $object->setTaxon($taxon);
        }

        $object->setPosition($data['position'] ?? null);

        return $object;
    }

    public function normalize(mixed $data, string $format = null, array $context = []) : array|string|int|float|bool|\ArrayObject|null
    {
        $productTaxonDto = $data;
        Assert::isInstanceOf($productTaxonDto, ProductTaxonDTO::class);

        $normalizedData = [];

        if ($productTaxonDto->isInitialized('taxon') && null !== $productTaxonDto->getTaxon()) {
            $normalizedData['taxon'] = $this->normalizer->normalize($productTaxonDto->getTaxon(), 'json', $context);
        }

        if ($productTaxonDto->isInitialized('position') && null !== $productTaxonDto->getPosition()) {
            $normalizedData['position'] = $productTaxonDto->getPosition();
        }

        return $normalizedData;
    }

    public function getSupportedTypes(?string $format = null) : array
    {
        return [ProductTaxonDTO::class => false];
    }
}