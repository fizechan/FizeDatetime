<?php
/** @noinspection PhpComposerExtensionStubsInspection */


namespace fize\datetime;

/**
 * 历法类
 * @package fize\datetime
 */
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

    /**
     * 转换Julian Day计数到一个支持的历法
     * @param int $jd 一个Julian day天数的整数数字
     * @param int $calendar 要转换成的历法
     * @return array
     */
    public static function fromJd($jd, $calendar)
    {
        return cal_from_jd($jd, $calendar);
    }

    /**
     * 返回选定历法的信息
     * @param int $calendar 返回信息所指定的历法名称，如果没有指定历法，将返回所有历法。
     * @return array
     */
    public static function info($calendar = -1)
    {
        return cal_info($calendar);
    }

    /**
     * 从一个支持的历法转变为Julian Day计数
     * @param int $calendar 选定的历法，可以是CAL_GREGORIAN，CAL_JULIAN，CAL_JEWISH或CAL_FRENCH中的某一个。
     * @param int $month 数字形式的月份，根据选定的calendar历法来确定范围。
     * @param int $day 数字形式的日期，根据选定的calendar历法来确定范围
     * @param int $year 数字形式的年份，根据选定的calendar历法来确定范围
     * @return int
     */
    public static function toJd($calendar, $month, $day, $year)
    {
        return cal_to_jd($calendar, $month, $day, $year);
    }

    /**
     * 得到指定年份的复活节午夜时的Unix时间戳。
     * @param int $year 1970年至2037年之间的数字形式的年份。缺省的默认值是当年。
     * @return int
     */
    public static function easterDate($year = null)
    {
        if($year) {
            return easter_date($year);
        }
        return easter_date();
    }

    /**
     * 得到指定年份的3月21日到复活节之间的天数
     * @param int $year 正数形式的年份
     * @param int $method 更多可用的常量参考calendar constants
     * @return int
     */
    public static function easterDays($year = null, $method = 0)
    {
        if($year) {
            return easter_days($year, $method);
        }
        return easter_days();
    }

    /**
     * 从一个French Republican历法的日期得到Julian Day计数。
     * @param int $month 月份的范围是1到13。
     * @param int $day 日期的范围是1到30。
     * @param int $year 年份的范围是1到14。
     * @return int
     */
    public static function frenchToJd($month, $day, $year)
    {
        return frenchtojd($month, $day, $year);
    }

    /**
     * 转变一个Gregorian历法日期到Julian Day计数
     * @param int $month 月份的范围是 1（January）到 12（December）。
     * @param int $day 日期的范围是 1到 31。
     * @param int $year 年份的范围是 -4714 到 9999。
     * @return int
     */
    public static function gregorianToJd($month, $day, $year)
    {
        return gregoriantojd($month, $day, $year);
    }

    /**
     * 返回星期的日期
     * @param int $julianday 一个julian天数。
     * @param int $mode 0、1、2
     * @return mixed
     */
    public static function jdDayOfWeek($julianday, $mode = 0)
    {
        return jddayofweek($julianday, $mode);
    }

    /**
     * 返回月份的名称
     * @param int $julianday 用来计算的julian天数
     * @param int $mode 指定使用哪种历法和月份名称的形式
     * @return string
     */
    public static function jdMonthName($julianday, $mode)
    {
        return jdmonthname($julianday, $mode);
    }

    /**
     * 转变一个Julian Day计数到French Republican历法的日期
     * @param int $juliandaycount 一个julian天数
     * @return string
     */
    public static function jdToFrench($juliandaycount)
    {
        return jdtofrench($juliandaycount);
    }

    /**
     * 转变一个Julian Day计数为Gregorian历法日期
     * @param int $julianday 一个julian天数
     * @return string
     */
    public static function jdToGregorian($julianday)
    {
        return jdtogregorian($julianday);
    }

    /**
     * 转换一个julian天数为Jewish历法的日期
     * @param int $juliandaycount 一个julian天数
     * @param bool $hebrew 如果参数 hebrew设置为 TRUE，参数fl可用于希伯莱语的格式。
     * @param int $fl 可用的格式有： CAL_JEWISH_ADD_ALAFIM_GERESH, CAL_JEWISH_ADD_ALAFIM, CAL_JEWISH_ADD_GERESHAYIM.
     * @return string
     */
    public static function jdToJewish($juliandaycount, $hebrew = false, $fl = 0)
    {
        return jdtojewish($juliandaycount, $hebrew, $fl);
    }

    /**
     * 转变一个Julian Day计数到Julian历法的日期
     * @param int $julianday 一个julian天数
     * @return string
     */
    public static function jdToJulian($julianday)
    {
        return jdtojulian($julianday);
    }

    /**
     * 转变Julian Day计数为一个Unix时间戳
     * @param int $jday 一个在 2440588 到 2465342 之间的julian天数
     * @return int
     */
    public static function jdToUnix($jday)
    {
        return jdtounix($jday);
    }

    /**
     * 转变一个Jewish历法的日期为一个Julian Day计数
     * @param int $month 在1到13之间的月份
     * @param int $day 在1到30日之间的日子
     * @param int $year 在1到9999之间的年份
     * @return int
     */
    public static function jewishToJd($month, $day, $year)
    {
        return jewishtojd($month, $day, $year);
    }

    /**
     * 转变一个Julian历法的日期为Julian Day计数
     * @param int $month 月份的范围从 1 (January) 到 12 ( December)
     * @param int $day 日期的范围从 1 到 31
     * @param int $year 年份的范围从 -4713 到 9999
     * @return int
     */
    public static function julianToJd($month, $day, $year)
    {
        return juliantojd($month, $day, $year);
    }

    /**
     * 转变Unix时间戳为Julian Day计数
     * @param int $timestamp 一个用于转变的时间戳。
     * @return int
     */
    public static function unixToJd($timestamp = 0)
    {
        return unixtojd($timestamp);
    }
}
