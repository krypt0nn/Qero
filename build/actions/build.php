<?php

namespace Qero\Actions;

use function Qero\color;

class Build implements Action
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
        $begin = time ();

        \Qero\Packages\AutoloadGenerator::generate ($this->params['package']);

        $time = time () - $begin;

        $display_time = $time > 60 ?
            ($m = (int)($time / 60)) .'m '. ($time - 60 * $m) .'s' :
            ($time == 60 ? '1m' : $time .'s');

        echo PHP_EOL . color ('Build finished after [yellow]'. $display_time .'[reset]') . PHP_EOL;
    }
}
