<?php

namespace Fize\Datetime;

use DateInterval;
use DatePeriod;
use DateTime;

/**
 * 时间周期类
 */
class Period extends DatePeriod
{

    /**
     * 两个时间的间隔迭代器
     * @param DateTime $start    开始时间
     * @param DateTime $end      结束时间
     * @param DateInterval    $interval 间隔时间
     * @return DatePeriod
     */
    public static function between(DateTime $start, DateTime $end, DateInterval $interval): DatePeriod
    {
        return new DatePeriod($start, $interval, $end);
    }
}
