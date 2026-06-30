<?php

namespace MonsieurBiz\SyliusSearchPlugin\Generated\Runtime\Normalizer;

use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;
class ValidationException extends RuntimeException
{
    public function __construct(
        private readonly ConstraintViolationListInterface $violationList,
    )
    {
        parent::__construct(
            sprintf('Model validation failed with %d errors.', $violationList->count()),
            Response::HTTP_BAD_REQUEST,
        );
    }

    public function getViolationList() : ConstraintViolationListInterface
    {
        return $this->violationList;
    }
}