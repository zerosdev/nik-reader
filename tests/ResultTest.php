<?php

use Exception;
use PHPUnit\Framework\TestCase;
use ZerosDev\NikReader\Reader;
use ZerosDev\NikReader\Exceptions\InvalidDatabaseException;

class ResultTest extends TestCase
{
    private $reader;

    protected function setUp()
    {
        $this->reader = new Reader();
    }

    protected function tearDown()
    {
        $this->reader = null;
    }

    public function testInvalidDatabase()
    {
        try {
            $this->reader->setDatabase(__DIR__.'/invalid-database.json');
        } catch (Exception $e) {
            $this->assertInstanceOf(InvalidDatabaseException::class, $e);
        }
    }

    public function testValidNik()
    {
        $nik = '3502200101910001';
        $result = $this->reader->read($nik);

        $this->assertTrue($result->valid);
        $this->assertFalse(! $result->valid);
    }

    public function testInvalidNik()
    {
        $nik = '3502203201910001';
        $reader = new Reader();
        $result = $this->reader->read($nik);

        $this->assertTrue(! $result->valid);
        $this->assertFalse($result->valid);
    }

    public function testValidBirthday()
    {
        $nik = '3502200101910001';
        $result = $this->reader->read($nik);

        $this->assertEquals('01-01-1991', $result->birthday);
    }

    public function testInvalidBirthday()
    {
        $nik = '3502203201910001';
        $result = $this->reader->read($nik);

        $this->assertNull($result->birthday);
    }

    public function testInvalidNikLength()
    {
        $nik = '350220320191000';
        $reader = new Reader();
        $result = $this->reader->read($nik);

        $this->assertTrue(! $result->valid);
        $this->assertFalse($result->valid);
    }

    public function testInvalidNikChars()
    {
        $nik = '350P2001Q191J00L';
        $reader = new Reader();
        $result = $this->reader->read($nik);

        $this->assertTrue(! $result->valid);
        $this->assertFalse($result->valid);
    }

    public function testReadMultipleNik()
    {
        $nik = '3502200101910001';
        $nik2 = '3502201101910001';

        $result = $this->reader->read($nik);
        $result2 = $this->reader->read($nik2);

        $this->assertEquals('01-01-1991', $result->birthday);
        $this->assertEquals('11-01-1991', $result2->birthday);
    }

    public function testRegion()
    {
        $nik = '3502200101910001';

        $result = $this->reader->read($nik);

        $this->assertEquals('JAWA TIMUR', strtoupper($result->province));
        $this->assertEquals('KAB. PONOROGO', strtoupper($result->city));
        $this->assertEquals('JAMBON', strtoupper($result->subdistrict));
    }
}
