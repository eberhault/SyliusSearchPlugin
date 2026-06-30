<?php

namespace MonsieurBiz\SyliusSearchPlugin\Generated\Model;

class ProductTaxonDTO
{
    protected array $initialized = [];

    public function isInitialized($property) : bool
    {
        return array_key_exists($property, $this->initialized);
    }

    protected TaxonDTO $taxon;

    protected ?int $position;

    public function getTaxon() : TaxonDTO
    {
        return $this->taxon;
    }

    public function setTaxon(TaxonDTO $taxon) : self
    {
        $this->initialized['taxon'] = true;

        $this->taxon = $taxon;

        return $this;
    }

    public function getPosition() : ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position) : self
    {
        $this->initialized['position'] = true;

        $this->position = $position;

        return $this;
    }
}