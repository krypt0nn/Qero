<?php

namespace Qero\Actions;

use Qero\Packages\{
    Package,
    RequiresTree
};

use function Qero\color;
use const Qero\QERO_DIR;

class Install implements Action
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
        $this->params['package']->requires ??= [];

        foreach ($this->params['package']->requires as $name)
        {
            $package = new Package ($name);

            RequiresTree::add ($package->name, $package->version);
        }

        foreach (sizeof ($this->args) > 0 ? $this->args : $this->params['package']->requires as $packageName)
        {
            $package = new Package ($packageName);

            if (!RequiresTree::add ($packageName, $package->version, $this->params['required_by'] ?? null) && !$this->params['--forced'])
            {
                echo color (' [red]![reset] Package [yellow]'. str_replace (
                    '@', '[reset]@', $package->toString ())
                    .'[reset] already required with another version ([yellow]'. RequiresTree::getVersion ($packageName) .'[reset])'
                    . (($required = RequiresTree::getRequiredBy ($packageName)) !== null ?
                        ' by [yellow]'. implode (', ', $required) .'[reset]' : '')) . PHP_EOL;

                continue;
            }

            if (file_exists (QERO_DIR .'/qero-packages/'. $package->name) && !$this->params['--forced'])
            {
                echo color (' [yellow]*[reset] Package [yellow]'. str_replace (
                    '@', '[reset]@', $package->toString ()) .'[reset] already installed') . PHP_EOL;

                if (!in_array ($packageName, $this->params['package']->requires))
                    $this->params['package']->requires[] = $packageName;

                continue;
            }

            elseif ($package->install ())
            {
                $package = (new Package ($packageName))->load ();

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

                if (!in_array ($packageName, $this->params['package']->requires ?? []))
                    $this->params['package']->requires[] = $packageName;

                if ($package->requires)
                    (new Install ($package->requires, [
                        'package'     => &$this->params['package'],
                        'required_by' => $packageName,
                        '--forced'    => $this->params['--forced']
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
            }
        }
    }
}
