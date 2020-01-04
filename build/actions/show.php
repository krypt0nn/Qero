<?php

namespace Qero\Actions;

use Qero\Packages\Package;
use const Qero\QERO_DIR;
use function Qero\color;

class Show implements Action
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
        echo PHP_EOL;

        foreach (sizeof ($this->args) > 0 ? $this->args :
            ($this->params['package']->requires ?? []) as $packageName)
            if (($version = new Package ($packageName))->exists ())
            {
                $package = (new Package ($packageName))->load ();

                echo color ('$ [yellow]'. $package->name .'[reset]') . PHP_EOL;
                echo color ('  version: [yellow]'. ($version->version ?: 'latest[reset]'. ($package->version ?
                    ' ('. $package->version .')' : '')) .'[reset]') . PHP_EOL;

                if ($package->requires && ($size = sizeof ($package->requires)) > 0)
                {
                    echo PHP_EOL . color ('  require [yellow]'. $size .'[reset] packages:') . PHP_EOL;

                    foreach ($package->requires as $required)
                        echo color ('  - [yellow]'. str_replace (
                            '@', '[reset]@', $required) .'[reset]') . PHP_EOL;

                    echo PHP_EOL;
                }

                $size = $this->countDirSize (QERO_DIR .'/qero-packages/'. $package->name) / 1024;
                $post = ' Kb';

                if ($size > 1000)
                {
                    $size /= 1024;
                    $post = ' Mb';
                }

                echo color ('  entry point: [green]'. ($package->entry_point ?: '-') .'[reset]') . PHP_EOL;
                echo color ('  size: [green]'. round ($size, 2) . $post .'[reset]') . PHP_EOL;
                echo color ('  node id: [green]'. ($package->node_id ?: '?') .'[reset]') . PHP_EOL;

                echo PHP_EOL;
            }

            else echo color (' [red]*[reset] Package [yellow]'. str_replace (
                '@', '[reset]@', $packageName) .'[reset] not installed') . PHP_EOL . PHP_EOL;
    }

    protected function countDirSize (string $path, int $exp = 1, int $precision = 2): int
    {
        if (!is_dir ($path))
            return 0;
        
        $size = 0;
        
        foreach (array_slice (scandir ($path), 2) as $file)
            $size += is_dir ($file = $path .'/'. $file) ?
                $this->countDirSize ($file, $exp, $precision) :
                round (filesize ($file) / $exp, $precision);

        return $size;
    }
}
