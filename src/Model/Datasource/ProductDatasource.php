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

namespace MonsieurBiz\SyliusSearchPlugin\Model\Datasource;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Webmozart\Assert\Assert;

use function Doctrine\ORM\QueryBuilder;

readonly class ProductDatasource implements DatasourceInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function getItems(string $sourceClass): iterable
    {
        $repository = $this->entityManager->getRepository($sourceClass);

        /** @var ProductRepositoryInterface&EntityRepository $repository */
        Assert::isInstanceOf(value: $repository, class: ProductRepositoryInterface::class);

        $queryBuilder = $repository->createQueryBuilder(alias: 'o');


        $queryBuilder
            ->where(
                $queryBuilder->expr()->eq('o.enabled', ':enabled')
            )
            ->andWhere('o.channels IS NOT EMPTY')
            ->setParameter('enabled', true);

        $paginator = new Pagerfanta(
            adapter: new QueryAdapter(
                query: $queryBuilder,
                fetchJoinCollection: false,
                useOutputWalkers: false,
            ),
        );

        $paginator->setMaxPerPage(maxPerPage: self::DEFAULT_MAX_PER_PAGE);

        $page = 1;
        do {
            $paginator->setCurrentPage(currentPage: $page);

            foreach ($paginator->getIterator() as $item) {
                yield $item;
            }

            $page = $paginator->hasNextPage() ? $paginator->getNextPage() : 1;
        } while ($paginator->hasNextPage());

        return null;
    }
}
