<?php

namespace ZerosDev\NikReader\Tests;

use DateTime;
use PHPUnit\Framework\TestCase;
use ZerosDev\NikReader\Reader;
use ZerosDev\NikReader\Exceptions\InvalidDatabaseException;

final class ResultTest extends TestCase
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * Set up test fixture.
     */
    protected function setUp(): void
    {
        $this->reader = new Reader();
    }

    /**
     * Tear down test fixture.
     */
    protected function tearDown(): void
    {
        $this->reader = null;
    }

    /**
     * Test for invalid database file.
     */
    public function testInvalidDatabase()
    {
        $this->expectException(InvalidDatabaseException::class);
        $this->reader->setDatabase(__DIR__.'/invalid-database.json');
    }

    /**
     * Test for valid NIK.
     */
    public function testValidNik()
    {
        $nik = '3502200101910001';
        $result = $this->reader->read($nik);

        $this->assertTrue($result->valid);
    }

    /**
     * Test for invalid NIK.
     */
    public function testInvalidNik()
    {
        $nik = '3502203201910001';
        $result = $this->reader->read($nik);

        $this->assertFalse($result->valid);
    }

    /**
     * Test for valid birth date.
     */
    public function testValidBirthDate()
    {
        $nik = '3502200101910001';
        $result = $this->reader->read($nik);

        $this->assertEquals('01-01-1991', $result->date_of_birth);
    }

    /**
     * Test for invalid birth date.
     */
    public function testInvalidBirthDate()
    {
        $nik = '3502203201910001';
        $result = $this->reader->read($nik);

        $this->assertNull($result->date_of_birth);
    }

    /**
     * Test for age calculation.
     */
    public function testAge()
    {
        $nik = '3502200101910001';
        $result = $this->reader->read($nik);

        $expected = (new DateTime())->diff(DateTime::createFromFormat('d-m-Y', '01-01-1991'));

        $this->assertEquals(intval($expected->y), $result->age['year']);
        $this->assertEquals(intval($expected->m), $result->age['month']);
        $this->assertEquals(intval($expected->d), $result->age['day']);
    }

    /**
     * Test for invalid NIK length.
     */
    public function testInvalidNikLength()
    {
        $nik = '350220320191000';
        $result = $this->reader->read($nik);

        $this->assertFalse($result->valid);
    }

    /**
     * Test for invalid NIK characters.
     */
    public function testInvalidNikChars()
    {
        $nik = '350P2001Q191J00L';
        $result = $this->reader->read($nik);

        $this->assertFalse($result->valid);
    }

    /**
     * Test for read multiple NIK with single Reader instance
     */
    public function testReadMultipleNik()
    {
        $nik = '3502200101910001';
        $nik2 = '3502201101910001';

        $result = $this->reader->read($nik);
        $this->assertEquals('01-01-1991', $result->date_of_birth);

        $result2 = $this->reader->read($nik2);
        $this->assertEquals('11-01-1991', $result2->date_of_birth);
    }

    /**
     * Test for region information.
     */
    public function testRegion()
    {
        $nik = '3502200101910001';

        $result = $this->reader->read($nik);

        $this->assertEquals('JAWA TIMUR', strtoupper($result->province));
        $this->assertEquals('KAB. PONOROGO', strtoupper($result->city));
        $this->assertEquals('JAMBON', strtoupper($result->subdistrict));
    }

    /**
     * Test for gender information.
     */
    public function testGender()
    {
        $maleNik = '3502200101910001';
        $femaleNik = '3502204101910001';

        $result = $this->reader->read($maleNik);
        $this->assertEquals('MALE', strtoupper($result->gender));

        $result2 = $this->reader->read($femaleNik);
        $this->assertEquals('FEMALE', strtoupper($result2->gender));
    }

    /**
     * Test for zodiac information.
     */
    public function testZodiac()
    {
        $nik = '3502200101910001';

        $result = $this->reader->read($nik);
        $this->assertEquals('CAPRICORN', strtoupper($result->zodiac));
    }
}
