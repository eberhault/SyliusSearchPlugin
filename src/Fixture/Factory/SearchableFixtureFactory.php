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

namespace MonsieurBiz\SyliusSearchPlugin\Fixture\Factory;

use Exception;
use MonsieurBiz\SyliusSearchPlugin\Entity\Product\SearchableInterface;
use Sylius\Bundle\CoreBundle\Fixture\Factory\AbstractExampleFactory;
use Sylius\Bundle\CoreBundle\Fixture\OptionsResolver\LazyOption;
use Sylius\Component\Product\Model\ProductAttributeInterface;
use Sylius\Component\Product\Model\ProductOptionInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Webmozart\Assert\Assert;

class SearchableFixtureFactory extends AbstractExampleFactory implements SearchableFixtureFactoryInterface
{
    private OptionsResolver $optionsResolver;

    public function __construct(
        private readonly RepositoryInterface $productAttributeRepository,
        private readonly RepositoryInterface $productOptionRepository,
    ) {
        $this->optionsResolver = new OptionsResolver();

        $this->configureOptions($this->optionsResolver);
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault(option: 'attribute', value: null)
                ->setAllowedTypes(
                    option: 'attribute',
                    allowedTypes: ['null', 'string', ProductAttributeInterface::class],
                )
                ->setNormalizer(
                    option: 'attribute',
                    normalizer: LazyOption::findOneBy(repository: $this->productAttributeRepository, field: 'code'),
                )
            ->setDefault(option: 'option', value: null)
                ->setAllowedTypes(
                    option: 'option',
                    allowedTypes: ['null', 'string', ProductOptionInterface::class],
                )
                ->setNormalizer(
                    option: 'option',
                    normalizer: LazyOption::findOneBy(repository: $this->productOptionRepository, field: 'code'),
                )
            ->setDefault(option: 'filterable', value: false)
            ->setDefault(option: 'searchable', value: false)
            ->setDefault(option: 'search_weight', value: 1)
        ;
    }

    /**
     * @throws Exception
     */
    public function create(array $options = []): SearchableInterface
    {
        $options = $this->optionsResolver->resolve(options: $options);

        $object = $this->getSearchableObject(options: $options);
        $object->setFilterable(filterable: (bool) ($options['filterable'] ?? false));
        $object->setSearchable(searchable: (bool) ($options['searchable'] ?? false));
        $object->setSearchWeight(searchWeight: (int) ($options['search_weight'] ?? 1));

        return $object;
    }

    private function getSearchableObject(array $options): SearchableInterface
    {
        $object = null;

        if ([] !== $options['attribute']) {
            $object = $options['attribute'];
        } elseif ([] !== $options['option']) {
            $object = $options['option'];
        }

        Assert::isInstanceOf(value: $object, class: SearchableInterface::class);

        return $object;
    }
}
