<?php

namespace fize\datetime;

use DateInterval;
use DatePeriod;
use DateTime;

/**
 * 时间周期类
 */
class Period extends DatePeriod
{

    /**
     * 两个时间的讲个迭代器
     * @param string|DateTime $start    开始时间
     * @param string|DateTime $end      结束时间
     * @param DateInterval    $interval 间隔时间
     * @return DatePeriod
     */
    public static function between($start, $end, DateInterval $interval)
    {
        if (is_string($start)) {
            $start = new DateTime($start);
        }
        if (is_string($end)) {
            $end = new DateTime($end);
        }

        return new DatePeriod($start, $interval, $end);
    }
}
