<?php

/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * @package     Qero
 * @copyright   2018 - 2020 Podvirnyy Nikita (Observer KRypt0n_)
 * @license     GNU GPLv3 <https://www.gnu.org/licenses/gpl-3.0.html>
 * @author      Podvirnyy Nikita (KRypt0n_)
 * 
 * Contacts:
 *
 * Email: <suimin.tu.mu.ga.mi@gmail.com>
 * VK:    vk.com/technomindlp
 *        vk.com/hphp_convertation
 * 
 */

namespace Qero;

const VERSION = '0.1';

use ConsoleArgs\{
    Manager,
    Command,
    DefaultCommand,
    Flag
};

define ('Qero\QERO_DIR', dirname (substr (__DIR__, 0, 7) == 'phar://' ? substr (__DIR__, 7) : __DIR__));
define ('Qero\IS_POWERFUL',
    strtoupper (substr (PHP_OS, 0, 3)) === 'WIN' ?
        strpos (php_uname ('v'), '(Windows 10)') !== false : true);

define ('Qero\PROGRESS_CHAR', IS_POWERFUL ? '█' : '#');

require 'ext/require.php';
require 'php/color.php';
require 'php/dir_delete.php';
require 'php/action.php';
require 'php/requires_tree.php';
require 'php/requester.php';
require 'php/package.php';
require 'php/downloader.php';
require 'php/autoload_generator.php';

foreach (array_diff (scandir ($dir = __DIR__ .'/actions'), ['.', '..', 'help.json']) as $action)
    require $dir .'/'. $action;

chdir (QERO_DIR);

global $index_package;
$index_package = new Packages\Package;

(new Manager ([
    (new Command ('help', function ($args)
    {
        (new Actions\Help ($args))->execute ();
    }))->addAliase ('h'),

    (new Command ('install', function ($args, $params) use (&$index_package)
    {
        $begin_count = sizeof ($index_package->requires ?? []);
        $begin_time  = time ();

        (new Actions\Install ($args, array_merge ($params, [
            'package' => &$index_package
        ])))->execute ();

        Packages\AutoloadGenerator::generate ($index_package);

        $time = time () - $begin_time;

        $display_time = $time > 60 ?
            ($m = (int)($time / 60)) .'m '. ($time - 60 * $m) .'s' :
            ($time == 60 ? '1m' : $time .'s');

        echo PHP_EOL . color ('Totally [yellow]'. (sizeof ($index_package->requires ?? []) - $begin_count)
            .'[reset] packages installed for '. $display_time) . PHP_EOL;
    }))->addParams ([
        (new Flag ('--forced'))->addAliase ('-f')
    ])->addAliase ('i')
      ->addAliase ('require'),

    (new Command ('update', function ($args) use (&$index_package)
    {
        (new Actions\Update ($args, ['package' => &$index_package]))->execute ();
    }))->addAliase ('u'),

    (new Command ('packages', function ($args) use (&$index_package)
    {
        (new Actions\Packages ($args, ['package' => &$index_package]))->execute ();
    }))->addAliase ('list'),

    (new Command ('show', function ($args) use (&$index_package)
    {
        (new Actions\Show ($args, ['package' => &$index_package]))->execute ();
    })),

    (new Command ('scripts', function ($args) use (&$index_package)
    {
        (new Actions\Scripts ($args, ['package' => &$index_package]))->execute ();
    })),

    (new Command ('remove', function ($args) use (&$index_package)
    {
        (new Actions\Remove ($args, ['package' => &$index_package]))->execute ();
    }))->addAliase ('delete'),

    (new Command ('build', function ($args) use (&$index_package)
    {
        (new Actions\Build ($args, ['package' => &$index_package]))->execute ();
    })),

    (new Command ('clear', function ($args) use (&$index_package)
    {
        (new Actions\Clear ($args, ['package' => &$index_package]))->execute ();
    })),

    (new Command ('audit', function ($args, $params) use (&$index_package)
    {
        (new Actions\Audit ($args, array_merge ($params, [
            'package' => &$index_package
        ])))->execute ();
    }))->addParams ([
        (new Flag ('--auto'))
            ->addAliase ('--fix')
            ->addAliase ('-a')
    ])
], new DefaultCommand (function ($args) use (&$index_package)
{
    if (sizeof ($args) > 0)
    {
        echo PHP_EOL;

        if (isset ($index_package->scripts[$args[0]]))
        {
            $script = trim ($index_package->scripts[$args[0]]);

            if (file_exists ($script))
                require $script;

            elseif (file_exists (QERO_DIR .'/'. $script))
                require QERO_DIR .'/'. $script;

            else
            {
                if (strlen ($script) >= 4 && substr ($script, 0, 4) == 'php ')
                    $script = '"'. PHP_BINARY .'" '. substr ($script, 4);

                $script = str_replace ('%QERO%', QERO_DIR, $script);

                echo color ('[green]>[reset] ') . ($script .= ' '. implode (' ', array_slice ($args, 1))) . PHP_EOL;
                echo @shell_exec ($script);
            }
        }

        else echo color (' [red]*[reset] Incorrect command [green]'. $args[0] .'[reset]. Use [green]help[reset] for available commands list');

        echo PHP_EOL;
    }

    else (new Actions\Help ($args))->execute ();
})))->execute (array_slice ($argv, 1));

$index_package->save ();

echo PHP_OS == 'Linux' ?
    PHP_EOL : '';
