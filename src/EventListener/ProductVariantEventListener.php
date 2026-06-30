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

namespace MonsieurBiz\SyliusSearchPlugin\EventListener;

use MonsieurBiz\SyliusSearchPlugin\Message\ProductReindexFromIds;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Messenger\MessageBusInterface;
use Webmozart\Assert\Assert;

final class ProductVariantEventListener
{
    private array $productIdsToReindex = [];

    public function __construct(
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function dispatchProductVariantReindexMessage(GenericEvent $event): void
    {
        $variant = $event->getSubject();
        Assert::isInstanceOf(value: $variant, class: ProductVariantInterface::class);

        $product = $variant->getProduct();

        if (!$product instanceof ProductInterface) {
            return;
        }

        $productReindexFromIdsMessage = new ProductReindexFromIds();
        $productReindexFromIdsMessage->addProductId(productIds: $product->getId());

        $this->messageBus->dispatch($productReindexFromIdsMessage);
    }

    public function saveProductIdToDispatchReindexMessage(GenericEvent $event): void
    {
        $variant = $event->getSubject();
        Assert::isInstanceOf(value: $variant, class: ProductVariantInterface::class);

        $product = $variant->getProduct();

        if (!$product instanceof ProductInterface) {
            return;
        }

        $this->productIdsToReindex[] = $product->getId();
    }

    public function dispatchProductReindexMessage(GenericEvent $event): void
    {
        $variant = $event->getSubject();
        Assert::isInstanceOf(value: $variant, class: ProductVariantInterface::class);

        if ([] === $this->productIdsToReindex) {
            return;
        }

        $productReindexFromIdsMessage = new ProductReindexFromIds();

        foreach ($this->productIdsToReindex as $productIdToReindex) {
            $productReindexFromIdsMessage->addProductId(productIds: $productIdToReindex);
        }

        $this->productIdsToReindex = [];

        $this->messageBus->dispatch($productReindexFromIdsMessage);
    }
}
