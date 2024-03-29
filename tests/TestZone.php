<?php

namespace Tests;

use DateTime;
use DateTimeZone;
use Fize\Datetime\Zone;
use PHPUnit\Framework\TestCase;

class TestZone extends TestCase
{

    public function testGet()
    {
        $dt = new DateTime();
        $timezone = Zone::get($dt);
        var_dump($timezone);
        self::assertIsObject($timezone);
    }

    public function testSet()
    {
        $dt = new DateTime();
        $timezone = new DateTimeZone('Asia/Shanghai');
        $dt_real = Zone::set($dt, $timezone);
        var_dump($dt_real);
        self::assertIsObject($dt_real);
    }

    public function testDefaultGet()
    {
        $timezone = Zone::defaultGet();
        var_dump($timezone);
        self::assertIsString($timezone);
    }

    public function testDefaultSet()
    {
        $timezone = Zone::defaultGet();
        var_dump($timezone);
        self::assertIsString($timezone);

        $timezone_identifier = 'Asia/Shanghai';
        $result = Zone::defaultSet($timezone_identifier);
        self::assertTrue($result);

        $timezone = Zone::defaultGet();
        var_dump($timezone);
        self::assertIsString($timezone);
    }

    public function testNameFromAbbr()
    {
        $name = Zone::nameFromAbbr('GET');
        var_dump($name);
        self::assertIsString($name);
    }

    public function testVersionGet()
    {
        $version = Zone::versionGet();
        var_dump($version);
        self::assertIsString($version);
    }
}
