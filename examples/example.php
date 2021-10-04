<?php

require dirname(__DIR__).'/vendor/autoload.php';

use ZerosDev\NikReader\Reader;

$nik = '3502200101200001';

$reader = new Reader();

$result = $reader->read($nik)->toArray();

print_r($result);
