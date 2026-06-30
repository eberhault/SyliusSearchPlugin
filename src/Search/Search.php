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

namespace MonsieurBiz\SyliusSearchPlugin\Search;

use MonsieurBiz\SyliusSearchPlugin\Exception\UnknownRequestTypeException;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestHandler;
use Pagerfanta\Elastica\ElasticaAdapter;
use Sylius\Component\Locale\Context\LocaleContextInterface;

readonly class Search implements SearchInterface
{
    public function __construct(
        private ClientFactory $clientFactory,
        private LocaleContextInterface $localeContext,
        private RequestHandler $requestHandler,
        private ResponseFactory $responseFactory
    ) {
    }

    /**
     * @throws UnknownRequestTypeException
     */
    public function search(RequestConfiguration $requestConfiguration): ResponseInterface
    {
        $request = $this->requestHandler->getRequest(requestConfiguration: $requestConfiguration);

        $documentable = $request->getDocumentable();

        $localeCode = $documentable->isTranslatable() ? $this->localeContext->getLocaleCode() : null;

        $indexName = $this->clientFactory->getIndexName(documentable: $documentable, locale: $localeCode);

        $client = $this->clientFactory->getClient(documentable: $documentable, localeCode: $localeCode);

        return $this->responseFactory->build(
            requestConfiguration: $requestConfiguration,
            adapter: new ElasticaAdapter(
                searchable: $client->getIndex($indexName),
                query: $request->getQuery(),
            ),
            documentable: $request->getDocumentable(),
        );
    }
}
