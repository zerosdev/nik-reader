<?php

namespace ZerosDev\NikReader;

use DateTime;

class Reader
{
    /**
     * @var bool
     */
    private $fetchNew = false;

    /**
     * @var object|null
     */
    private static $database;

    /**
     * @var int|null
     */
    private static $filemtime;

    /**
     * @var bool
     */
    public $valid = false;

    /**
     * @var string|null
     */
    public $nik;

    /**
     * @var string|null
     */
    public $province_id;

    /**
     * @var string|null
     */
    public $province;

    /**
     * @var string|null
     */
    public $city_id;

    /**
     * @var string|null
     */
    public $city;

    /**
     * @var string|null
     */
    public $subdistrict_id;

    /**
     * @var string|null
     */
    public $subdistrict;

    /**
     * @var string|null
     */
    public $postal_code;

    /**
     * @deprecated Use $date_of_birth
     * @var string|null
     */
    public $born_date;

    /**
     * @var string|null
     */
    public $date_of_birth;

    /**
     * @var string|null
     */
    public $zodiac;

    /**
     * @var array<string,int|null>
     */
    public $age = array(
        'year'  => null,
        'month' => null,
        'day'   => null
    );

    /**
     * @var string|null
     */
    public $gender;

    /**
     * @var string|null
     */
    public $unique_code;

    /**
     * Constructor.
     *
     * @param string|null $nik
     */
    public function __construct(?string $nik = null)
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
    private function setNik(string $nik): self
    {
        $this->nik = is_scalar($nik) ? trim((string) $nik) : $nik;

        return $this;
    }

    /**
     * Reset computed values.
     *
     * @return void
     */
    private function reset(): void
    {
        $this->valid = false;
        $this->province_id = null;
        $this->province = null;
        $this->city_id = null;
        $this->city = null;
        $this->subdistrict_id = null;
        $this->subdistrict = null;
        $this->postal_code = null;
        $this->born_date = null;
        $this->date_of_birth = null;
        $this->age = array('year' => null, 'month' => null, 'day' => null);
        $this->zodiac = null;
        $this->gender = null;
        $this->unique_code = null;
    }

    /**
     * Check if the current NIK has a valid numeric format.
     *
     * @return bool
     */
    public function isValidNik(): bool
    {
        if (! is_string($this->nik) || ! preg_match('/^[0-9]{16}$/', $this->nik)) {
            return false;
        }

        $day = intval(substr($this->nik, 6, 2));
        $month = intval(substr($this->nik, 8, 2));

        if ($day < 1 || $day > 71 || $month < 1 || $month > 12) {
            return false;
        }

        return true;
    }

    /**
     * Cek validitas nomor NIK.
     *
     * @return bool
     */
    public function valid(): bool
    {
        return $this->valid = (
            $this->isValidNik()
            && $this->getProvince()
            && $this->getCity()
            && $this->getSubdistrict()
            && $this->getDateOfBirth()
        );
    }

    /**
     * Read data (modifies current instance)
     */
    public function read(?string $nik = null): self
    {
        if (! is_null($nik)) {
            $this->setNik($nik);
        }

        $this->fetchNew = true;
        $this->reset();

        $this->getProvince();
        $this->getCity();
        $this->getSubdistrict();
        $this->getPostalCode();
        $this->getDateOfBirth();
        $this->getAge();
        $this->getZodiac();
        $this->getGender();
        $this->getUniqueCode();
        $this->valid();

        $this->fetchNew = false;

        return $this;
    }

    /**
     * Set database dan baca isinya.
     *
     * @param string $file
     */
    public function setDatabase(string $file): self
    {
        if (! is_string($file) || ! is_file($file) || ! is_readable($file)) {
            throw new Exceptions\InvalidDatabaseException(sprintf(
                'The database file cannot be found or not readable: %s',
                $file
            ));
        }

        $mtime = filemtime($file);
        if ($mtime === false) {
            throw new Exceptions\InvalidDatabaseException(sprintf(
                'Unable to read database file metadata: %s',
                $file
            ));
        }

        if (static::$database === null || static::$filemtime < $mtime) {
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
            static::$filemtime = $mtime;
        }

        return $this;
    }

