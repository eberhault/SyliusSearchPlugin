<?php

namespace MonsieurBiz\SyliusSearchPlugin\Generated\Model;

class ProductAttributeDTO
{
    protected array $initialized = [];

    public function isInitialized($property) : bool
    {
        return array_key_exists($property, $this->initialized);
    }

    protected string $code;

    protected string $name;

    protected mixed $value;

    public function getCode() : string
    {
        return $this->code;
    }

    public function setCode(string $code) : self
    {
        $this->initialized['code'] = true;

        $this->code = $code;

        return $this;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function setName(string $name) : self
    {
        $this->initialized['name'] = true;

        $this->name = $name;

        return $this;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setValue(mixed $value) : self
    {
        $this->initialized['value'] = true;

        $this->value = $value;

        return $this;
    }
}