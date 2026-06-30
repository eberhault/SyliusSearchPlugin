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

namespace MonsieurBiz\SyliusSearchPlugin\Form\Extension;

use Sylius\Bundle\ProductBundle\Form\Type\ProductAttributeType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

final class ProductAttributeTypeExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $searchWeightValues = range(start: 1, end: 10);

        $builder
            ->add(
                child: 'searchable',
                type: CheckboxType::class,
                options: [
                    'label' => 'monsieurbiz_searchplugin.admin.product_attribute.form.searchable',
                    'required' => true,
                ],
            )
            ->add(
                child: 'filterable',
                type: CheckboxType::class,
                options: [
                    'label' => 'monsieurbiz_searchplugin.admin.product_attribute.form.filterable',
                    'required' => true,
                ],
            )
            ->add(
                child: 'search_weight',
                type: ChoiceType::class,
                options: [
                    'label' => 'monsieurbiz_searchplugin.admin.product_attribute.form.search_weight',
                    'required' => true,
                    'choices' => array_combine(keys: $searchWeightValues, values: $searchWeightValues),
                ],
            );
    }

    public static function getExtendedTypes(): array
    {
        return [
            ProductAttributeType::class,
        ];
    }
}
