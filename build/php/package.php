<?php

namespace Qero\Packages;

use Qero\Requests\Downloader;
use const Qero\QERO_DIR;

use function Qero\{
    color,
    dir_delete
};

class Package
{
    public ?string $name    = null;
    public ?string $author  = null;
    public ?string $version = null;
    public ?array $requires = null;
    public ?array $scripts  = null;
    public ?string $after_install = null;
    public ?string $entry_point   = null;
    public ?string $node_id       = null;

    protected string $dir = QERO_DIR;

    public function __construct (string $package = null)
    {
        if ($package !== null)
            $this->fromString ($package);

        elseif (file_exists (QERO_DIR .'/qero-package.json'))
        {
            $package = json_decode (file_get_contents (QERO_DIR .'/qero-package.json'), true);

            foreach ($package as $name => $value)
                $this->$name = $value;
        }
    }

    public function install (string $folder = null): bool
    {
        if ($folder === null)
            $folder = QERO_DIR .'/qero-packages';

        $info = Downloader::getInfo ($this);

        if ($info === null)
        {
            echo color (' [red]*[reset] Package [yellow]'.
                str_replace ('@', '[reset]@', $this->toString ()) .'[reset] not founded') . PHP_EOL;

            return false;
        }

        elseif ($info['node_id'] == (new Package ($this->name))->load ()->node_id)
            return false;

        $archive = Downloader::getTarball ($this);

        dir_delete ($folder .'/'. $this->name);

        if (!file_exists ($branch = dirname ($folder .'/'. $this->name)))
            mkdir ($branch, 0777, true);

        file_put_contents ($branch .'/branch.tar', $archive);

        $archive = new \PharData ($branch .'/branch.tar');
        $archive->extractTo ($branch, null, true);

        rename ($branch .'/'. $archive->current ()->getFilename (), $folder .'/'. $this->name);

        unset ($archive);
        \PharData::unlinkArchive ($branch .'/branch.tar');
        @unlink ($branch .'/branch.tar');

        $file = $folder .'/'. $this->name .'/qero-package.json';

        $data = file_exists ($file) ?
            json_decode (file_get_contents ($file), true) : [];

        $data['name']    = $this->name;
        $data['node_id'] = $info['node_id'];

        if ($this->version)
            $data['version'] = $this->version;

        $this->node_id = $info['node_id'];

        file_put_contents ($file, json_encode ($data, JSON_PRETTY_PRINT));

        return true;
    }

    public function toString (): string
    {
        return ($this->name ?? 'vendor/package') . ($this->version !== null ?
            '@'. $this->version : '');
    }

    public function fromString (string $package): Package
    {
        if (($pos = strpos ($package, '@')) !== false)
        {
            $this->version = substr ($package, $pos + 1);

            $package = substr ($package, 0, $pos);
        }

        $this->name = strpos ($package, '/') === false ?
            $package .'/'. $package : $package;

        return $this;
    }

    public function load (): Package
    {
        if (file_exists ($file = QERO_DIR .'/qero-packages/'. $this->name .'/qero-package.json'))
        {
            $this->dir = 'qero-packages/'. $this->name;

            $package = json_decode (file_get_contents ($file), true);

            foreach ($package as $name => $value)
                $this->$name = $value;
        }

        return $this;
    }

    public function exists (): bool
    {
        return $this->name && file_exists (QERO_DIR .'/qero-packages/'. $this->name .'/qero-package.json');
    }

    public function save (): Package
    {
        $package = [];

        foreach ([
            'name', 'author', 'version', 'requires',
            'scripts', 'after_install', 'entry_point', 'node_id'
        ] as $name)
            if ($this->$name !== null)
                $package[$name] = $this->$name;

        file_put_contents ($this->dir .'/qero-package.json', json_encode ($package, JSON_PRETTY_PRINT));

        return $this;
    }
}
