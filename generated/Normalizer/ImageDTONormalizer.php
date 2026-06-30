<?php

namespace MonsieurBiz\SyliusSearchPlugin\Generated\Normalizer;

use Jane\Component\JsonSchemaRuntime\Reference;
use MonsieurBiz\SyliusSearchPlugin\Generated\Model\ImageDTO;
use MonsieurBiz\SyliusSearchPlugin\Generated\Runtime\Normalizer\CheckArray;
use MonsieurBiz\SyliusSearchPlugin\Generated\Runtime\Normalizer\ValidatorTrait;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\HttpKernel\Kernel;
use Webmozart\Assert\Assert;

class ImageDTONormalizer implements DenormalizerInterface, NormalizerInterface, DenormalizerAwareInterface, NormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    use NormalizerAwareTrait;
    use CheckArray;
    use ValidatorTrait;

    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []) : bool
    {
        return $type === ImageDTO::class;
    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = []) : bool
    {
        return $data instanceof ImageDTO;
    }

    public function denormalize(mixed $data, string $type, string $format = null, array $context = []) : ImageDTO
    {
        $object = new ImageDTO();

        if (null === $data || false === \is_array($data)) {
            return $object;
        }

        $object
            ->setPath($data['path'] ?? null)
            ->setType($data['type'] ?? null);

        return $object;
    }
    public function normalize(mixed $data, string $format = null, array $context = []) : array|string|int|float|bool|\ArrayObject|null
    {
        $imageDto = $data;
        Assert::isInstanceOf($imageDto, ImageDTO::class);

        $normalizedData = [];

        if ($imageDto->isInitialized('path') && null !== $imageDto->getPath()) {
            $normalizedData['path'] = $imageDto->getPath();
        }

        if ($imageDto->isInitialized('type') && null !== $imageDto->getType()) {
            $normalizedData['type'] = $imageDto->getType();
        }

        return $normalizedData;
    }
    public function getSupportedTypes(?string $format = null) : array
    {
        return [ImageDTO::class => false];
    }
}