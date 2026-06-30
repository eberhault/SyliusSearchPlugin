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

namespace MonsieurBiz\SyliusSearchPlugin\Index;

use Doctrine\Common\Proxy\Proxy;
use Doctrine\ORM\EntityManagerInterface;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use JoliCode\Elastically\Indexer as ElasticallyIndexer;
use JoliCode\Elastically\Model\Document;
use MonsieurBiz\SyliusSearchPlugin\AutoMapper\Mapper\Mapper;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\PrefixedDocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\ClientFactory;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Locale\Model\LocaleInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use TypeError;

final class Indexer implements IndexerInterface
{
    private array $locales = [];

    public function __construct(
        private readonly ServiceRegistryInterface $documentableRegistry,
        private readonly ChannelRepositoryInterface $channelRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ClientFactory $clientFactory,
        private readonly Mapper $mapper,
    ) {
    }

    /**
     * Index all documentable object.
     */
    public function indexAll(?OutputInterface $output = null): void
    {
        $output = $output ?? new NullOutput();

        /** @var DocumentableInterface $documentable */
        foreach ($this->documentableRegistry->all() as $documentable) {
            $documentable instanceof PrefixedDocumentableInterface && [] !== $documentable->getPrefix() ?
                $output->writeln(\sprintf('Indexing <info>%s</info> (Prefix: <info>%s</info>)', $documentable->getIndexCode(), $documentable->getPrefix())) :
                $output->writeln(\sprintf('Indexing <info>%s</info>', $documentable->getIndexCode()));

            $this->indexDocumentable(output: $output, documentable: $documentable);
        }
    }

    /**
     * @throws ClientResponseException
     * @throws ExceptionInterface
     * @throws ServerResponseException
     * @throws MissingParameterException
     */
    public function indexByDocuments(DocumentableInterface $documentable, array $documents, ?string $locale = null, ?ElasticallyIndexer $indexer = null): void
    {
        if (!$indexer instanceof ElasticallyIndexer) {
            $indexer = $this->clientFactory->getIndexer(documentable: $documentable, localeCode: $locale);
        }

        if (null === $locale && $documentable->isTranslatable()) {
            foreach ($this->getLocales() as $localeCode) {
                $this->indexByDocuments(
                    documentable: $documentable,
                    documents: $documents,
                    locale: $localeCode,
                    indexer: $indexer,
                );
            }

            return;
        }

        $index = $this->clientFactory->getIndex(documentable: $documentable, locale: $locale);

        foreach ($documents as $document) {
            if (null !== $locale && $document instanceof TranslatableInterface) {
                $document->setCurrentLocale($locale);
            }

            $dto = $this->mapper->map($document, $documentable->getTargetClass());

            $indexer->scheduleIndex(
                index: $index,
                document: new Document(id: (string)$dto->getId(), data: $dto),
            );
        }

        $indexer->flush();
    }

    /**
     * @throws ClientResponseException
     * @throws ServerResponseException
     * @throws MissingParameterException
     */
    public function deleteByDocumentIds(
        DocumentableInterface $documentable,
        array $documentsIds,
        ?string $locale = null,
        ?ElasticallyIndexer $indexer = null,
    ): void {
        if (null === $indexer) {
            $indexer = $this->clientFactory->getIndexer(documentable: $documentable, localeCode: $locale);
        }

        if (null === $locale && $documentable->isTranslatable()) {
            foreach ($this->getLocales() as $localeCode) {
                $this->deleteByDocumentIds(
                    documentable: $documentable,
                    documentsIds: $documentsIds,
                    locale: $localeCode,
                    indexer: $indexer,
                );
            }

            return;
        }

        $index = $this->clientFactory->getIndex(documentable: $documentable, locale: $locale);

        foreach ($documentsIds as $documentsId) {
            $indexer->scheduleDelete(index: $index, id: (string)$documentsId);
        }

        $indexer->flush();
    }

