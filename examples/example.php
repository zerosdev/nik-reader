<?php

require dirname(__DIR__).'/vendor/autoload.php';

use ZerosDev\NikReader\Reader;

$nik = '3502200101040001';

$reader = new Reader();

$result = $reader->read($nik);
$province = $result->getProvince();
$city = $result->getCity();
$subdistrict = $result->getSubdistrict();
// ..........

$json = $reader->toJSON();

print_r($json);
