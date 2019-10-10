<?php


use fize\datetime\Date;
use PHPUnit\Framework\TestCase;

class DateTest extends TestCase
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
}
