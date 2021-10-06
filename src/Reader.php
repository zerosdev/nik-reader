<?php

namespace ZerosDev\NikReader;

use DateTime;
use Exception;

class Reader
{
    private $instanceUsed = false;
    private $fetchNew = false;

    private static $database;
    private static $filemtime;

    public $valid = false;
    public $nik;
    public $province_id;
    public $province;
    public $city_id;
    public $city;
    public $subdistrict_id;
    public $subdistrict;
    public $postal_code;
    public $born_date;
    public $zodiac;
    public $age = array(
        'year'  => null,
        'month' => null,
        'day'   => null
    );
    public $gender;
    public $unique_code;

    /**
     * Constructor.
     *
     * @param string|null $nik
     * @param string|null $database
     */
    public function __construct(string $nik = null)
    {
        if (! is_null($nik)) {
            $this->setNik($nik);
        }

        $database = dirname(__DIR__) . '/database/database.json';

        $this->setDatabase($database);
    }

    /**
     * Set nomor NIK.
     *
     * @param string $nik
     */
    private function setNik(string $nik)
    {
        $this->nik = $nik;

        return $this;
    }

    /**
     * Cek validitas nomor NIK.
     *
     * @return bool
     */
    public function valid()
    {
        return $this->valid = (
            is_string($this->nik)
            && preg_match("/^[0-9]+$/is", $this->nik)
            && strlen($this->nik) === 16
            && $this->getProvince()
            && $this->getCity()
            && $this->getSubdistrict()
            && $this->getBornDate()
        );
    }

    /**
     * Read data
     */
    public function read(string $nik = null)
    {
        $instance = $this->instanceUsed ? new self() : $this;

        if (! is_null($nik)) {
            $instance->setNik($nik);
        }

        $instance->fetchNew = true;

        $instance->getProvince();
        $instance->getCity();
        $instance->getSubdistrict();
        $instance->getPostalCode();
        $instance->getBornDate();
        $instance->getAge();
        $instance->getZodiac();
        $instance->getGender();
        $instance->getUniqueCode();
        $instance->valid();

        $instance->instanceUsed = true;

        return $instance;
    }

    /**
     * Set database dan baca isinya.
     *
     * @param string $file
     */
    public function setDatabase(string $file)
    {
        if (! is_file($file) || ! is_readable($file)) {
            throw new Exceptions\InvalidDatabaseException(sprintf(
                'The database file cannot be found or not readable: %s',
                $file
            ));
        }

        if (static::$filemtime <= filemtime($file)) {
            $database = file_get_contents($file);
            $database = json_decode($database);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exceptions\InvalidDatabaseException(sprintf(
                    'Unable to decode database contents: %s (%d)',
                    $file,
                    json_last_error()
                ));
            }

            static::$database = $database;
            static::$filemtime = filemtime($file);
        }

