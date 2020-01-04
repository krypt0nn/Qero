<?php

namespace Colorizer;

class Colors
{
    public static function black (bool $bright = false, bool $background = false): string
    {
        return "\x1b[". (30 + ($bright ? 60 : 0) + ($background ? 10 : 0)) .';1m';
    }

    public static function red (bool $bright = false, bool $background = false): string
    {
        return "\x1b[". (31 + ($bright ? 60 : 0) + ($background ? 10 : 0)) .';1m';
    }

    public static function green (bool $bright = false, bool $background = false): string
    {
        return "\x1b[". (32 + ($bright ? 60 : 0) + ($background ? 10 : 0)) .';1m';
    }

    public static function yellow (bool $bright = false, bool $background = false): string
    {
        return "\x1b[". (33 + ($bright ? 60 : 0) + ($background ? 10 : 0)) .';1m';
    }

    public static function blue (bool $bright = false, bool $background = false): string
    {
        return "\x1b[". (34 + ($bright ? 60 : 0) + ($background ? 10 : 0)) .';1m';
    }

    public static function magenta (bool $bright = false, bool $background = false): string
    {
        return "\x1b[". (35 + ($bright ? 60 : 0) + ($background ? 10 : 0)) .';1m';
    }

    public static function cyan (bool $bright = false, bool $background = false): string
    {
        return "\x1b[". (36 + ($bright ? 60 : 0) + ($background ? 10 : 0)) .';1m';
    }

    public static function white (bool $bright = false, bool $background = false): string
    {
        return "\x1b[". (37 + ($bright ? 60 : 0) + ($background ? 10 : 0)) .';1m';
    }

    public static function reset (): string
    {
        return "\x1b[0m";
    }

    public static function format (string $text): string
    {
        $colors = array_diff (get_class_methods (self::class), ['format']);

        return preg_replace_callback ('/\[('. implode ('|', $colors) .')(\,[01]{1}(\,[01]{1})?)?\]/', function (array $code): string
        {
            return new Color (...explode (',', substr ($code[0], 1, -1)));
        }, $text);
    }
}
