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

namespace MonsieurBiz\SyliusSearchPlugin\Command;

use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Model\Product\ProductDTO;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Search;
use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Webmozart\Assert\Assert;

#[AsCommand(name: 'monsieurbiz:search:search')]
class SearchCommand extends Command
{
    private const string DEFAULT_CHANNEL_CODE = 'FASHION_WEB';

    public function __construct(
        private readonly Search $search,
        private readonly RequestStack $requestStack,
        private readonly ChannelContextInterface $channelContext,
        private readonly SettingsInterface $searchSettings,
        private readonly ServiceRegistryInterface $documentableRegistry,
    ) {
        parent::__construct();
    }


    protected function configure(): void
    {
        parent::configure();
        $this->addArgument('query', InputArgument::REQUIRED, 'Search query');
        $this->addOption('channel', 'c', InputOption::VALUE_OPTIONAL, 'Channel code', 'FASHION_WEB');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle(input: $input, output: $output);

        $query = $input->getArgument(name: 'query');

        $channel = $input->getOption(name: 'channel');

        $request = new Request(query: ['query' => $query, '_channel_code' => $channel]);

        $this->requestStack->push(request: $request);

        $documentable = $this->documentableRegistry->get(identifier: 'search.documentable.monsieurbiz_product');
        Assert::isInstanceOf(value: $documentable, class: DocumentableInterface::class);

        $requestConfiguration = new RequestConfiguration(
            request: $request,
            type: RequestInterface::SEARCH_TYPE,
            documentable: $documentable,
            searchSettings: $this->searchSettings,
            channelContext: $this->channelContext,
        );

        $result = $this->search->search(requestConfiguration: $requestConfiguration);

        $io->title(message: sprintf('Search result for: %s', $query));
        $io->section(message: sprintf('Nb results: %s', $result->count()));

        $documents = [];
        foreach ($result->getIterator() as $resultItem) {
            /** @var ProductDTO $productDTO */
            $productDTO = $resultItem->getModel();
            $documents[] = [$resultItem->getScore(), $productDTO->getData(name: 'id')];
        }

        $io->table(['Score', 'Document ID'], $documents);

        return Command::SUCCESS;
    }
}
