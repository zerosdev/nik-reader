<?php

require dirname(__DIR__).'/vendor/autoload.php';

use ZerosDev\NikReader\Reader;

$nik = '3502200101910001';

$reader = new Reader($nik);

print_r($reader->isValid());
