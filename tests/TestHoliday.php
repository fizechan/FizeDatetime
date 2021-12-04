<?php

namespace Tests;

use Fize\Datetime\Holiday;
use PHPUnit\Framework\TestCase;

class TestHoliday extends TestCase
{

    public function testYear()
    {
        $holidays = Holiday::year(2021);
        var_export($holidays);
        self::assertIsArray($holidays);
    }
}
