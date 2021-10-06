<?php

require dirname(__DIR__).'/vendor/autoload.php';

use ZerosDev\NikReader\Reader;

$nik = '3502204101910002';

try {
    $reader = new Reader();
    $json = $reader->read($nik)->toJSON(JSON_PRETTY_PRINT);

    print_r($json);
} catch (\Exception $e) {
    print_r(get_class($e) . " => " .$e->getMessage());
}
