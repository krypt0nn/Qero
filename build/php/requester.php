<?php

namespace Qero\Requests;

use ProgressBar\ProgressBar;

class Requester
{
    public static function get (string $url, ProgressBar &$progressBar = null)
    {
        if (extension_loaded ('curl') && $curl = curl_init ($url))
        {
            curl_setopt_array ($curl, [
                CURLOPT_HEADER         => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_NOPROGRESS     => false,
                CURLOPT_SSLVERSION     => 4,

                CURLOPT_PROGRESSFUNCTION => function ($t, $download_size, $downloaded) use (&$progressBar)
                {
                    if ($progressBar)
                        $progressBar->update ((int)($downloaded / $download_size * 100));
                },

                CURLOPT_HTTPHEADER => [
                    'User-Agent: PHP'
                ]
            ]);

            $response = curl_exec ($curl);
            curl_close ($curl);

            if ($progressBar)
            {
                $progressBar->update (100);

                echo PHP_EOL;
            }

            return $response;
        }

        return file_get_contents ($url, false, stream_context_create ([
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            ],

            'http' => [
                'method' => 'GET',
                'header' => ['User-Agent: PHP'],
                'follow_location' => true
            ]
        ]));
    }
}
