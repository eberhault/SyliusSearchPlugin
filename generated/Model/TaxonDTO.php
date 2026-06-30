<?php

namespace MonsieurBiz\SyliusSearchPlugin\Generated\Model;


class TaxonDTO
{
    protected array $initialized = [];

    public function isInitialized($property) : bool
    {
        return array_key_exists($property, $this->initialized);
    }

    protected ?string $name;

    protected string $code;

    protected int $position;

    protected int $level;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name) : self
    {
        if (null !== $name) {
            return $this;
        }

        $this->initialized['name'] = true;

        $this->name = $name;

        return $this;
    }

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

    public function getPosition() : int
    {
        return $this->position;
    }

    public function setPosition(int $position) : self
    {
        $this->initialized['position'] = true;

        $this->position = $position;

        return $this;
    }

    public function getLevel() : int
    {
        return $this->level;
    }

    public function setLevel(int $level) : self
    {
        $this->initialized['level'] = true;

        $this->level = $level;

        return $this;
    }
}
