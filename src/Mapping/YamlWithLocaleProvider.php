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

namespace MonsieurBiz\SyliusSearchPlugin\Mapping;

use ArrayObject;
use Elastica\Exception\InvalidException;
use JoliCode\Elastically\Mapping\MappingProviderInterface;
use MonsieurBiz\SyliusSearchPlugin\Event\MappingProviderEvent;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Parser;

class YamlWithLocaleProvider implements MappingProviderInterface
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly FileLocatorInterface $fileLocator,
        private readonly iterable $configurationDirectories = [],
        private ?Parser $parser = null
    ) {
        $this->parser = $parser ?? new Parser();
    }

    public function provideMapping(string $indexName, array $context = []): ?array
    {
        $mapping = [];

        $locale = $context['locale'] ?? null;

        foreach ($this->configurationDirectories as $configurationDirectory) {
            $configurationDirectory = $this->fileLocator->locate($configurationDirectory);

            if (!is_string($configurationDirectory)) {
                continue;
            }

            $mapping = $this->appendMapping(
                configurationDirectory: $configurationDirectory,
                mapping: $mapping,
                indexName: $indexName,
                context: $context,
            );

            $mapping = $this->appendLocaleAnalyzers(
                configurationDirectory: $configurationDirectory,
                mapping: $mapping,
                locale: $locale,
            );
        }

        $mappingProviderEvent = new MappingProviderEvent(
            indexCode: $context['index_code'] ?? $indexName,
            mapping: new ArrayObject($mapping),
            context: $context,
        );

        $this->eventDispatcher->dispatch(
            event: $mappingProviderEvent,
            eventName: MappingProviderEvent::EVENT_NAME,
        );

        $mapping = (array)$mappingProviderEvent->getMapping();

        if (empty($mapping['mappings'] ?? [])) {
            throw new InvalidException(
                sprintf('Mapping no found for "%s" not found. Please check your configuration.', $indexName),
            );
        }

        return $mapping;
    }

    private function appendMapping(string $configurationDirectory, array $mapping, string $indexName, array $context): array
    {
        try {
            $indexName = $context['index_code'] ?? $indexName;

            $fileName = $context['filename'] ?? sprintf('%s_mapping.yaml', $indexName);

            $mappingFilePath = sprintf(
                '%s%s%s',
                $configurationDirectory,
                DIRECTORY_SEPARATOR,
                $fileName,
            );

            /** @var array $parsedMapping */
            $parsedMapping = $this->parser->parseFile(filename: $mappingFilePath) ?? [];

            $mapping = array_merge_recursive($mapping, $parsedMapping);
        } catch (ParseException) {
            // the mapping yaml file does not exist.
        }

        return $mapping;
    }

    private function appendLocaleAnalyzers(string $configurationDirectory, array $mapping, ?string $locale): array
    {
        $filePath = sprintf('%s%sanalyzers.yaml', $configurationDirectory, DIRECTORY_SEPARATOR);

        $mapping = $this->appendAnalyzers(analyzerFilePath: $filePath, mapping: $mapping);

        if (null === $locale) {
            return $mapping;
        }

        foreach ($this->getLocaleCode(locale: $locale) as $localeCode) {
            $filePath = sprintf(
                '%s%sanalyzers%s.yaml',
                $configurationDirectory,
                DIRECTORY_SEPARATOR,
                $localeCode,
            );

            $mapping = $this->appendAnalyzers(
                analyzerFilePath: $filePath,
                mapping: $mapping,
            );
        }

        return $mapping;
    }

    private function appendAnalyzers(string $analyzerFilePath, array $mapping): array
    {
        try {
            /** @var array $analyzer */
            $analyzer = $this->parser->parseFile($analyzerFilePath) ?? [];

            $mapping['settings']['analysis'] = array_merge_recursive(
                $mapping['settings']['analysis'] ?? [],
                $analyzer,
            );
        } catch (ParseException) {
            // the yaml file does not exist or does not exist.
        }

        return $mapping;
    }

    private function getLocaleCode(string $locale): array
    {
        return array_unique(
            array: [
                current(array: explode(separator: '_', string: $locale)),
                $locale,
            ],
        );
    }
}
