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

use InvalidArgumentException;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\PrefixedDocumentableInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class DocumentableRegistryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(id: 'monsieurbiz.search.registry.documentable')) {
            return;
        }

        $documentables = $container->getParameter(name: 'monsieurbiz.search.config.documents');
        if (!is_array(value: $documentables)) {
            return;
        }

        // Sort documentables by position
        uasort(
            $documentables,
            static fn($documentableA, $documentableB) => $documentableA['position'] <=> $documentableB['position'],
        );

        $this->addDocumentableServices(container: $container, documentables: $documentables);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function validateDocumentableResource(string $class): void
    {
        $interfaces = (array)class_implements(object_or_class: $class);

        if (!in_array(needle: DocumentableInterface::class, haystack: $interfaces, strict: true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Class "%s" must implement "%s" to be registered as a Documentable.',
                    $class,
                    DocumentableInterface::class,
                ),
            );
        }
    }

    private function isPrefixedDocumentableClass(string $class): bool
    {
        $interfaces = (array)class_implements(object_or_class: $class);

        return in_array(needle: PrefixedDocumentableInterface::class, haystack: $interfaces, strict: true);
    }

    private function addDocumentableServices(ContainerBuilder $container, array $documentables): void
    {
        $registry = $container->getDefinition(id: 'monsieurbiz.search.registry.documentable');

        $searchSettings = [];
        if ($container->hasParameter(name: 'monsieurbiz.settings.config.plugins')) {
            /** @var array $searchSettings */
            $searchSettings = $container->getParameter(name: 'monsieurbiz.settings.config.plugins');
        }

        foreach ($documentables as $indexCode => $documentableConfiguration) {
            $documentableServiceId = sprintf('search.documentable.%s', $indexCode);

            // Create documentable service
            $this->createDocumentable(
                container: $container,
                documentableServiceId: $documentableServiceId,
                indexCode: $indexCode,
                documentableConfiguration: $documentableConfiguration,
            );

            // Add documentable into registry
            $registry->addMethodCall(
                method: 'register',
                arguments: [$documentableServiceId, new Reference(id: $documentableServiceId)],
            );

            // Add the default settings value of documentable
            $searchSettings['monsieurbiz.search']['default_values'][sprintf('search_enabled__%s', $indexCode)] = $documentableConfiguration['search_enabled'];
            $searchSettings['monsieurbiz.search']['default_values'][sprintf('instant_search_enabled__%s', $indexCode)] = $documentableConfiguration['instant_search_enabled'];
            $searchSettings['monsieurbiz.search']['default_values'][sprintf('limits__%s', $indexCode)] = $documentableConfiguration['limits'];
        }

        $container->setParameter(name: 'monsieurbiz.settings.config.plugins', value: $searchSettings);
    }

    private function createDocumentable(
        ContainerBuilder $container,
        string $documentableServiceId,
        string $indexCode,
        array $documentableConfiguration,
    ): void {
        $documentableClass = $documentableConfiguration['document_class'];

        $this->validateDocumentableResource(class: $documentableClass);

        $documentableDefinition = (new Definition(class: $documentableClass))
            ->setAutowired(autowired: true)
            ->setArguments(
                arguments: [
                    '$indexCode' => $indexCode,
                    '$sourceClass' => $documentableConfiguration['source'],
                    '$targetClass' => $documentableConfiguration['target'],
                    '$templates' => $documentableConfiguration['templates'],
                    '$limits' => $documentableConfiguration['limits'],
                ],
            );

        $documentableDefinition = $container->setDefinition(
            id: $documentableServiceId,
            definition: $documentableDefinition,
        );

        $documentableDefinition->addTag(name: 'monsieurbiz.search.documentable');

        $documentableDefinition->addMethodCall(
            method: 'setMappingProvider',
            arguments: [new Reference(id: $documentableConfiguration['mapping_provider'])],
        );

        $documentableDefinition->addMethodCall(
            method: 'setDatasource',
            arguments: [new Reference(id: $documentableConfiguration['datasource'])],
        );

        if ($this->isPrefixedDocumentableClass(class: $documentableClass) && isset($documentableConfiguration['prefix'])) {
            $documentableDefinition->addMethodCall(
                method: 'setPrefix',
                arguments: [$documentableConfiguration['prefix']],
            );
        }
    }
}
