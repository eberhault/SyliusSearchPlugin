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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Request;

use MonsieurBiz\SyliusSearchPlugin\Exception\ObjectNotInstanceOfClassException;
use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSettingsPlugin\Settings\SettingsInterface;
use Sylius\Bundle\ResourceBundle\Controller\Parameters;
use Sylius\Component\Channel\Context\ChannelContextInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\HttpFoundation\Request;

final class RequestConfiguration
{
    public const int FALLBACK_LIMIT = 9;

    public function __construct(
        private readonly Request $request,
        private readonly string $type,
        private readonly DocumentableInterface $documentable,
        private readonly SettingsInterface $searchSettings,
        private readonly ChannelContextInterface $channelContext,
        private ?Parameters $parameters = null
    ) {
        $this->parameters = $this->parameters ?? new Parameters();
    }

    public function getQueryText(): string
    {
        $query = $this->request->query
            ->getString(
                key: 'query',
                default: $this->request->attributes->getString(key: 'query')
            );
        return trim(
            string: urldecode(
                string: $query,
            ),
        );
    }

    public function getAppliedFilters(string $type = null): array
    {
        $requestQuery = $this->request->query->all();

        $requestQuery = array_map(
            callback: function ($query) {
                return \is_array($query) ? array_filter($query) : $query;
            },
            array: $requestQuery,
        );

        $this->manageRangeField(field: 'price');

        return null !== $type ? ($requestQuery[$type] ?? []) : $requestQuery;
    }

    public function getSorting(): array
    {
        return $this->request->query->all(key: 'sorting');
    }

    public function getPage(): int
    {
        return $this->request->query->getInt(key: 'page', default: 1);
    }

    public function manageRangeField(string $field): void
    {
        /** @var array $range */
        $range = $this->request->query->all(key: $field);

        if (!is_array($range) || empty($range)) {
            return;
        }

        // Reverse min and max if min is greater than max
        if (isset($range['min'], $range['max'])) {
            $min = (float)$range['min'];
            $max = (float)$range['max'];
            if ($min > $max) {
                $min = $range['min']; // Take the original value, not casted
                $range['min'] = $range['max'];
                $range['max'] = $min;
            }
        }

        // Remove min value is 0 or less
        if (isset($range['min']) && 0 >= (float)$range['min']) {
            unset($range['min']);
        }

        // Remove max value if it is 0 or less
        if (isset($range['max']) && 0 >= (float)$range['max']) {
            unset($range['max']);
        }

        $this->request->query->set(key: $field, value: $range);
    }

    public function getLimit(): int
    {
        $limit = $this->request->query->getInt(key: 'limit', default: self::FALLBACK_LIMIT);

        $availableLimits = $this->getAvailableLimits();

        if (0 < count(value: $availableLimits) && !in_array(needle: $limit, haystack: $availableLimits, strict: true)) {
            $limit = reset($availableLimits);
        }

        return $limit;
    }

    public function getAvailableLimits(): array
    {
        /** @var array $configLimits */
        $configLimits = $this->searchSettings->getCurrentValue(
            channel: $this->channelContext->getChannel(),
            localeCode: null,
            path: sprintf('limits__%s', $this->getDocumentType()),
        );

        return $configLimits[$this->getType()] ?? $this->documentable->getLimits(queryType: $this->getType());
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getDocumentType(): string
    {
        return $this->documentable->getIndexCode();
    }

    public function getTaxon(): TaxonInterface
    {
        if (!$this->parameters->has(key: 'taxon')) {
            throw new ParameterNotFoundException(key: 'taxon');
        }

        $taxon = $this->parameters->get(key: 'taxon');
        if (!$taxon instanceof TaxonInterface) {
            throw ObjectNotInstanceOfClassException::fromClassName(className: TaxonInterface::class);
        }

        return $this->parameters->get(key: 'taxon');
    }

    public function getParameters(): Parameters
    {
        return $this->parameters;
    }
}
