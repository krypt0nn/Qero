<?php

ini_set ('phar.readonly', 0);
date_default_timezone_set ('UTC');

$begin = microtime (true);
$phar  = new Phar ('qero.phar');

$phar->buildFromDirectory ('build');

echo PHP_EOL . PHP_EOL .'   Qero build completed!'. PHP_EOL . PHP_EOL;

echo '   Builded for '. round (microtime (true) - $begin, 4) .' sec.'. PHP_EOL;
echo '   File size: '. round (filesize ('qero.phar') / 1024, 2) .' Kb'. PHP_EOL;
echo '   PHP version: '. phpversion () . PHP_EOL;
echo '   Date: '. date ('d/m/Y H:i:s') .' (UTC, timestamp: '. time () .')'. PHP_EOL . PHP_EOL;

echo '   Checksums'. PHP_EOL;
echo '      SHA1:    '. strtoupper (sha1_file ('qero.phar')) . PHP_EOL;
echo '       MD5:    '. strtoupper (md5_file ('qero.phar')) . PHP_EOL;
echo '     CRC32:    '. crc32 (file_get_contents ('qero.phar')) . PHP_EOL . PHP_EOL;
