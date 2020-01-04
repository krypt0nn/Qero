<?php

namespace Qero\Packages;

class RequiresTree
{
    public static array $requires = [];

    public static function add (string $name, string $version = null, string $required_by = null): bool
    {
        if (!isset (self::$requires[$name]))
            self::$requires[$name] = [
                'version'     => $version,
                'required_by' => $required_by !== null ? [$required_by => true] : []
            ];

        elseif (self::$requires[$name]['version'] == $version)
            self::$requires[$name]['required_by'][$required_by] = true;

        else return false;

        return true;
    }

    public static function getVersion (string $name): ?string
    {
        return self::$requires[$name]['version'] ?? null;
    }

    public static function getRequiredBy (string $name): ?array
    {
        return isset (self::$requires[$name]) && sizeof (self::$requires[$name]['required_by']) > 0 ?
            array_keys (self::$requires[$name]['required_by']) : null;
    }
}
