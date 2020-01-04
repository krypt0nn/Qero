<?php

namespace Qero\Actions;

use function Qero\{
    dir_delete,
    color
};

use const Qero\QERO_DIR;

class Clear implements Action
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

        dir_delete (QERO_DIR .'/qero-packages');
        $this->params['package']->requires = null;

        echo PHP_EOL . color ('Removed [yellow]'. sizeof ($packages) .'[reset] packages') . PHP_EOL;
    }
}
