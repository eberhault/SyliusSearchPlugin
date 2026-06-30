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

use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Response\FilterBuilders\FilterBuilderInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class MonsieurBizSyliusSearchExtension extends Extension
{
    public const string EXTENSION_CONFIG_NAME = 'monsieurbiz.search.config';

    /**
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(
            configuration: $this->getConfiguration([], $container),
            configs: $configs,
        );

        foreach ($config as $name => $value) {
            $container->setParameter(
                name: sprintf('%s.%s', self::EXTENSION_CONFIG_NAME, $name),
                value: $value,
            );

            if ('documents' === $name) {
                $this->addDocumentsConfiguration(
                    name: sprintf('%s.%s', self::EXTENSION_CONFIG_NAME, $name),
                    values: $value,
                    container: $container,
                );
            }
        }


        $loader = new YamlFileLoader(
            container: $container,
            locator: new FileLocator(paths: sprintf('%s/../Resources/config', __DIR__)),
        );
        $loader->load(resource: 'services.yaml');

        $container->registerForAutoconfiguration(interface: RequestInterface::class)
            ->addTag(name: 'monsieurbiz.search.request');

        $container->registerForAutoconfiguration(interface: FilterBuilderInterface::class)
            ->addTag(name: 'monsieurbiz.search.response.filter_builder');
    }

    /**
     * @inheritdoc
     */
    public function getAlias(): string
    {
        return str_replace(search: ['monsieur_biz'], replace: ['monsieurbiz'], subject: parent::getAlias());
    }

    private function addDocumentsConfiguration(string $name, array $values, ContainerBuilder $container): void
    {
        foreach ($values as $documentIndexName => $documentValues) {
            $this->addDocumentConfiguration(
                name: sprintf('%s.%s', $name, $documentIndexName),
                values: $documentValues,
                container: $container,
            );
        }
    }

    private function addDocumentConfiguration(string $name, array $values, ContainerBuilder $container): void
    {
        foreach ($values as $configName => $configValue) {
            $container->setParameter(
                name: sprintf('%s.%s', $name, $configName),
                value: $configValue,
            );
        }
    }
}
