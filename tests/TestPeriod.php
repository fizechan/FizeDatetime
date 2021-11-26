<?php

namespace Tests;

use DateInterval;
use DateTime;
use DateTimeZone;
use Fize\Datetime\Period;
use PHPUnit\Framework\TestCase;

class TestPeriod extends TestCase
{

    public function testBetween()
    {
        $timezones = new DateTimeZone('Asia/Shanghai');
        $start = new DateTime('2008-08-08',$timezones);
        $end = new DateTime('2008-08-09',$timezones);
        $interval= new DateInterval('P0DT2H');
        $ranges = Period::between($start, $end, $interval);
        var_dump($ranges);
        self::assertIsObject($ranges);
        foreach ($ranges as $range)
        {
            var_dump($range);
            self::assertIsObject($range);
        }
    }
}
