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

namespace MonsieurBiz\SyliusSearchPlugin\Twig\Extension;

use MonsieurBiz\SyliusSearchPlugin\Checker\ElasticsearchCheckerInterface;
use MonsieurBiz\SyliusSearchPlugin\Form\Type\SearchType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\AbstractExtension;
use Twig\Markup;
use Twig\TwigFunction;

class RenderSearchForm extends AbstractExtension
{
    public function __construct(
        private readonly FormFactoryInterface $formFactory,
        private readonly Environment $templatingEngine,
        private readonly RequestStack $requestStack,
        private readonly ElasticsearchCheckerInterface $elasticsearchChecker
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(name: 'search_form', callable: $this->createForm(...)),
        ];
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function createForm(?string $template = null): Markup
    {
        if (false === $this->elasticsearchChecker->check()) {
            return new Markup(content: '', charset: 'UTF-8');
        }

        $request = $this->requestStack->getCurrentRequest();

        $template = $template ?? '@MonsieurBizSyliusSearchPlugin/Search/_form.html.twig';

        $query = null !== $request ? $request->query->getString(key: 'query') : '';

        $content = $this->templatingEngine->render(
            name: $template,
            context: [
                'form' => $this->formFactory->create(type: SearchType::class)->createView(),
                'query' => urldecode(string: $query),
            ],
        );

        return new Markup(content: $content, charset: 'UTF-8');
    }
}
