<?php
/** @noinspection PhpComposerExtensionStubsInspection */


use fize\datetime\Calendar;
use PHPUnit\Framework\TestCase;

class CalendarTest extends TestCase
{

    public function testDaysInMonth()
    {
        $days = Calendar::daysInMonth(CAL_GREGORIAN, 2, 2019);
        self::assertEquals($days, 28);
    }

    public function testFromJd()
    {
        $today = unixtojd(mktime(0, 0, 0, 8, 16, 2003));
        $cal = Calendar::fromJd($today, CAL_GREGORIAN);
        var_dump($cal);
        
        self::assertIsArray($cal);
        self::assertNotEmpty($cal);
    }

    public function testInfo()
    {
        $infos = Calendar::info();
        var_dump($infos);
        self::assertIsArray($infos);

        $info = Calendar::info(CAL_GREGORIAN);
        var_dump($info);
        self::assertIsArray($info);
    }

    public function testToJd()
    {
        $julian_days = Calendar::toJd(CAL_GREGORIAN, 10, 9, 2019);
        var_dump($julian_days);
        self::assertIsInt($julian_days);
    }

    public function testEasterDate()
    {
        $timestamp = Calendar::easterDate();
        var_dump($timestamp);
        self::assertIsInt($timestamp);
    }

    public function testEasterDays()
    {
        $days = Calendar::easterDays();
        var_dump($days);
        self::assertIsInt($days);
    }

    public function testFrenchToJd()
    {
        $julian_days = Calendar::frenchToJd(10, 9, 2019);
        var_dump($julian_days);
        self::assertIsInt($julian_days);
    }

    public function testGregorianToJd()
    {
        $julian_days = Calendar::gregorianToJd(10, 9, 2019);
        var_dump($julian_days);
        self::assertIsInt($julian_days);
    }

    public function testJdDayOfWeek()
    {
        $julian_days = Calendar::gregorianToJd(10, 9, 2019);
        $int_week = Calendar::jdDayOfWeek($julian_days);
        var_dump($int_week);
        self::assertIsInt($int_week);
        $string_week = Calendar::jdDayOfWeek($julian_days, CAL_DOW_LONG);
        var_dump($string_week);
        self::assertIsString($string_week);
    }

    public function testJdMonthName()
    {
        $julian_days = Calendar::gregorianToJd(10, 9, 2019);
        $month_name = Calendar::jdMonthName($julian_days, 2);
        var_dump($month_name);
        self::assertIsString($month_name);
    }

    public function testJdToFrench()
    {
        $julian_days = Calendar::toJd(CAL_GREGORIAN, 10, 9, 1980);
        $french = Calendar::jdToFrench($julian_days);
        var_dump($french);  //出现0/0/0的非预期情况
        self::assertIsString($french);
    }

    public function testJdToGregorian()
    {
        $julian_days = Calendar::toJd(CAL_GREGORIAN, 10, 9, 1980);
        $gregorian = Calendar::jdToGregorian($julian_days);
        var_dump($gregorian);
        self::assertIsString($gregorian);
    }

    public function testJdToJewish()
    {
        $julian_days = Calendar::toJd(CAL_GREGORIAN, 10, 9, 1980);
        $jewish = Calendar::jdToJewish($julian_days);
        var_dump($jewish);
        self::assertIsString($jewish);
    }

    public function testJdToJulian()
    {
        $julian_days = Calendar::toJd(CAL_GREGORIAN, 10, 9, 1980);
        $julian = Calendar::jdToJulian($julian_days);
        var_dump($julian);
        self::assertIsString($julian);
    }

    public function testJdToUnix()
    {
        $julian_days = Calendar::toJd(CAL_GREGORIAN, 10, 9, 1980);
        $unix = Calendar::jdToUnix($julian_days);
        var_dump($unix);
        self::assertIsInt($unix);
    }

    public function testJewishToJd()
    {
        $jd = Calendar::jewishToJd(10, 9, 2019);
        var_dump($jd);
        self::assertIsInt($jd);
    }

    public function testJulianToJd()
    {
        $jd = Calendar::julianToJd(10, 9, 2019);
        var_dump($jd);
        self::assertIsInt($jd);
    }

    public function testUnixToJd()
    {
        $jd = Calendar::unixToJd();
        var_dump($jd);
        self::assertIsInt($jd);
    }
}
