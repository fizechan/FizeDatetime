<?php

namespace fize\datetime;

use DateTime;

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
    public static function check($month, $day, $year)
    {
        return checkdate($month, $day, $year);
    }

    /**
     * 获取根据指定格式格式化的给定日期的信息
     * @param string $format 格式化
     * @param string $date   日期
     * @return array
     */
    public static function parseFromFormat($format, $date)
    {
        return date_parse_from_format($format, $date);
    }

    /**
     * 返回有关给定日期的详细信息的关联数组
     * @param string $date 日期
     * @return array 失败时返回false
     */
    public static function parse($date)
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
    public static function sunInfo($time, $latitude, $longitude)
    {
        return date_sun_info($time, $latitude, $longitude);
    }

    /**
     * 返回给定的日期与地点的日出时间
     * @param int   $timestamp  时间戳
     * @param int   $format     格式化常量
     * @param float $latitude   维度
     * @param float $longitude  经度
     * @param float $zenith     默认： date.sunrise_zenith。
     * @param float $gmt_offset 单位是小时。
     * @return mixed 按指定格式 format 返回的日出时间， 或者在失败时返回 FALSE。
     */
    public static function sunrise($timestamp, $format = null, $latitude = null, $longitude = null, $zenith = null, $gmt_offset = null)
    {
        if (is_null($latitude)) {
            return date_sunrise($timestamp, $format);
        }
        return date_sunrise($timestamp, $format, $latitude, $longitude, $zenith, $gmt_offset);
    }

    /**
     * 返回给定的日期与地点的日落时间
     * @param int   $timestamp  时间戳
     * @param int   $format     格式化常量
     * @param float $latitude   维度
     * @param float $longitude  经度
     * @param float $zenith     默认： date.sunrise_zenith。
     * @param float $gmt_offset 单位是小时。
     * @return mixed 按指定格式 format 返回的日出时间， 或者在失败时返回 FALSE。
     */
    public static function sunset($timestamp, $format = null, $latitude = null, $longitude = null, $zenith = null, $gmt_offset = null)
    {
        if (is_null($latitude)) {
            return date_sunset($timestamp, $format);
        }
        return date_sunset($timestamp, $format, $latitude, $longitude, $zenith, $gmt_offset);
    }

    /**
     * 取得日期／时间信息
     * @param int $timestamp 一个 integer 的 Unix 时间戳
     * @return array
     */
    public static function getdate($timestamp = null)
    {
        return getdate($timestamp);
    }

    /**
     * 格式化一个 GMT/UTC 日期／时间
     * @param string $format    格式化
     * @param int    $timestamp 时间戳
     * @return string 失败时返回false
     */
    public static function gmdate($format, $timestamp = null)
    {
        return gmdate($format, $timestamp);
    }

    /**
     * 将本地时间日期格式化为整数
     * @param string $format    格式化
     * @param int    $timestamp 时间戳
     * @return int
     */
    public static function idate($format, $timestamp = null)
    {
        return idate($format, $timestamp);
    }
}
