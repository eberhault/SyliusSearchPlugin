<?php

/*
 * This file is part of Monsieur Biz' Search plugin for Sylius.
 *
 * (c) Monsieur Biz <sylius@monsieurbiz.com>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace MonsieurBiz\SyliusSearchPlugin\Model\Product;

use Jacquesbh\Eater\Eater;

class ProductDTO extends Eater
{
    public function getImagesByType(string $type): array
    {
        $images = $this->getData(name: 'images') ?? [];

        if (!is_array(value: $images)) {
            return [];
        }

        return array_filter(
            array: $images,
            callback: fn($image): bool => is_object(value: $image) &&
                method_exists(object_or_class: $image, method: 'getType') &&
                $type === $image->getType(),
        );
    }
}
