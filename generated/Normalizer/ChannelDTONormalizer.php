<?php

namespace MonsieurBiz\SyliusSearchPlugin\Generated\Normalizer;

use MonsieurBiz\SyliusSearchPlugin\Generated\Model\ChannelDTO;
use MonsieurBiz\SyliusSearchPlugin\Generated\Runtime\Normalizer\CheckArray;
use MonsieurBiz\SyliusSearchPlugin\Generated\Runtime\Normalizer\ValidatorTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

class ChannelDTONormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;
    use CheckArray;
    use ValidatorTrait;

    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []) : bool
    {
        return $type === ChannelDTO::class;
    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = []) : bool
    {
        return $data instanceof ChannelDTO;
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = []) : ChannelDTO
    {
        $object = new ChannelDTO();
        if (null === $data || false === \is_array($data)) {
            return $object;
        }

        if (\array_key_exists('code', $data)) {
            $object->setCode($data['code']);
        }

        return $object;
    }

    public function normalize(mixed $data, string $format = null, array $context = []) : array|string|int|float|bool|\ArrayObject|null
    {
        $channelDto = $data;
        Assert::isInstanceOf($channelDto, ChannelDTO::class);

        $normalizedData = [];

        if ($channelDto->isInitialized('code') && null !== $channelDto->getCode()) {
            $normalizedData['code'] = $channelDto->getCode();
        }

        return $normalizedData;
    }

    public function getSupportedTypes(?string $format = null) : array
    {
        return [ChannelDTO::class => false];
    }
}