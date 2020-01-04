<?php

namespace Qero\Actions;

use Qero\Packages\{
    Package,
    AutoloadGenerator
};

use function Qero\{
    color,
    dir_delete
};

use const Qero\QERO_DIR;

class Remove implements Action
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
        $size = sizeof ($this->params['package']->requires);

        $this->params['package']->requires = array_values (array_diff ($this->params['package']->requires ?? [], $this->args));

        $size -= sizeof ($this->params['package']->requires);

        foreach ($this->args as $package)
        {
            $package = (new Package ($package))->load ();

            if (!file_exists (QERO_DIR .'/qero-packages/'. $package->name))
            {
                echo color (' [red]![reset] Package [yellow]'. str_replace (
                    '@', '[reset]@', $package->toString ()) .'[reset] not installed') . PHP_EOL;

                continue;
            }

            dir_delete (QERO_DIR .'/qero-packages/'. $package->name);
            @rmdir (dirname (QERO_DIR .'/qero-packages/'. $package->name));

            echo color (' - Removed [yellow]'. str_replace (
                '@', '[reset]@', $package->toString ()) .'[reset]') . PHP_EOL;

            if ($package->requires && sizeof ($package->requires) > 0)
                echo color ('   [yellow]*[reset] This package required some other packages. To remove them type'. PHP_EOL .
                     '     [green,1]php qero.phar remove '. implode (' ', $package->requires) .'[reset]') . PHP_EOL . PHP_EOL;
        }

        AutoloadGenerator::generate ($this->params['package']);

        echo PHP_EOL . color ('Totally [yellow]'. $size .'[reset] packages removed') . PHP_EOL;
    }
}
