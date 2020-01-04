<?php

namespace Colorizer;

class Dialog
{
    public string $background         = '';
    public string $foreground_message = '';
    public string $foreground_caption = '';

    public static $eol = "\n";

    protected int $size = 0;
    protected string $message = '';
    protected string $caption = '';

    public function __construct (string $message = null, string $caption = null)
    {
        $this->setCaption ($caption ?? '');
        $this->setMessage ($message ?? '');
    }

    public function setMessage (string $message): Dialog
    {
        $this->message = $message;

        foreach (explode (self::$eol, $message) as $line)
            $this->size = max ($this->size, strlen ($line));

        return $this;
    }

    public function setCaption (string $caption): Dialog
    {
        $this->caption = $caption;
        $this->size    = max ($this->size, strlen ($caption));

        return $this;
    }

    public function width (int $width): Dialog
    {
        $this->size = max ($this->size, $width);

        return $this;
    }

    public function background (string $name): Dialog
    {
        $this->background = $name;

        return $this;
    }

    public function foregroundMessage (string $name): Dialog
    {
        $this->foreground_message = $name;

        return $this;
    }

    public function foregroundCaption (string $name): Dialog
    {
        $this->foreground_caption = $name;
        
        return $this;
    }

    public function __toString (): string
    {
        $border  = str_repeat (' ', $this->size + 8);
        $message = explode (self::$eol, $this->message);

        foreach ($message as $id => $line)
            $message[$id] = $line . str_repeat (' ', $this->size - strlen ($line));

        $message = implode (self::$eol, $message);
        $caption = $this->caption . str_repeat (' ', $this->size - strlen ($this->caption));

        return new Color ($this->background, false, true) . $border . self::$eol .
            ($this->caption ?
                new Color ($this->foreground_caption) .'    '. $caption .'    '.
                    Colors::reset () . new Color ($this->background, false, true) . self::$eol : '') .
            new Color ($this->foreground_message) .'    '. $message .'    '. self::$eol .
            $border . Colors::reset ();
    }
}
