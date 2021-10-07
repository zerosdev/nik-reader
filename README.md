<h1 align="center">NIK Reader</h1>
<h6 align="center">Identity data reader based on NIK (Nomor Induk Kependudukan)</h6>

<p align="center">
  <img src="https://github.com/ZerosDev/nik-reader/workflows/build/badge.svg" alt="build"/>
  <img src="https://img.shields.io/github/v/release/ZerosDev/nik-reader?include_prereleases" alt="release"/>
  <img src="https://img.shields.io/github/languages/top/ZerosDev/nik-reader" alt="language"/>
  <img src="https://img.shields.io/github/license/ZerosDev/nik-reader" alt="license"/>
  <img src="https://img.shields.io/github/languages/code-size/ZerosDev/nik-reader" alt="size"/>
  <img src="https://img.shields.io/github/downloads/ZerosDev/nik-reader/total" alt="downloads"/>
  <img src="https://img.shields.io/badge/PRs-welcome-brightgreen.svg" alt="pulls"/>
</p>

## About

This library gives you a way to convert the NIK number into useful information such as: Region name (province, city, sub-district), date of birth, gender, zodiac, age, and more. This can also be used to validate whether the NIK number is valid or not.
Here is the example result :

| Name           | Type        | Example Value                              |
|----------------|-------------|---------------------------------------------
| valid          | Boolean     | true                                       |
| nik            | String      | 3502204101910001                           |
| province_id    | String/Null | 35                                         |
| province       | String/Null | JAWA TIMUR                                 |
| city_id        | String/Null | 02                                         |
| city           | String/Null | KAB. PONOROGO                              |
| subdistrict_id | String/Null | 20                                         |
| subdistrict    | String/Null | JAMBON                                     |
| postal_code    | String/Null | 63456                                      |
| born_date      | String/Null | 01-01-1991                                 |
| age            | Array       | ['year' => 30, 'month' => '9', 'day' => 6] |
| zodiac         | String/Null | Capricorn                                  |
| gender         | String/Null | female                                     |
| unique_code    | String/Null | 0001                                       |

## Installation

1. Run command
<pre><code>composer require zerosdev/nik-reader</code></pre>

## Usage

### Laravel

```php
// .........
public function method()
{
    $nik = '3502200101910001';
    $result = \NikReader::read($nik);
    
    if (true === $result->valid) {
        // code
    }
}
```

### Non-Laravel

```php
<?php

require 'path/to/your/composer/autoload.php';

use ZerosDev\NikReader\Reader;

$nik = '3502200101910001';
$reader = new Reader();
$result = $reader->read($nik);

if (true === $result->valid) {
    // code
}
```

### Available Methods

| Method                    | Description                      |
|---------------------------|----------------------------------|
| read(string $nik)         | Start reading NIK number         |
| valid()                   | Check wether NIK is valid or not |
| setDatabase(string $file) | Load database file               |
| getProvince()             | Get province data                |
| getCity()                 | Get city data                    |
| getSubdistrict()          | Get subdistrict data             |
| getPostalCode()           | Get postal code data             |
| getBornDate()             | Get date of birth data           |
| getAge()                  | Get age data                     |
| getZodiac()               | Get zodiac data                  |
| getGender()               | Get gender data                  |
| getUniqueCode()           | Get unique code                  |
| toArray()                 | Convert result into Array format |
| toJSON($flags)            | Convert result into JSON format  |