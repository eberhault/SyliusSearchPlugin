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

namespace MonsieurBiz\SyliusSearchPlugin\Controller;

use MonsieurBiz\SyliusSearchPlugin\Exception\UnknownRequestTypeException;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Search;
use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;
use Sylius\Bundle\ResourceBundle\Controller\Parameters;
use Sylius\Bundle\ResourceBundle\Controller\ParametersParserInterface;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Currency\Context\CurrencyContextInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Registry\NonExistingServiceException;
use Sylius\Component\Registry\ServiceRegistryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Intl\Currencies;

class SearchController extends AbstractController
{
    public function __construct(
        private readonly Search $search,
        private readonly CurrencyContextInterface $currencyContext,
        private readonly LocaleContextInterface $localeContext,
        private readonly ChannelContextInterface $channelContext,
        private readonly SettingsInterface $searchSettings,
        private readonly ServiceRegistryInterface $documentableRegistry,
        private readonly ParametersParserInterface $parametersParser
    ) {
    }

    public function searchAction(
        Request $request,
        string $query
    ): Response {
        $documentType = ((string)$request->query->get('document_type')) ?: null;

        $documentable = $this->getDocumentable($documentType);

        $requestConfiguration = new RequestConfiguration(
            request: $request,
            type: RequestInterface::SEARCH_TYPE,
            documentable: $documentable,
            searchSettings: $this->searchSettings,
            channelContext: $this->channelContext,
        );

        $result = $this->search->search(requestConfiguration: $requestConfiguration);

        return $this->render(
            view: '@MonsieurBizSyliusSearchPlugin/Search/result.html.twig',
            parameters: [
                'documentableRegistries' => $this->getSearchEnabledDocumentables(),
                'documentable' => $result->getDocumentable(),
                'requestConfiguration' => $requestConfiguration,
                'query' => urldecode(string: $query),
                'query_url' => $query,
                'result' => $result,
                'currencySymbol' => Currencies::getSymbol(
                    currency: $this->currencyContext->getCurrencyCode(),
                    displayLocale: $this->localeContext->getLocaleCode(),
                ),
            ],
        );
    }

    /**
     * Post search.
     */
    public function postAction(Request $request): RedirectResponse
    {
        $query = (array)($request->request->all()['monsieurbiz_searchplugin_search'] ?? []);
        $query = $query['query'] ?? '';

        // With Apache a URL with a encoded slash (%2F) is provoking a 404 error on the server level
        return $this->redirectToRoute(
            route: 'monsieurbiz_search_search',
            parameters: [
                'query' => str_replace(search: '%2F', replace: '/', subject: urlencode(string: $query)),
            ],
        );
    }

    /**
     * Perform the instant search action & display results.
     */
    public function instantAction(Request $request): Response
    {
        $results = [];

        /** @var DocumentableInterface $documentable */
        foreach ($this->getInstantSearchEnabledDocumentables() as $documentable) {
            $requestConfiguration = new RequestConfiguration(
                request: $request,
                type: RequestInterface::INSTANT_TYPE,
                documentable: $documentable,
                searchSettings: $this->searchSettings,
                channelContext: $this->channelContext,
            );

            try {
                $results[$documentable->getIndexCode()] = $this->search->search(requestConfiguration: $requestConfiguration);
            } catch (UnknownRequestTypeException) {
                continue;
            }
        }

        return $this->render(
            view: '@MonsieurBizSyliusSearchPlugin/Instant/result.html.twig',
            parameters: [
                'results' => $results,
            ],
        );
    }

    /**
     * @throws UnknownRequestTypeException
     */
    public function taxonAction(
        Request $request,
        string $documentType = 'monsieurbiz_product'
    ): Response {
        $documentable = $this->getDocumentable(documentType: $documentType);

        /** @var array $syliusAttribute */
        $syliusAttribute = $request->attributes->get(key: '_sylius', default: []);

        $requestConfiguration = new RequestConfiguration(
            request: $request,
            type: RequestInterface::TAXON_TYPE,
            documentable: $documentable,
            searchSettings: $this->searchSettings,
            channelContext: $this->channelContext,
            parameters: new Parameters(
                $this->parametersParser->parseRequestValues(parameters: $syliusAttribute, request: $request),
            ),
        );

        $result = $this->search->search(requestConfiguration: $requestConfiguration);

        return $this->render(
            view: '@MonsieurBizSyliusSearchPlugin/Taxon/result.html.twig',
            parameters: [
                'requestConfiguration' => $requestConfiguration,
                'result' => $result,
                'currencySymbol' => Currencies::getSymbol(
                    currency: $this->currencyContext->getCurrencyCode(),
                    displayLocale: $this->localeContext->getLocaleCode(),
                ),
            ],
        );
    }

    protected function getDocumentable(?string $documentType): DocumentableInterface
    {
        if (null === $documentType) {
            $documentables = $this->getSearchEnabledDocumentables();

            return reset($documentables);
        }

        try {
            /** @phpstan-ignore-next-line */
            return $this->documentableRegistry->get(sprintf('search.documentable.%s', $documentType));
        } catch (NonExistingServiceException) {
            throw new NotFoundHttpException(sprintf('Documentable "%s" not found', $documentType));
        }
    }

    protected function getSearchEnabledDocumentables(): array
    {
        return array_filter(
            array: $this->documentableRegistry->all(),
            callback: function (DocumentableInterface $documentable): bool {
                return (bool)$this->searchSettings->getCurrentValue(
                    channel: $this->channelContext->getChannel(),
                    localeCode: null,
                    path: sprintf('search_enabled__%s', $documentable->getIndexCode()),
                );
            },
        );
    }

    protected function getInstantSearchEnabledDocumentables(): array
    {
        return array_filter(
            array: $this->documentableRegistry->all(),
            callback: function (DocumentableInterface $documentable): bool {
                return (bool)$this->searchSettings->getCurrentValue(
                    channel: $this->channelContext->getChannel(),
                    localeCode: null,
                    path: sprintf('instant_search_enabled__%s', $documentable->getIndexCode()),
                );
            },
        );
    }
}
