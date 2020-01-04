<?php

namespace Qero\Actions;

use function Qero\color;

class Scripts implements Action
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
        foreach ($scripts = $this->params['package']->scripts ?? [] as $script => $ex)
            echo color (' - [yellow]'. $script .'[reset]') . PHP_EOL;

        echo PHP_EOL . color ('Totally [yellow]'. sizeof ($scripts) .'[reset] scripts defined') . PHP_EOL;
    }
}
