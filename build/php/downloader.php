<?php

namespace Qero\Requests;

use Qero\Packages\Package;
use function Qero\color;

use const Qero\PROGRESS_CHAR;

class Downloader
{
    public static function getTarball (Package $package): ?string
    {
        $begin = microtime (true);

        $progress = new \ProgressBar\ProgressBar (100, 30, color (' - Installing [yellow]'. str_replace (
            '@', '[reset]@', $package->toString ()) .'[reset]: Downloading '), function ($actual, $max) use ($begin)
            {
                $time = ($abs_time = microtime (true) - $begin) * ($max - $actual) / max ($actual, 1);
                
                $minutes = (int)($time / 60);
                $seconds = (int)($time - $minutes * 60);

                return ' '. ($minutes == 0 && $seconds == 0 ?
                    (($abs_time >= 60 ? (int)($abs_time / 60) .'m ' : '') .
                    ((int)($abs_time - (int)($abs_time / 60) * 60)) .'s') :

                    ('ETA: '. ($minutes > 0 ? $minutes .'m ' : '') .
                    ($seconds > 0 ? $seconds .'s' : '')));
            }, PROGRESS_CHAR);

        $archive = @Requester::get ('https://api.github.com/repos/'. $package->name .'/tarball'.
            ($package->version === null ? '' : '/'. $package->version), $progress);

        return $archive && substr ($archive, 0, 2) != '{"' ? $archive : null;
    }

    public static function getInfo (Package $package): ?array
    {
        $info = $package->version !== null ?
            @json_decode (@Requester::get ('https://api.github.com/repos/'. $package->name .'/releases/tags/'. $package->version), true) :
            @json_decode (@Requester::get ('https://api.github.com/repos/'. $package->name .'/commits'), true)[0];

        return isset ($info['node_id']) ?
            $info : null;
    }
}
