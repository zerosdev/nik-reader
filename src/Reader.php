<?php

namespace ZerosDev\NikReader;

use DateTime;
use Exception;

class Reader
{
    private $nik;
    private $fetchNew = false;

    private static $database;
    private static $filemtime;

    public $province_id;
    public $province;
    public $city_id;
    public $city;
    public $subdistrict_id;
    public $subdistrict;
    public $postal_code;
    public $birthday;
    public $gender;
    public $unique_id;

    /**
     * Constructor.
     *
     * @param string|null $nik
     * @param string|null $database
     */
    public function __construct(string $nik = null, string $database = null)
    {
        if (! is_null($nik)) {
            $this->setNik($nik);
        }

        $database = ! is_null($database) ? $database : dirname(__DIR__) . '/database/database.json';

        $this->setDatabase($database);
    }

    /**
     * Cek validitas nomor NIK.
     *
     * @return bool
     */
    public function isValid()
    {
        return is_string($this->nik) && strlen($this->nik) === 16;
    }

    /**
     * Set nomor NIK.
     *
     * @param string $nik
     */
    public function setNik(string $nik)
    {
        $this->nik = $nik;

        if (! $this->isValid()) {
            throw new Exceptions\InvalidNikNumberException(sprintf(
                'NIK number should be a 16-digit numeric string. Got: %s (%d)',
                gettype($nik),
                strlen($nik)
            ));
        }

        return $this;
    }

    /**
     * Read data
     */
    public function read(string $nik = null)
    {
        if (! is_null($nik)) {
            $this->setNik($nik);
        }

        $this->fetchNew = true;

        $this->getProvince();
        $this->getCity();
        $this->getSubdistrict();
        $this->getPostalCode();
        $this->getBirthday();
        $this->getGender();
        $this->getUniqueId();

        return $this;
    }

    /**
     * Set database dan baca isinya.
     *
     * @param string $file
     */
    public function setDatabase(string $file)
    {
        if (! is_file($file) || ! is_readable($file)) {
            throw new Exceptions\InvalidDatabaseWilayahException(sprintf(
                'The database file cannot be found or not readable: %s',
                $file
            ));
        }

        if (static::$filemtime <= filemtime($file)) {
            $database = file_get_contents($file);
            $database = json_decode($database);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exceptions\InvalidDatabaseWilayahException(sprintf(
                    'Unable to decode database contents: %s',
                    $file
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
            isset(static::$database->provinsi->{$this->province_id})
                ? static::$database->provinsi->{$this->province_id}
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
            isset(static::$database->kabkot->{$this->city_id})
                ? static::$database->kabkot->{$this->city_id}
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
            isset(static::$database->kecamatan->{$this->subdistrict_id})
                ? static::$database->kecamatan->{$this->subdistrict_id}
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
            isset(static::$database->kecamatan->{$code})
                ? static::$database->kecamatan->{$code}
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
    public function getBirthday()
    {
        if ($this->birthday && ! $this->fetchNew) {
            return $this->birthday;
        }

        $code = substr($this->nik, 6, 6);
        list($day, $month, $year) = str_split($code, 2);

        $day = (intval($day) > 40) ? (intval($day) - 40) : $day;

        $max = date('Y') - 17;
        $min = 1945;

        $temp = '20' . $year;
        $low = '19' . $year;
        $high = '20' . $year;

        $year = ($temp > $min) ? (($high > $max) ? $low : $high) : $low;

        if ($year < $min) {
            throw new Exceptions\InvalidDateOfBirthException(sprintf(
                'Unable to parse date of birth (%s) from an invalid NIK number (%s)',
                $code,
                $this->nik
            ));
        }

        try {
            $parse = DateTime::createFromFormat(
                'd-m-Y',
                sprintf('%s-%s-%d', $day, $month, $year)
            );
            if ($parse !== false) {
                return $this->birthday = $parse->format('d-m-Y');
            } else {
                throw new Exception();
            }
        } catch (Exception $e) {
            throw new Exceptions\InvalidDateOfBirthException(sprintf(
                'Unable to parse date of birth (%s) from an invalid NIK number (%s)',
                $code,
                $this->nik
            ));
        }
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
     * Get unique id.
     *
     * @return string|null
     */
    public function getUniqueId()
    {
        if ($this->unique_id && ! $this->fetchNew) {
            return $this->unique_id;
        }

        $code = substr($this->nik, 12, 4);

        return $this->unique_id = (strlen($code) === 4 ? $code : null);
    }

    /**
     * Convert to Array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'province_id' => $this->province_id,
            'province' => $this->province,
            'city_id' => $this->city_id,
            'city' => $this->city,
            'subdistrict_id' => $this->subdistrict_id,
            'subdistrict' => $this->subdistrict,
            'postal_code' => $this->postal_code,
            'birthday' => $this->birthday,
            'gender' => $this->gender,
            'unique_id' => $this->unique_id
        ];
    }

    /**
     * Convert to JSON
     *
     * @return string
     */
    public function toJSON()
    {
        return json_encode($this->toArray());
    }
}
