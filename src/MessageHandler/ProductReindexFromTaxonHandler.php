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

namespace MonsieurBiz\SyliusSearchPlugin\MessageHandler;

use Doctrine\ORM\EntityRepository;
use MonsieurBiz\SyliusSearchPlugin\Index\IndexerInterface;
use MonsieurBiz\SyliusSearchPlugin\Message\ProductReindexFromTaxonId;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

readonly class ProductReindexFromTaxonHandler implements MessageHandlerInterface
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private IndexerInterface $indexer,
        private ServiceRegistryInterface $documentableRegistry
    ) {
    }

    public function __invoke(ProductReindexFromTaxonId $message): void
    {
        /** @var DocumentableInterface $documentable */
        $documentable = $this->documentableRegistry->get(identifier: 'search.documentable.monsieurbiz_product');

        if (!$this->productRepository instanceof EntityRepository) {
            return;
        }

        $queryBuilder = $this->productRepository->createQueryBuilder(alias: 'o');

        /** @var array $products */
        $products = $queryBuilder
            ->innerJoin(
                join: 'o.productTaxons',
                alias: 'productTaxon',
            )
            ->where(
                $queryBuilder->expr()->eq('productTaxon.taxon', ':taxonId'),
            )
            ->setParameter(key: 'taxonId', value: $message->getTaxonId())
            ->getQuery()
            ->getResult();

        $this->indexer->indexByDocuments(
            documentable: $documentable,
            documents: $products,
        );
    }
}
