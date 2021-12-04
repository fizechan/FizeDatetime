<?php

namespace Fize\Datetime;

use DateInterval;
use DatePeriod;
use DateTime as DT;

/**
 * 日期
 */
class Date extends DateTime
{

    /**
     * 检查由参数构成的日期的合法性。
     *
     * 如果每个参数都正确定义了则会被认为是有效的。
     * 参数 `$day` :
     *   闰年已经考虑进去了。
     * @param int $month month 的值是从 1 到 12。
     * @param int $day   Day 的值在给定的 month 所应该具有的天数范围之内
     * @param int $year  year 的值是从 1 到 32767。
     * @return bool
     */
    public static function check(int $month, int $day, int $year): bool
    {
        return checkdate($month, $day, $year);
    }

    /**
     * 获取根据指定格式格式化的给定日期的信息
     * @param string $format 格式化
     * @param string $date   日期
     * @return array
     */
    public static function parseFromFormat(string $format, string $date): array
    {
        return date_parse_from_format($format, $date);
    }

    /**
     * 返回有关给定日期的详细信息的关联数组
     * @param string $date 日期
     * @return array 失败时返回false
     */
    public static function parse(string $date): array
    {
        return date_parse($date);
    }

    /**
     * 返回一个包含日落/日出和黄昏开始/结束信息的数组
     * @param int   $time      时间戳
     * @param float $latitude  维度
     * @param float $longitude 经度
     * @return array 失败时返回false
     */
    public static function sunInfo(int $time, float $latitude, float $longitude): array
    {
        return date_sun_info($time, $latitude, $longitude);
    }

    /**
     * 返回给定的日期与地点的日出时间
     * @param int        $timestamp  时间戳
     * @param int|null   $format     格式化常量
     * @param float|null $latitude   维度
     * @param float|null $longitude  经度
     * @param float|null $zenith     默认： date.sunrise_zenith。
     * @param float|null $gmt_offset 单位是小时。
     * @return string|int|float|false 按指定格式 format 返回的日出时间， 或者在失败时返回 FALSE。
     */
    public static function sunrise(int $timestamp, int $format = null, float $latitude = null, float $longitude = null, float $zenith = null, float $gmt_offset = null)
    {
        if (is_null($latitude)) {
            return date_sunrise($timestamp, $format);
        }
        return date_sunrise($timestamp, $format, $latitude, $longitude, $zenith, $gmt_offset);
    }

    /**
     * 返回给定的日期与地点的日落时间
     * @param int        $timestamp  时间戳
     * @param int|null   $format     格式化常量
     * @param float|null $latitude   维度
     * @param float|null $longitude  经度
     * @param float|null $zenith     默认： date.sunrise_zenith。
     * @param float|null $gmt_offset 单位是小时。
     * @return string|int|float|false 按指定格式 format 返回的日出时间， 或者在失败时返回 FALSE。
     */
    public static function sunset(int $timestamp, int $format = null, float $latitude = null, float $longitude = null, float $zenith = null, float $gmt_offset = null)
    {
        if (is_null($latitude)) {
            return date_sunset($timestamp, $format);
        }
        return date_sunset($timestamp, $format, $latitude, $longitude, $zenith, $gmt_offset);
    }

    /**
     * 取得日期／时间信息
     * @param int|null $timestamp 一个 integer 的 Unix 时间戳
     * @return array
     */
    public static function getdate(int $timestamp = null): array
    {
        return getdate($timestamp);
    }

    /**
     * 格式化一个 GMT/UTC 日期／时间
     * @param string   $format    格式化
     * @param int|null $timestamp 时间戳
     * @return string 失败时返回false
     */
    public static function gmdate(string $format, int $timestamp = null): string
    {
        return gmdate($format, $timestamp);
    }

    /**
     * 将本地时间日期格式化为整数
     * @param string   $format    格式化
     * @param int|null $timestamp 时间戳
     * @return int
     */
    public static function idate(string $format, int $timestamp = null): int
    {
        return idate($format, $timestamp);
    }

    /**
     * 获取日期在指定时间前后的对应日期，该月没有这一天时则为最后一天。
     * @param string $date   日期，格式Y-m-d
     * @param int    $months 月数,负数表示时间之前
     * @param int    $days   天数,负数表示时间之前，如果计算得为负数，则取当月第一天
     * @return string
     */
    public static function get(string $date, int $months, int $days = 0): string
    {
        [$sYear, $sMonth] = explode('-', $date);
        if ($months < 0) {
            $monthend = date("Y-m-d", strtotime(($months + 1) . " month -1 day", strtotime($sYear . '-' . $sMonth . '-01')));
            $nextDate = date("Y-m-d", strtotime(($months) . " month", strtotime($date)));
        } else {
            $monthend = date("Y-m-d", strtotime("+" . ($months + 1) . " month -1 day", strtotime($sYear . '-' . $sMonth . '-01')));
            $nextDate = date("Y-m-d", strtotime("+" . ($months) . " month", strtotime($date)));
        }

        $currDate = min($nextDate, $monthend);
        if ($days) {
            if ($days < 0) {
                $currDate = date("Y-m-d", strtotime($days . " day", strtotime($currDate)));
                $monthfst = date("Y-m-01", strtotime($monthend));
                $currDate = min($currDate, $monthfst);
            } else {
                $currDate = date("Y-m-d", strtotime("+" . ($days) . " day", strtotime($currDate)));
            }
        }
        return min($currDate, $monthend);
    }

    /**
     * 获取两个日期间的所有日期
     * @param string $date_begin 开始日期，(Y-m-d)
     * @param string $date_end   结束日期，(Y-m-d)
     * @return array
     */
    public static function dates(string $date_begin, string $date_end): array
    {
        $dates = [];
        if ($date_begin > $date_end) {
            return $dates;
        }
        $start = new DateTime($date_begin);
        $end = new DateTime($date_end);
        foreach (new DatePeriod($start, new DateInterval('P1D'), $end) as $d) {
            /**
             * @var DateTime $d
             */
            $dates[] = $d->format('Y-m-d');
        }
        $dates[] = $date_end;
        return $dates;
    }

    /**
     * 获取两个日期相隔的天数
     * @param string $start_date 开始日期
     * @param string $end_date   结束日期
     * @param bool   $days360    是否使用360天制
     * @return int
     */
    public static function days(string $start_date, string $end_date, bool $days360 = false)
    {
        $datetime_start = new DateTime($start_date);
        $datetime_end = new DateTime($end_date);
        if ($days360) {
            $year_start = $datetime_start->format('Y');
            $year_end = $datetime_end->format('Y');
            $years = (int)$year_end - (int)$year_start;
            $month_start = $datetime_start->format('m');
            $month_end = $datetime_end->format('m');
            $months = (int)$month_end - (int)$month_start;
            $day_start = $datetime_start->format('d');
            $day_end = $datetime_end->format('d');
            $days = (int)$day_end - (int)$day_start;
            return ($months + $years * 12) * 30 + $days;
        } else {
            $diff = $datetime_start->diff($datetime_end);
            $days = $diff->days;
            return (int)$days;
        }
    }

    /**
     * 对 DateTime 设置指定日期
     * @param DT  $dateTime 日期
     * @param int $day      日期，如果大于当月最后一天，则返回最后一天
     * @return DT
     */
    public static function set(DT $dateTime, int $day): DT
    {
        $t = (int)$dateTime->format('t');
        if ($day > $t) {
            $day = $t;
        }
        return new DT($dateTime->format("Y-m-$day"));
    }
}
