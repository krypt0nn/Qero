<?php

namespace Qero\Actions;

use function Qero\color;
use const Qero\VERSION;

class Help implements Action
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
        $actions    = [];
        $max_length = 0;

        foreach (json_decode (file_get_contents (__DIR__ .'/help.json'), true) as $name => $action)
        {
            $actions[$name] = $action;

            $max_length = max ($max_length, strlen ($name));
        }

        echo PHP_EOL . color ('[magenta]Qero[reset] ') . VERSION . PHP_EOL;
        echo PHP_EOL . color ('Usage: [green]php qero.phar <command> [arguments][reset]') . PHP_EOL;
        echo color ('Example: [green]php qero.phar i php-ai/php-ml[reset]') . PHP_EOL . PHP_EOL;

        foreach ($actions as $name => $action)
        {
            echo str_repeat (' ', $max_length - strlen ($name)) .
                 color ('  [yellow]'. $name .'[reset]  ');

            if (isset ($action['aliases']))
                echo color ('[black,1](') .
                     implode (', ', $action['aliases']) .
                     color (')[reset]') .'  ';

            if (isset ($action['arguments']))
                echo implode (' ', $action['arguments']) .'  ';

            echo (isset ($action['aliases']) || isset ($action['arguments']) ? PHP_EOL . str_repeat (' ', $max_length + 4) : '') .
                color ($action['description']) . PHP_EOL . PHP_EOL;

            if (isset ($action['properties']))
            {
                $prop_max_length = 0;

                foreach ($action['properties'] as $name => $property)
                    $prop_max_length = max ($prop_max_length, strlen ($name));

                $prop_max_length += $max_length + 2;

                foreach ($action['properties'] as $name => $property)
                {
                    echo str_repeat (' ', $prop_max_length - strlen ($name)) .
                        color ('  [magenta]'. $name .'[reset]  ');

                    if (isset ($property['aliases']))
                        echo color ('[black,1](') .
                            implode (', ', $property['aliases']) .
                            color (')[reset]') .'  ';

                    if (isset ($property['default']))
                        echo $property['default'] .'  ';

                    echo (isset ($property['aliases']) || isset ($property['default']) ? PHP_EOL . str_repeat (' ', $prop_max_length + 4) : '') .
                        color ($property['description']) . PHP_EOL . PHP_EOL;
                }
            }
        }
    }
}
