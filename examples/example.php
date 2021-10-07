<?php

require dirname(__DIR__).'/vendor/autoload.php';

use ZerosDev\NikReader\Reader;

$nik = '3502204101910002';

try {
    $reader = new Reader();
    $result = $reader->read($nik);

    print_r($result->toJSON(JSON_PRETTY_PRINT));
} catch (\Exception $e) {
    print_r(get_class($e) . " => " .$e->getMessage());
}
