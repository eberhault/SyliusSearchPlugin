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

namespace MonsieurBiz\SyliusSearchPlugin\Repository;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

readonly class ProductOptionRepository implements ProductOptionRepositoryInterface
{
    public function __construct(
        private EntityRepository $productOptionRepository,
    ) {
    }

    public function findIsSearchableOrFilterable(): array
    {
        $queryBuilder = $this->productOptionRepository->createQueryBuilder(alias: 'product_option');

        return $queryBuilder
            ->innerJoin(
                join: 'product_option.translations',
                alias: 'product_option_translations',
            )
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq(x: 'product_option.searchable', y: ':searchable'),
                    $queryBuilder->expr()->eq(x: 'product_option.filterable', y: ':filterable')
                )
            )
            ->setParameter(key: 'searchable', value: true)
            ->setParameter(key: 'filterable', value: true)
            ->getQuery()
            ->getResult();
    }
}
