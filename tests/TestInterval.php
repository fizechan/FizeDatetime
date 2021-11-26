<?php

namespace Tests;

use Fize\Datetime\Interval;
use PHPUnit\Framework\TestCase;

class TestInterval extends TestCase
{

    public function testDiff()
    {
        $diff = Interval::diff("2019-10-11 11:14:15.638276", '2012-07-08 11:14:15.889342');
        var_dump($diff);
        self::assertIsObject($diff);
    }
}
