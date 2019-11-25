<?php


namespace fize\datetime;

use DateInterval;
use DateTime;

/**
 * 时间间隔类
 */
class Interval extends DateInterval
{

    /**
     * 获取两个时间的区间
     * @param string|DateTime $dt1 时间字符串或者DateTime对象
     * @param string|DateTime $dt2 时间字符串或者DateTime对象
     * @return DateInterval
     */
    public static function diff($dt1, $dt2)
    {
        if(is_string($dt1)) {
            $dt1 = new DateTime($dt1);
        }
        if(is_string($dt2)) {
            $dt2 = new DateTime($dt2);
        }
        return $dt2->diff($dt1);
    }
}