<?php

namespace Tests;

use Fize\Datetime\DateTime;
use PHPUnit\Framework\TestCase;

class TestDateTime extends TestCase
{

    public function testHuman()
    {
        $str = DateTime::human(strtotime('2019-10-11 11:14:15'));
        var_dump($str);
        self::assertIsString($str);
    }

    public function testIsBetween()
    {
        $bool1 = DateTime::isBetween('2019-10-11 11:14:15', '2018-10-11 11:14:15', '2020-10-11 11:14:15');
        self::assertTrue($bool1);
        $bool2 = DateTime::isBetween('2019-10-11 11:14:15', '2018-10-11 11:14:15', '2017-10-11 11:14:15');
        self::assertFalse($bool2);
    }
}
