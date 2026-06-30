<?php

namespace MonsieurBiz\SyliusSearchPlugin\Generated\Normalizer;

use MonsieurBiz\SyliusSearchPlugin\Generated\Model\PricingDTO;
use MonsieurBiz\SyliusSearchPlugin\Generated\Runtime\Normalizer\CheckArray;
use MonsieurBiz\SyliusSearchPlugin\Generated\Runtime\Normalizer\ValidatorTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

class PricingDTONormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;
    use CheckArray;
    use ValidatorTrait;

    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []) : bool
    {
        return $type === PricingDTO::class;
    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = []) : bool
    {
        return $data instanceof PricingDTO;
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = []) : PricingDTO
    {
        $object = new PricingDTO();

        if (null === $data || false === \is_array($data)) {
            return $object;
        }

        $object
            ->setChannelCode($data['channel_code'] ?? null)
            ->setPrice($data['price'] ?? null)
            ->setOriginalPrice($data['original_price'] ?? null)
            ->setPriceReduced($data['price_reduced'] ?? null)
        ;

        return $object;
    }
    public function normalize(mixed $data, string $format = null, array $context = []) : array|string|int|float|bool|\ArrayObject|null
    {
        $pricingDto = $data;
        Assert::isInstanceOf($pricingDto, PricingDTO::class);

        $normalizedData = [];
        if ($pricingDto->isInitialized('channelCode') && null !== $pricingDto->getChannelCode()) {
            $normalizedData['channel_code'] = $pricingDto->getChannelCode();
        }

        if ($pricingDto->isInitialized('price') && null !== $pricingDto->getPrice()) {
            $normalizedData['price'] = $pricingDto->getPrice();
        }

        if ($pricingDto->isInitialized('originalPrice') && null !== $pricingDto->getOriginalPrice()) {
            $normalizedData['original_price'] = $pricingDto->getOriginalPrice();
        }

        if ($pricingDto->isInitialized('priceReduced') && null !== $pricingDto->getPriceReduced()) {
            $normalizedData['price_reduced'] = $pricingDto->getPriceReduced();
        }

        return $normalizedData;
    }
    public function getSupportedTypes(?string $format = null) : array
    {
        return [PricingDTO::class => false];
    }
}