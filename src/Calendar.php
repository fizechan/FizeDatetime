<?php


namespace fize\datetime;


class Calendar
{

    /**
     * 返回某个历法中某年中某月的天数
     * @param int $calendar 用来计算的某个历法
     * @param int $month 选定历法中的某月
     * @param int $year 选定历法中的某年
     * @return int
     */
    public static function daysInMonth($calendar, $month, $year)
    {
        return cal_days_in_month($calendar, $month, $year);
    }

    public static function fromJd()
    {

    }
}
