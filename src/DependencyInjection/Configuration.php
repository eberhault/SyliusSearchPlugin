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

namespace MonsieurBiz\SyliusSearchPlugin\DependencyInjection;

use MonsieurBiz\SyliusSearchPlugin\Mapping\YamlWithLocaleProvider;
use MonsieurBiz\SyliusSearchPlugin\Model\Datasource\RepositoryDatasource;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\Documentable;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    /**
     * @inheritdoc
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(name: 'monsieur_biz_sylius_search');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
                ->arrayNode(name: 'documents')
                    ->useAttributeAsKey(name: 'code', removeKeyItem: false)
                    ->defaultValue(value: [])
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode(name: 'prefix')->defaultValue(value: null)->end()
                            ->scalarNode(name: 'document_class')->defaultValue(value: Documentable::class)->end()
                            ->scalarNode(name: 'search_enabled')->defaultValue(value: true)->end()
                            ->scalarNode(name: 'instant_search_enabled')->defaultValue(value: true)->end()
                            ->scalarNode(name: 'source')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode(name: 'target')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode(name: 'mapping_provider')->defaultValue(value: YamlWithLocaleProvider::class)->end()
                            ->scalarNode(name: 'datasource')->defaultValue(value: RepositoryDatasource::class)->end()
                            ->arrayNode(name: 'templates')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode(name: 'item')->isRequired()->cannotBeEmpty()->end()
                                    ->scalarNode(name: 'instant')->isRequired()->cannotBeEmpty()->end()
                                ->end()
                            ->end()

                            // Limits
                            ->arrayNode(name: 'limits')
                                ->performNoDeepMerging()
                                ->useAttributeAsKey(name: 'type')
                                ->defaultValue(value: ['search' => [9, 18, 27], 'taxon' => [9, 18, 27], 'instant_search' => [10]])
                                ->prototype(type: 'array')
                                ->prototype(type: 'scalar')->end()
                                ->end()
                            ->end()

                            // Position
                            ->integerNode(name: 'position')->defaultValue(value: 0)->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode(name: 'automapper_classes')
                    ->children()
                        ->arrayNode(name: 'sources')
                            ->useAttributeAsKey(name: 'code', removeKeyItem: false)
                            ->defaultValue([])
                            ->prototype(type: 'scalar')->end()
                        ->end()
                        ->arrayNode(name: 'targets')
                            ->useAttributeAsKey(name: 'code', removeKeyItem: false)
                            ->defaultValue(value: [])
                            ->prototype(type: 'scalar')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode(name: 'elastically_configuration_paths')
                    ->defaultValue(value: [])
                    ->prototype(type: 'scalar')->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
