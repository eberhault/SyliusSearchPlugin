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

namespace MonsieurBiz\SyliusSearchPlugin\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\UnitOfWork;
use MonsieurBiz\SyliusSearchPlugin\Manager\AutomaticReindexManagerInterface;
use MonsieurBiz\SyliusSearchPlugin\Message\ProductReindexFromTaxonId;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Sylius\Component\Core\Model\ProductTaxonInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * This event subscriber only manages product taxons modifications.
 * For the other entities, we use the event listener and the event sylius (pre/post).
 */
class ReindexProductEventSubscriber implements EventSubscriberInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly AutomaticReindexManagerInterface $automaticReindexManager,
    ) {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::onFlush => 'onFlush',
        ];
    }

    public function onFlush(OnFlushEventArgs $eventArgs): void
    {
        if (!$this->automaticReindexManager->shouldBeAutomaticallyReindex()) {
            return;
        }

        $eventArgs->getObjectManager()->getEventManager()->removeEventListener(events: Events::onFlush, listener: $this);

        $unitOfWork = $eventArgs->getObjectManager()->getUnitOfWork();

        $this->manageUnitOfWork(unitOfWork: $unitOfWork);
    }

    private function manageUnitOfWork(UnitOfWork $unitOfWork): void
    {
        $entities = array_merge($unitOfWork->getScheduledEntityInsertions(), $unitOfWork->getScheduledEntityUpdates());

        foreach ($entities as $entity) {
            if (!$entity instanceof ProductTaxonInterface) {
                continue;
            }

            $taxon = $entity->getTaxon();

            if (!$taxon instanceof TaxonInterface) {
                continue;
            }

            $this->messageBus->dispatch(new ProductReindexFromTaxonId($taxon->getId()));
        }
    }
}
