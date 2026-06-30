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

readonly class ProductAttributeRepository implements ProductAttributeRepositoryInterface
{
    public function __construct(
        private EntityRepository $attributeRepository,
    ) {
    }

    public function findIsSearchableOrFilterable(): array
    {
        $queryBuilder = $this->attributeRepository->createQueryBuilder(alias: 'product_attribute');

        return $queryBuilder
            ->innerJoin(
                join: 'product_attribute.translations',
                alias: 'product_attribute_translations',
            )
            ->where(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq(x: 'product_attribute.searchable', y: ':searchable'),
                    $queryBuilder->expr()->eq(x: 'product_attribute.filterable', y: ':filterable')
                )
            )
            ->setParameter(key: 'searchable', value: true)
            ->setParameter(key: 'filterable', value: true)
            ->getQuery()
            ->getResult();
    }
}
