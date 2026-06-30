<?php

namespace MonsieurBiz\SyliusSearchPlugin\Generated\Model;

class ChannelDTO
{
    protected array $initialized = [];

    public function isInitialized($property) : bool
    {
        return array_key_exists($property, $this->initialized);
    }

    protected string $code;

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
}