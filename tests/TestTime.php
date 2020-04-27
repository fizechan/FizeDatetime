<?php


use fize\datetime\Time;
use PHPUnit\Framework\TestCase;

class TestTime extends TestCase
{

    public function testGettimeofday()
    {
        $time1 = Time::gettimeofday(true);
        var_dump($time1);
        self::assertIsFloat($time1);

        $time2 = Time::gettimeofday();
        var_dump($time2);
        self::assertIsArray($time2);
    }

    public function testGmmktime()
    {
        $timestamp = Time::gmmktime();
        var_dump($timestamp);
        self::assertIsInt($timestamp);
    }

    public function testGmstrftime()
    {
        $time = Time::gmstrftime("%b %d %Y %H:%M:%S", mktime (20,0,0,12,31,98));
        var_dump($time);
        self::assertIsString($time);
    }

    public function testLocaltime()
    {
        $time1 = Time::localtime();
        var_dump($time1);
        self::assertIsArray($time1);

        $time2 = Time::localtime(time(), true);
        var_dump($time2);
        self::assertIsArray($time2);
    }

    public function testMicrotime()
    {
        $time1 = Time::microtime();
        var_dump($time1);
        self::assertIsString($time1);

        $time2 = Time::microtime(true);
        var_dump($time2);
        self::assertIsFloat($time2);
    }

    public function testMktime()
    {
        $mktime = Time::mktime();
        var_dump($mktime);
        self::assertIsInt($mktime);
    }

    public function testStrftime()
    {
        $time = Time::strftime("%b %d %Y %H:%M:%S", mktime (20,0,0,12,31,98));
        var_dump($time);
        self::assertIsString($time);
    }

    public function testStrptime()
    {
        if(strtoupper(substr(PHP_OS,0,3))==='WIN') {
            echo 'strptime函数未在 Windows 平台下实现。';
            self::assertFalse(false);
            return;
        }

        $format = '%d/%m/%Y %H:%M:%S';
        $strf = strftime($format);
        var_dump($strf);
        $strp = Time::strptime($strf, $format);
        var_dump($strp);
        self::assertIsArray($strp);
    }

    public function testStrtotime()
    {
        $time = Time::strtotime("now");
        var_dump($time);
        self::assertIsInt($time);
    }
}
