<?php

namespace Qero\Actions;

use function Qero\color;

class Packages implements Action
{
    public array $args   = [];
    public array $params = [];

    public function __construct (array $args, array $params = [])
    {
        $this->args   = $args;
        $this->params = $params;
    }

    public function execute (): void
    {
        foreach ($packages = $this->params['package']->requires ?? [] as $package)
            echo color (' - [yellow]'. str_replace (
                '@', '[reset]@', $package) .'[reset]') . PHP_EOL;

        echo PHP_EOL . color ('Totally [yellow]'. sizeof ($packages) .'[reset] packages installed'). PHP_EOL;
    }
}
