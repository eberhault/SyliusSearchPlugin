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
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\OutOfBoundsException;

class AutowireMappingProviderParameterPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(id: YamlWithLocaleProvider::class)) {
            return;
        }

        $yamlMappingProvider = $container->getDefinition(id: YamlWithLocaleProvider::class);
        $decoratedYamlMappingProvider = $container->getDefinition(id: YamlWithLocaleProvider::class);

        try {
            $decoratedYamlMappingProvider->setArgument(
                key: '$configurationDirectory',
                value: $yamlMappingProvider->getArgument(index: '$configurationDirectory')
            );
        } catch (OutOfBoundsException) {
            // yaml provider service has no configuration directory argument
        }
    }
}
