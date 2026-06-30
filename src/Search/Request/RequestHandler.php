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

use MonsieurBiz\SyliusSearchPlugin\Exception\UnknownRequestTypeException;

readonly class RequestHandler
{
    public function __construct(
        private iterable $searchRequests,
    ) {
    }

    /**
     * @throws UnknownRequestTypeException
     */
    public function getRequest(RequestConfiguration $requestConfiguration): RequestInterface
    {
        /** @var RequestInterface $request */
        foreach ($this->searchRequests as $request) {
            $support = $request->supports(
                type: $requestConfiguration->getType(),
                documentableCode: $requestConfiguration->getDocumentType(),
            );

            if (!$support) {
                continue;
            }

            $request->setConfiguration(configuration: $requestConfiguration);

            return $request;
        }

        throw new UnknownRequestTypeException();
    }
}