    /**
     * Get data provinsi dari NIK.
     *
     * @return string|null
     */
    public function getProvince(): ?string
    {
        if ($this->province !== null && ! $this->fetchNew) {
            return $this->province;
        }

        if (! $this->isValidNik()) {
            return $this->province = null;
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
    public function getCity(): ?string
    {
        if ($this->city !== null && ! $this->fetchNew) {
            return $this->city;
        }

        if (! $this->isValidNik()) {
            return $this->city = null;
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
    public function getSubdistrict(): ?string
    {
        if ($this->subdistrict !== null && ! $this->fetchNew) {
            return $this->subdistrict;
        }

        if (! $this->isValidNik()) {
            return $this->subdistrict = null;
        }

        $this->subdistrict_id = substr($this->nik, 0, 6);

        $this->subdistrict = (
            isset(static::$database->subdistricts->{$this->subdistrict_id})
                ? static::$database->subdistricts->{$this->subdistrict_id}
                : null
        );

        if (! is_null($this->subdistrict)) {
            $subdistrict = explode(' -- ', $this->subdistrict);
            $this->subdistrict = isset($subdistrict[0]) ? $subdistrict[0] : null;
        }

        return $this->subdistrict;
    }

    /**
     * Get kode pos
     *
     * @return string|null
     */
    public function getPostalCode(): ?string
    {
        if ($this->postal_code !== null && ! $this->fetchNew) {
            return $this->postal_code;
        }

        if (! $this->isValidNik()) {
            return $this->postal_code = null;
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
    public function getDateOfBirth(): ?string
    {
        if ($this->date_of_birth !== null && ! $this->fetchNew) {
            return $this->date_of_birth;
        }

        if (! $this->isValidNik()) {
            return $this->date_of_birth = null;
        }

        $code = substr($this->nik, 6, 6);
        list($rawDay, $rawMonth, $rawYear) = str_split($code, 2);

        $day = intval($rawDay);
        $month = intval($rawMonth);
        $year = intval($rawYear);

        if ($day > 40) {
            $day -= 40;
        }

        if ($day < 1 || $day > 31) {
            return $this->date_of_birth = null;
        }

        $currentYear = intval(date('Y'));
        $max = $currentYear - 17;
        $min = 1945;

        $high = 2000 + $year;
        $low = 1900 + $year;

        $year = ($high > $max) ? $low : $high;

        if ($year < $min || $year > $max) {
            return $this->date_of_birth = null;
        }

        $formatted = sprintf('%02d-%02d-%04d', $day, $month, $year);
        $parse = DateTime::createFromFormat('d-m-Y', $formatted);

        if ($parse === false) {
            return $this->date_of_birth = null;
        }

        $value = $parse->format('d-m-Y');
        $this->date_of_birth = $value;
        $this->born_date = $value;

        return $value;
    }

    /**
     * @deprecated Use getDateOfBirth()
     *
     * @return string|null
     */
    public function getBornDate(): ?string
    {
        if (! defined('E_USER_DEPRECATED')) {
            define('E_USER_DEPRECATED', 16384);
        }

        trigger_error('getBornDate() is deprecated. Use getDateOfBirth() instead.', E_USER_DEPRECATED);

        return $this->getDateOfBirth();
    }

    /**
     * Get age precision
     *
     * @return array => string|null
     */
    public function getAge(): array
    {
        if ($this->age['year'] !== null && ! $this->fetchNew) {
            return $this->age;
        }

        $born_date = $this->getDateOfBirth();

        if (! $born_date) {
            return $this->age = array(
                'year' => null,
                'month' => null,
                'day' => null
            );
        }

        list($day, $month, $year) = explode('-', $born_date);

        $born = DateTime::createFromFormat('Y-m-d', sprintf('%s-%s-%s', $year, $month, $day));
        if ($born === false) {
            return $this->age = array(
                'year' => null,
                'month' => null,
                'day' => null
            );
        }

        $now = new DateTime();
        $diff = $now->diff($born);

        return $this->age = array(
            'year' => intval($diff->y),
            'month' => intval($diff->m),
            'day' => intval($diff->d)
        );
    }

    /**
     * Get zodiac
     *
     * @return string|null
     */
    public function getZodiac(): ?string
    {
        if ($this->zodiac !== null && ! $this->fetchNew) {
            return $this->zodiac;
        }

        if (! $this->isValidNik()) {
            return $this->zodiac = null;
        }

        list($rawDay, $rawMonth) = str_split(substr($this->nik, 6, 4), 2);

        $day = intval($rawDay);
        $month = intval($rawMonth);

        if ($day > 40) {
            $day -= 40;
        }

        if ($day < 1 || $day > 31 || $month < 1 || $month > 12) {
            return $this->zodiac = null;
        }

        $target = intval(sprintf('%02d%02d', $month, $day));

        foreach (static::$database->zodiacs as $data) {
            $range = explode('-', $data[0]);
            $rangeStart = explode('/', $range[0]);
            $rangeEnd = explode('/', $range[1]);

            $cd1 = intval($rangeStart[0]);
            $cm1 = intval($rangeStart[1]);

            $cd2 = intval($rangeEnd[0]);
            $cm2 = intval($rangeEnd[1]);

            $start = intval(sprintf('%02d%02d', $cm1, $cd1));
            $end = intval(sprintf('%02d%02d', $cm2, $cd2));

            if ($start <= $end) {
                if ($target >= $start && $target <= $end) {
                    $this->zodiac = $data[1];
                    break;
                }
            } else {
                if ($target >= $start || $target <= $end) {
                    $this->zodiac = $data[1];
                    break;
                }
            }
        }

        return $this->zodiac;
    }

    /**
     * Get gender type.
     *
     * @return string|null
     */
    public function getGender(): ?string
    {
        if ($this->gender !== null && ! $this->fetchNew) {
            return $this->gender;
        }

        if (! $this->isValidNik()) {
            return $this->gender = null;
        }

        $day = intval(substr($this->nik, 6, 2));

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
    public function getUniqueCode(): ?string
    {
        if ($this->unique_code !== null && ! $this->fetchNew) {
            return $this->unique_code;
        }

        if (! $this->isValidNik()) {
            return $this->unique_code = null;
        }

        $code = substr($this->nik, 12, 4);

        return $this->unique_code = (strlen($code) === 4 ? $code : null);
    }

    /**
     * Convert to Array
     *
     * @return array
     */
    public function toArray(): array
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
            'date_of_birth' => $this->date_of_birth,
            'age' => $this->age,
            'zodiac' => $this->zodiac,
            'gender' => $this->gender,
            'unique_code' => $this->unique_code
        ];
    }

    /**
     * Convert to JSON
     * 
     * @param int $flags JSON encode flags (default: 0)
     * @return string
     */
    public function toJSON(int $flags = 0): string
    {
        return json_encode($this->toArray(), $flags);
    }
}
