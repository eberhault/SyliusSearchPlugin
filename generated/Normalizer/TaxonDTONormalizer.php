<?php

namespace MonsieurBiz\SyliusSearchPlugin\Generated\Normalizer;

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

class TaxonDTONormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;
    use CheckArray;
    use ValidatorTrait;

    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []) : bool
    {
        return $type === TaxonDTO::class;
    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = []) : bool
    {
        return $data instanceof TaxonDTO;
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = []) : TaxonDTO
    {
        $object = new TaxonDTO();

        if (null === $data || false === \is_array($data)) {
            return $object;
        }

        $object
            ->setName($data['name'] ?? null)
            ->setCode($data['code'] ?? null)
            ->setPosition($data['position'] ?? null)
            ->setLevel($data['level'] ?? null)
        ;

        return $object;
    }
    public function normalize(mixed $data, string $format = null, array $context = []) : array|string|int|float|bool|\ArrayObject|null
    {
        $taxonDto = $data;
        Assert::isInstanceOf($taxonDto, TaxonDTO::class);

        $normalizedData = [];

        if ($taxonDto->isInitialized('name') && null !== $taxonDto->getName()) {
            $normalizedData['name'] = $taxonDto->getName();
        }

        if ($taxonDto->isInitialized('code') && null !== $taxonDto->getCode()) {
            $normalizedData['code'] = $taxonDto->getCode();
        }

        if ($taxonDto->isInitialized('position') && null !== $taxonDto->getPosition()) {
            $normalizedData['position'] = $taxonDto->getPosition();
        }

        if ($taxonDto->isInitialized('level') && null !== $taxonDto->getLevel()) {
            $normalizedData['level'] = $taxonDto->getLevel();
        }

        return $normalizedData;
    }

    public function getSupportedTypes(?string $format = null) : array
    {
        return [TaxonDTO::class => false];
    }
}