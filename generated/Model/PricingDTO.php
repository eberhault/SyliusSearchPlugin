<?php

namespace MonsieurBiz\SyliusSearchPlugin\Generated\Model;

class PricingDTO
{
    protected array $initialized = [];

    public function isInitialized($property) : bool
    {
        return array_key_exists($property, $this->initialized);
    }

    protected string $channelCode;

    protected ?int $price;

    protected ?int $originalPrice;

    protected bool $priceReduced;

    public function getChannelCode() : string
    {
        return $this->channelCode;
    }

    public function setChannelCode(string $channelCode) : self
    {
        $this->initialized['channelCode'] = true;

        $this->channelCode = $channelCode;

        return $this;
    }

    public function getPrice() : ?int
    {
        return $this->price;
    }

    public function setPrice(?int $price) : self
    {
        $this->initialized['price'] = true;

        $this->price = $price;

        return $this;
    }

    public function getOriginalPrice() : ?int
    {
        return $this->originalPrice;
    }

    public function setOriginalPrice(?int $originalPrice) : self
    {
        $this->initialized['originalPrice'] = true;

        $this->originalPrice = $originalPrice;

        return $this;
    }

    public function getPriceReduced() : bool
    {
        return $this->priceReduced;
    }

    public function setPriceReduced(bool $priceReduced) : self
    {
        $this->initialized['priceReduced'] = true;

        $this->priceReduced = $priceReduced;

        return $this;
    }
}