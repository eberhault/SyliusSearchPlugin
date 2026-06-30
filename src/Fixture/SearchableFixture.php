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

namespace MonsieurBiz\SyliusSearchPlugin\Fixture;

use Doctrine\ORM\EntityManagerInterface;
use MonsieurBiz\SyliusSearchPlugin\Fixture\Factory\SearchableFixtureFactoryInterface;
use Sylius\Bundle\CoreBundle\Fixture\AbstractResourceFixture;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

class SearchableFixture extends AbstractResourceFixture implements SearchableFixtureInterface
{
    public function __construct(
        EntityManagerInterface $productManager,
        SearchableFixtureFactoryInterface $exampleFactory
    ) {
        parent::__construct($productManager, $exampleFactory);
    }

    public function getName(): string
    {
        return 'monsieurbiz_sylius_search';
    }

    protected function configureResourceNode(ArrayNodeDefinition $resourceNode): void
    {
        $resourceNode
            ->children()
            ->scalarNode(name: 'attribute')->end()
            ->scalarNode(name: 'option')->end()
            ->booleanNode(name: 'filterable')->defaultValue(value: true)->end()
            ->booleanNode(name: 'searchable')->defaultValue(value: true)->end()
            ->integerNode(name: 'search_weight')->defaultValue(value: 1)->end()
        ;
    }
}
