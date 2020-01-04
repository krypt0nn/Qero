<?php

namespace Qero\Actions;

use Qero\Packages\Package;

use function Qero\{
    color,
    dir_delete
};

use const Qero\QERO_DIR;

class Audit implements Action
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

        $packages = [];
        $requires = array_map (
            fn ($package) => new Package ($package),
                $this->params['package']->requires) ?? [];

        $errors = 0;
        $fixed  = 0;
        $required_packages = [];
        $packages_assocs   = [];

        foreach ($requires as $required)
        {
            if (!file_exists (QERO_DIR .'/qero-packages/'. $required->name) && !isset ($required_packages[$required->name]))
            {
                echo color (' [red]![reset] Package [yellow]'. $required->name .'[reset] required, but not downloaded');

                ++$errors;

                if ($this->params['--auto'])
                {
                    $this->params['package']->requires = array_values (array_diff (
                        $this->params['package']->requires, [$required->toString ()]));

                    echo color (' : [green]Fixed[reset]');

                    ++$fixed;
                }

                echo PHP_EOL;
            }

            if (isset ($required_packages[$required->name]) && $required_packages[$required->name])
            {
                echo color (' [red]![reset] Package [yellow]'. $required->name .'[reset] already required with another version');

                ++$errors;
                $required_packages[$required->name] = false;

                if ($this->params['--auto'])
                {
                    $toRemove = [];

                    foreach ($this->params['package']->requires as $trequired)
                        if ((new Package ($trequired))->name == $required->name)
                            $toRemove[] = $trequired;

                    if (sizeof ($toRemove) > 0)
                    {
                        $this->params['package']->requires = array_values (array_diff (
                            $this->params['package']->requires, $toRemove));
                        
                        $this->params['package']->requires[] = (new Package ($required->name))
                            ->load ()->toString ();
                    }

                    echo color (' : [green]Fixed[reset]');

                    ++$fixed;
                }

                echo PHP_EOL;
            }

            if (isset ($packages_assocs[$lower_name = strtolower ($required->name)]) && $packages_assocs[$lower_name] != $required->name)
            {
                echo color (' [red]![reset] Package [yellow]'. $required->name .'[reset] have different typings ([green]'. $packages_assocs[$lower_name] .'[reset])');

                ++$errors;
                $required_packages[$required->name] = false;

                if ($this->params['--auto'])
                {
                    $toRemove = [];

                    foreach ($this->params['package']->requires as $trequired)
                        if (strtolower ((new Package ($trequired))->name) == $lower_name)
                            $toRemove[] = $trequired;

                    if (sizeof ($toRemove) > 0)
                    {
                        $this->params['package']->requires = array_values (array_diff (
                            $this->params['package']->requires, $toRemove));

                        $onecy = true;

                        foreach ($toRemove as $tremove)
                            if (file_exists (QERO_DIR .'/qero-packages/'. $tremove) && $onecy)
                            {
                                $this->params['package']->requires[] = $tremove;

                                $onecy = false;
                            }

                            else
                            {
                                dir_delete (QERO_DIR .'/qero-packages/'. $tremove);
                                @rmdir (dirname (QERO_DIR .'/qero-packages/'. $tremove));
                            }
                    }

                    echo color (' : [green]Fixed[reset]');

                    ++$fixed;
                }

                echo PHP_EOL;
            }
            
            if (!isset ($required_packages[$required->name]))
                $required_packages[$required->name] = true;

            if (!isset ($packages_assocs[$lower_name]))
                $packages_assocs[$lower_name] = $required->name;
        }

        foreach (glob ($prefix = QERO_DIR .'/qero-packages/*/*') as $package)
            if (is_dir ($package))
            {
                $package = (new Package (substr ($package, strlen ($prefix) - 3)))
                    ->load ();

                $founded = false;

                foreach ($requires as $required)
                    if ($required->name == $package->name)
                    {
                        $founded = true;

                        break;
                    }

                if (!$founded)
                {
                    echo color (' [red]![reset] Detected downloaded package [yellow]'. str_replace (
                        '@', '[reset]@', $package->toString ()) .'[reset], but they are not indexed');

                    ++$errors;

                    if ($this->params['--auto'])
                    {
                        $this->params['package']->requires[] = $package->toString ();

                        echo color (' : [green]Fixed[reset]');

                        ++$fixed;
                    }

                    echo PHP_EOL;
                }
            }

        $commands = [];

        foreach (json_decode (file_get_contents (__DIR__ .'/help.json'), true) as $command => $info)
            $commands = array_merge ($commands, [$command], $info['aliases'] ?? []);

        foreach (array_keys ($this->params['package']->scripts ?? []) as $name)
            if (in_array ($name, $commands))
            {
                echo color (' [red]![reset] Script with name [green]'. $name .'[reset] conflicting with qero commands names');

                ++$errors;

                if ($this->params['--auto'])
                {
                    unset ($this->params['package']->scripts[$name]);

                    if (sizeof ($this->params['package']->scripts) == 0)
                        $this->params['package']->scripts = null;

                    echo color (' : [green]Fixed[reset]');

                    ++$fixed;
                }

                echo PHP_EOL;
            }

        if ($this->params['--auto'] && $fixed > 0)
            \Qero\Packages\AutoloadGenerator::generate ($this->params['package']);
        
        echo PHP_EOL . color ($errors > 0 ?
            'Founded [red]'. $errors .'[reset] installation errors' . ($this->params['--auto'] ?
                ', [green]'. $fixed .'[reset] fixed' : '') :
            '[green]All packages installed correctly[reset]') . PHP_EOL;
    }
}
