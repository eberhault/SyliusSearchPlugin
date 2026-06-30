<?php

namespace MonsieurBiz\SyliusSearchPlugin\Generated\Model;

class ImageDTO
{
    protected array $initialized = [];

    public function isInitialized($property) : bool
    {
        return array_key_exists($property, $this->initialized);
    }

    protected ?string $path;

    protected ?string $type;

    public function getPath() : ?string
    {
        return $this->path;
    }

    public function setPath(?string $path) : self
    {
        $this->initialized['path'] = true;

        $this->path = $path;

        return $this;
    }

    public function getType() : ?string
    {
        return $this->type;
    }

    public function setType(?string $type) : self
    {
        $this->initialized['type'] = true;

        $this->type = $type;

        return $this;
    }
}