    /**
     * Retrieve all used locales.
     */
    private function getLocales(): array
    {
        if (0 === count($this->locales)) {
            $enabledChannels = $this->channelRepository->findBy(criteria: ['enabled' => true]);

            /** @var ChannelInterface $channel */
            foreach ($enabledChannels as $channel) {
                $channelLocales = $channel->getLocales()
                    ->map(func: fn(LocaleInterface $locale): string => $locale->getCode() ?? '')
                    ->toArray();

                $this->locales = array_merge(
                    $this->locales,
                    $channelLocales,
                );
            }

            $this->locales = array_unique(array: array_filter(array: $this->locales));
        }

        return $this->locales;
    }

    /**
     * @throws \Elastica\Exception\ExceptionInterface
     * @throws ClientResponseException
     * @throws ExceptionInterface
     * @throws MissingParameterException
     * @throws ServerResponseException
     */
    private function indexDocumentable(OutputInterface $output, DocumentableInterface $documentable, ?string $locale = null): void
    {
        if (null === $locale && $documentable->isTranslatable()) {
            foreach ($this->getLocales() as $localeCode) {
                $documentable instanceof PrefixedDocumentableInterface && [] !== $documentable->getPrefix() ?
                    $output->writeln(
                        \sprintf('Indexing <info>%s</info> for locale <info>%s</info> (Prefix: <info>%s</info>)', $documentable->getIndexCode(), $localeCode, $documentable->getPrefix()),
                        OutputInterface::VERBOSITY_VERBOSE
                    ) :
                    $output->writeln(
                        \sprintf('Indexing <info>%s</info> for locale <info>%s</info>', $documentable->getIndexCode(), $localeCode),
                        OutputInterface::VERBOSITY_VERBOSE
                    );

                $this->indexDocumentable(output: $output, documentable: $documentable, locale: $localeCode);
            }

            return;
        }

        $indexName = $this->clientFactory->getIndexName(documentable: $documentable, locale: $locale);

        $indexBuilder = $this->clientFactory->getIndexBuilder(documentable: $documentable, localeCode: $locale);

        $newIndex = $indexBuilder->createIndex(
            indexName: $indexName,
            context: [
                'index_code' => $documentable->getIndexCode(),
                'locale' => null !== $locale ? strtolower(string: $locale) : null,
            ],
        );

        $indexer = $this->clientFactory->getIndexer(documentable: $documentable, localeCode: $locale);

        $items = $documentable->getDatasource()
            ->getItems(sourceClass: $documentable->getSourceClass());

        foreach ($items as $item) {
            /** @var object $item */
            $item = $this->getRealEntity(entity: $item);

            if (null !== $locale && $item instanceof TranslatableInterface) {
                $item->setCurrentLocale($locale);
            }

            try {
                $dto = $this->mapper->map(entity: $item, targetClass: $documentable->getTargetClass());
            } catch (TypeError $e) {
                $id = method_exists(object_or_class: $item, method: 'getId') ? $item->getId() : 'unknown';

                $output->writeln(\sprintf('Error while mapping %s (id: %s): %s', $item::class, $id, $e->getMessage()));

                continue;
            }

            $indexer->scheduleIndex(
                index: $newIndex,
                document: new Document(id: (string)$dto->getId(), model: $dto),
            );
        }

        $indexer->flush();

        $indexBuilder->markAsLive(index: $newIndex, indexName: $indexName);

        $output->writeln(
            sprintf('Index <info>%s</info> is now live', $indexName),
            OutputInterface::VERBOSITY_VERBOSE,
        );

        $indexBuilder->speedUpRefresh(index: $newIndex);

        $indexBuilder->purgeOldIndices(indexName: $indexName);

        $output->writeln(
            sprintf('Old indices for <info>%s</info> are now purged', $indexName),
            OutputInterface::VERBOSITY_VERBOSE,
        );
    }

    /**
     * Convert proxies classes to the entity one.
     *
     * This avoid to retrieve the incorrect Mapper and have errors like :
     * `index: /<INDEX_NAME>/_doc/<ID> caused failed to parse`
     */
    private function getRealEntity(mixed $entity): mixed
    {
        if (!$entity instanceof Proxy || !method_exists(object_or_class: $entity, method: 'getId')) {
            return $entity;
        }

        $this->entityManager->clear();

        $entityClassName = $this->entityManager->getClassMetadata(className: $entity::class)->rootEntityName;

        return $this->entityManager->find(className: $entityClassName, id: $entity->getId());
    }
}
