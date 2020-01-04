<?php

namespace Qero;

use Colorizer\Colors;

function color (string $string): string
{
    static $colors;

    if ($colors === null)
        $colors = array_diff (get_class_methods ('\\Colorizer\\Colors'), ['format']);

    return IS_POWERFUL ?
        Colors::format ($string) :
        preg_replace ('/\[('. implode ('|', $colors) .')(\,[01]{1}(\,[01]{1})?)?\]/', '', $string);
}
