<?php

namespace Qero\Actions;

use Qero\Packages\{
    Package,
    RequiresTree
};

use function Qero\color;
use const Qero\QERO_DIR;

class Update implements Action
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
        $begin_time = time ();
        
        $this->params['package']->requires ??= [];
        $update = [];

        foreach ($this->params['package']->requires as $name)
        {
            $package = new Package ($name);

            RequiresTree::add ($package->name, $package->version);

            if (!$package->version)
                $update[] = $package;
        }

        $updated = 0;

        foreach ($update as $package)
            if ($package->install ())
            {
                $package = (new Package ($package->name))->load ();

                if ($package->entry_point === null)
                {
                    $name = explode ('/', $package->name);

                    foreach ([
                        end ($name) .'.php',
                        'index.php',
                        'main.php'
                    ] as $entryPoint)
                        if (file_exists (QERO_DIR .'/qero-packages/'. $package->name .'/'. $entryPoint))
                        {
                            $package->entry_point = $entryPoint;

                            break;
                        }
                }

                if ($package->requires && sizeof ($requires = array_diff ($package->requires, array_map (
                    fn ($package) => $package->toString (), $update))) > 0)
                        (new Install ($requires, [
                            'package'     => &$this->params['package'],
                            'required_by' => $package->toString ()
                        ]))->execute ();

                $package->save ();

                if ($package->after_install !== null)
                {
                    if (file_exists ($package->after_install))
                        require $package->after_install;

                    elseif (file_exists ($path = QERO_DIR .'/qero-packages/'. $package->name .'/'. $package->after_install))
                        require $path;

                    elseif (file_exists ($path = QERO_DIR .'/'. $package->after_install))
                        require $path;
                }

                ++$updated;
            }

        $time = time () - $begin_time;

        $display_time = $time > 60 ?
            ($m = (int)($time / 60)) .'m '. ($time - 60 * $m) .'s' :
            ($time == 60 ? '1m' : $time .'s');

        echo PHP_EOL . color ('Updated [yellow]'. $updated .'[reset] packages for '. $display_time) . PHP_EOL;
    }
}
