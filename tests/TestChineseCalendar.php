<?php

namespace Tests;

use Fize\Datetime\ChineseCalendar;
use PHPUnit\Framework\TestCase;

class TestChineseCalendar extends TestCase
{

    public function testLunar2gregorian()
    {
        $dt = ChineseCalendar::lunar2gregorian(2021, 11, 1);
        var_dump($dt);
        self::assertEquals('2021-12-04', $dt->format('Y-m-d'));
    }

    public function testGregorian2lunar()
    {
        $rst = ChineseCalendar::gregorian2lunar(2021, 12, 4);
        var_dump($rst);
        self::assertIsArray($rst);
    }

    public function testDays()
    {
        $days = ChineseCalendar::days(2021, 11);
        var_dump($days);
        self::assertIsInt($days);
    }

    public function testLeap()
    {
        $leapMonth = ChineseCalendar::leap(2021);
        var_dump($leapMonth);
        self::assertIsInt($leapMonth);
    }
}
