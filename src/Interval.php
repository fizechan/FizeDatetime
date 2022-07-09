<?php

namespace Fize\Datetime;

use DateInterval;
use DateTime;

/**
 * 间隔
 */
class Interval extends DateInterval
{

    /**
     * 获取两个时间的区间
     * @param DateTime $dt1 DateTime对象1
     * @param DateTime $dt2 DateTime对象2
     * @return DateInterval
     */
    public static function diff(DateTime $dt1, DateTime $dt2): DateInterval
    {
        return $dt2->diff($dt1);
    }
}
