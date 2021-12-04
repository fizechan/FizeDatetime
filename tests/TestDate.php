<?php

namespace Tests;

use Fize\Datetime\Date;
use Fize\Datetime\DateTime;
use PHPUnit\Framework\TestCase;

class TestDate extends TestCase
{

    public function testCheck()
    {
        $check1 = Date::check(11, 2, 2019);
        self::assertTrue($check1);
        $check2 = Date::check(2, 30, 2019);
        self::assertFalse($check2);
    }

    public function testParseFromFormat()
    {
        $format = 'Y-m-d H:i:s';
        $date = date($format);
        var_dump($date);
        $parse = Date::parseFromFormat($format, $date);
        var_dump($parse);
        self::assertIsArray($parse);
    }

    public function testParse()
    {
        $format = 'Y-m-d H:i:s';
        $date = date($format);
        $parse = Date::parse($date);
        var_dump($parse);
        self::assertIsArray($parse);
    }

    public function testSunInfo()
    {
        $sun_info = Date::sunInfo(time(), 24.48923061, 118.10388605);
        var_dump($sun_info);
        self::assertIsArray($sun_info);
    }

    public function testSunrise()
    {
        $sunrise1 = Date::sunrise(time(), SUNFUNCS_RET_STRING);
        var_dump($sunrise1);
        self::assertIsString($sunrise1);

        $sunrise2 = Date::sunrise(time(), SUNFUNCS_RET_DOUBLE);
        var_dump($sunrise2);
        self::assertIsFloat($sunrise2);

        $sunrise3 = Date::sunrise(time(), SUNFUNCS_RET_TIMESTAMP);
        var_dump($sunrise3);
        self::assertIsInt($sunrise3);
    }

    public function testSunset()
    {
        $sunset1 = Date::sunset(time(), SUNFUNCS_RET_STRING);
        var_dump($sunset1);
        self::assertIsString($sunset1);

        $sunset2 = Date::sunset(time(), SUNFUNCS_RET_DOUBLE);
        var_dump($sunset2);
        self::assertIsFloat($sunset2);

        $sunset3 = Date::sunset(time(), SUNFUNCS_RET_TIMESTAMP);
        var_dump($sunset3);
        self::assertIsInt($sunset3);
    }

    public function testGetdate()
    {
        $info = Date::getdate(time());
        var_dump($info);
        self::assertIsArray($info);
    }

    public function testGmdate()
    {
        $gmdate = Date::gmdate('Y-m-d H:i:s', time());
        var_dump($gmdate);
        self::assertIsString($gmdate);
    }

    public function testIdate()
    {
        $idate = Date::idate('Y', time());
        var_dump($idate);
        self::assertIsInt($idate);
        self::assertEquals($idate, date('Y'));
    }

    public function testGet()
    {
        $date = Date::get('2021-01-30', 1);
        self::assertEquals('2021-02-28', $date);

        $date = Date::get('2021-02-28', -1, 5);
        self::assertEquals('2021-01-31', $date);

        $date = Date::get('2021-01-5', 1, 5);
        self::assertEquals('2021-02-10', $date);

        $date = Date::get('2021-01-26', 1, 2);
        self::assertEquals('2021-02-28', $date);

        $date = Date::get('2021-01-26', 1, 5);
        self::assertEquals('2021-02-28', $date);
    }

    public function testDates()
    {
        $dates = Date::dates('2021-12-12', '2022-01-21');
        var_export($dates);
        self::assertIsArray($dates);
    }

    public function testDays()
    {
        $days = Date::days('2021-12-12', '2022-01-21');
        var_dump($days);
        self::assertIsInt($days);

        $days = Date::days('2021-12-12', '2022-01-21', true);
        var_dump($days);
        self::assertIsInt($days);
    }

    public function testSet()
    {
        $dt = new DateTime();
        $dt2 = Date::set($dt, 11);
        var_export($dt2);
        $dt3 = Date::set($dt, 32);
        var_export($dt3);
        self::assertNotEquals($dt2, $dt3);
    }
}