        return $this;
    }

    /**
     * Get data provinsi dari NIK.
     *
     * @return string|null
     */
    public function getProvince()
    {
        if ($this->province && ! $this->fetchNew) {
            return $this->province;
        }

        $this->province_id = substr($this->nik, 0, 2);

        return $this->province = (
            isset(static::$database->provinces->{$this->province_id})
                ? static::$database->provinces->{$this->province_id}
                : null
        );
    }

    /**
     * Get data kabupaten/kota dari NIK.
     *
     * @return string|null
     */
    public function getCity()
    {
        if ($this->city && ! $this->fetchNew) {
            return $this->city;
        }

        $this->city_id = substr($this->nik, 0, 4);

        return $this->city = (
            isset(static::$database->cities->{$this->city_id})
                ? static::$database->cities->{$this->city_id}
                : null
        );
    }

    /**
     * Get data kecamatan dari NIK.
     *
     * @return string|null
     */
    public function getSubdistrict()
    {
        if ($this->subdistrict && ! $this->fetchNew) {
            return $this->subdistrict;
        }

        $this->subdistrict_id = substr($this->nik, 0, 6);

        $this->subdistrict = (
            isset(static::$database->subdistricts->{$this->subdistrict_id})
                ? static::$database->subdistricts->{$this->subdistrict_id}
                : null
        );

        if (! is_null($this->subdistrict)) {
            $this->subdistrict = explode(' -- ', $this->subdistrict);
            $this->subdistrict = isset($this->subdistrict[0]) ? $this->subdistrict[0] : null;
        }

        return $this->subdistrict;
    }

    /**
     * Get kode pos
     *
     * @return string|null
     */
    public function getPostalCode()
    {
        if ($this->postal_code && ! $this->fetchNew) {
            return $this->postal_code;
        }

        $code = substr($this->nik, 0, 6);

        $subdistrict = (
            isset(static::$database->subdistricts->{$code})
                ? static::$database->subdistricts->{$code}
                : null
        );

        if (! is_null($subdistrict)) {
            $subdistrict = explode(' -- ', $subdistrict);
            $this->postal_code = isset($subdistrict[1]) ? $subdistrict[1] : null;
        }

        return $this->postal_code;
    }

    /**
     * Get data tanggal lahir dari NIK.
     *
     * @return string|null
     */
    public function getBornDate()
    {
        if ($this->born_date && ! $this->fetchNew) {
            return $this->born_date;
        }

        $code = substr($this->nik, 6, 6);
        list($day, $month, $year) = str_split($code, 2);

        if (intval($day) > 31 && intval($day) <= 40) {
            return $this->born_date = null;
        }

        $day = (intval($day) > 40) ? (intval($day) - 40) : $day;

        $max = date('Y') - 17;
        $min = 1945;

        $temp = '20' . $year;
        $low = '19' . $year;
        $high = '20' . $year;

        $year = ($temp > $min) ? (($high > $max) ? $low : $high) : $low;

        if ($year < $min) {
            return $this->born_date = null;
        }

        try {
            $parse = DateTime::createFromFormat(
                'd-m-Y',
                sprintf('%s-%s-%d', $day, $month, $year)
            );
            if ($parse !== false) {
                return $this->born_date = $parse->format('d-m-Y');
            } else {
                throw new Exception();
            }
        } catch (Exception $e) {
            return $this->born_date = null;
        }
    }

    /**
     * Get age precision
     *
     * @return array => string|null
     */
    public function getAge()
    {
        $born_date = $this->getBornDate();

        if (! $born_date) {
            return $this->age = array(
                'year' => null,
                'month' => null,
                'day' => null
            );
        }

        list($day, $month, $year) = explode('-', $born_date);

        $age = time() - strtotime($year . "-" .$month . "-" . $day);

        $this->age['year'] = abs(gmdate('Y', $age) - 1970);
        $this->age['month'] = abs(gmdate('m', $age) - 1);
        $this->age['day'] = abs(gmdate('d', $age) - 1);

        return $this->age;
    }

    /**
     * Get zodiac
     *
     * @return string|null
     */
    public function getZodiac()
    {
        if ($this->zodiac && ! $this->fetchNew) {
            return $this->zodiac;
        }

        list($day, $month) = str_split(substr($this->nik, 6, 4), 2);

        $day = intval($day);
        $month = intval($month);

        if ($day > 40) {
            $day = $day - 40;
        }

        foreach (static::$database->zodiacs as $data) {
            $range = explode('-', $data[0]);
            $rangeStart = explode('/', $range[0]);
            $rangeEnd = explode('/', $range[1]);

            if (($month <> intval($rangeStart[1])) && ($month <> intval($rangeEnd[1]))) {
                continue;
            }

            if ($day >= intval($rangeStart[0]) || $day <= intval($rangeEnd[0])) {
                $this->zodiac = $data[1];
                break;
            }
        }

        return $this->zodiac;
    }

    /**
     * Get gender type.
     *
     * @return string|null
     */
    public function getGender()
    {
        if ($this->gender && ! $this->fetchNew) {
            return $this->gender;
        }

        $day = substr($this->nik, 6, 2);

        if ($day > 40) {
            $this->gender = 'female';
        } else {
            $this->gender = 'male';
        }

        return $this->gender;
    }

    /**
     * Get unique_code
     *
     * @return string|null
     */
    public function getUniqueCode()
    {
        if ($this->unique_code && ! $this->fetchNew) {
            return $this->unique_code;
        }

        $code = substr($this->nik, 12, 4);

        return $this->unique_code = (strlen($code) === 4 ? $code : null);
    }

    /**
     * Convert to Array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'valid' => $this->valid,
            'nik' => $this->nik,
            'province_id' => $this->province_id,
            'province' => $this->province,
            'city_id' => $this->city_id,
            'city' => $this->city,
            'subdistrict_id' => $this->subdistrict_id,
            'subdistrict' => $this->subdistrict,
            'postal_code' => $this->postal_code,
            'born_date' => $this->born_date,
            'age' => $this->age,
            'zodiac' => $this->zodiac,
            'gender' => $this->gender,
            'unique_code' => $this->unique_code
        ];
    }

    /**
     * Convert to JSON
     *
     * @return string
     */
    public function toJSON($flags = 0)
    {
        return json_encode($this->toArray(), $flags);
    }
}
