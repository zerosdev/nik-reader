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

```json
{
    "valid": true,
    "nik": "3502200101000001",
    "province_id": "35",
    "province": "JAWA TIMUR",
    "city_id": "3502",
    "city": "KAB. PONOROGO",
    "subdistrict_id": "350220",
    "subdistrict": "JAMBON",
    "postal_code": "63456",
    "birthday": "01-01-2000",
    "age":
    {
        "year": 21,
        "month": 9,
        "day": 25
    },
    "zodiac": "Capricorn",
    "gender": "male",
    "unique_code": "0001"
} 
```

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

require 'path/to/your/vendor/autoload.php';

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
| read()                    | Start reading NIK number         |
| valid()                   | Check wether NIK is valid or not |
| setDatabase()             | Load database file               |
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
| toJSON()                  | Convert result into JSON format  |
