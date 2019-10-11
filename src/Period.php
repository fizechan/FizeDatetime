<?php


namespace fize\datetime;

use DatePeriod;
use DateInterval;
use DateTime;

/**
 * 时间周期类
 * @package fize\datetime
 */
class Period extends DatePeriod
{

    /**
     * 两个时间的讲个迭代器
     * @param mixed $start 开始时间
     * @param mixed $end 结束时间
     * @param DateInterval $interval 间隔时间
     * @return DatePeriod
     */
    public static function between($start, $end, DateInterval $interval)
    {
        if(is_string($start)) {
            $start = new DateTime($start);
        }
        if(is_string($end)) {
            $end = new DateTime($end);
        }

        return new DatePeriod($start, $interval, $end);
    }
}