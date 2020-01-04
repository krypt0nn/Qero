<?php

namespace Colorizer;

class Color
{
    protected string $color    = '';
    protected bool $bright     = false;
    protected bool $background = false;

    public function __construct (string $name = null, bool $bright = false, bool $background = false)
    {
        if ($name)
            $this->color = $name;
        
        $this->bright     = $bright;
        $this->background = $background;
    }

    public function name (string $name): Color
    {
        $this->color = $name;

        return $this;
    }

    public function bright (bool $bright = true): Color
    {
        $this->bright = $bright;

        return $this;
    }

    public function background (bool $background = true): Color
    {
        $this->background = $background;

        return $this;
    }

    public function __toString (): string
    {
        $color = $this->color;

        return $color ? (new Colors)->$color ($this->bright, $this->background) : '';
    }
}
