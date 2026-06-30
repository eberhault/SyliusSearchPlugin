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

namespace MonsieurBiz\SyliusSearchPlugin\Search\Response\FilterBuilders\Product;

use MonsieurBiz\SyliusSearchPlugin\Model\Documentable\DocumentableInterface;
use MonsieurBiz\SyliusSearchPlugin\Search\Filter\Filter;
use MonsieurBiz\SyliusSearchPlugin\Search\Request\RequestConfiguration;
use MonsieurBiz\SyliusSearchPlugin\Search\Response\FilterBuilders\FilterBuilderInterface;

class MainTaxonFilterBuilder implements FilterBuilderInterface
{
    public function build(
        DocumentableInterface $documentable,
        RequestConfiguration $requestConfiguration,
        string $aggregationCode,
        array $aggregationData
    ): ?array {
        if (!str_ends_with($documentable->getIndexCode(), 'monsieurbiz_product') || 'main_taxon' !== $aggregationCode) {
            return null;
        }

        $taxonAggregation = $aggregationData['main_taxon'] ?? null;

        if ($taxonAggregation && $taxonAggregation['doc_count'] > 0) {
            $filter = new Filter(
                requestConfiguration: $requestConfiguration,
                code: 'main_taxon',
                label: 'monsieurbiz_searchplugin.filters.taxon_filter',
                count: $taxonAggregation['doc_count'],
                type: 'taxon',
            );

            // Get main taxon code in aggregation
            $taxonCodeBuckets = $taxonAggregation['codes']['buckets'] ?? [];

            foreach ($taxonCodeBuckets as $taxonCodeBucket) {
                if (0 === $taxonCodeBucket['doc_count']) {
                    continue;
                }

                $taxonCode = $taxonCodeBucket['key'];

                $taxonName = null;

                // Get main taxon level in aggregation
                $taxonLevelBuckets = $taxonCodeBucket['levels']['buckets'] ?? [];

                foreach ($taxonLevelBuckets as $taxonLevelBucket) {
                    // Get main taxon name in aggregation
                    $taxonNameBuckets = $taxonLevelBucket['names']['buckets'] ?? [];

                    foreach ($taxonNameBuckets as $taxonNameBucket) {
                        $taxonName = $taxonNameBucket['key'];

                        $filter->addValue(
                            label: $taxonName ?? $taxonCode,
                            count: $taxonCodeBucket['doc_count'],
                            value: $taxonCode,
                        );

                        break 2;
                    }
                }
            }

            // Put taxon filter in first if contains value
            if (0 !== count(value: $filter->getValues())) {
                return [$filter];
            }
        }

        return null;
    }

    public function getPosition(): int
    {
        return 1;
    }
}
