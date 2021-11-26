<?php

namespace Fize\Datetime;

/**
 * 时间
 */
class Time extends Date
{

    /**
     * 取得当前时间
     * @param bool|null $return_float 是否返回浮点型
     * @return array|float 默认返回一个 array。如果 return_float 设置了则会返回一个 float。
     */
    public static function gettimeofday(bool $return_float = null)
    {
        return gettimeofday($return_float);
    }

    /**
     * 取得 GMT 日期的 UNIX 时间戳
     * @param int|null $hour   小时
     * @param int|null $minute 分钟
     * @param int|null $second 秒
     * @param int|null $month  月
     * @param int|null $day    日
     * @param int|null $year   年
     * @return int
     */
    public static function gmmktime(int $hour = null, int $minute = null, int $second = null, int $month = null, int $day = null, int $year = null): int
    {
        return gmmktime($hour, $minute, $second, $month, $day, $year);
    }

    /**
     * 根据区域设置格式化 GMT/UTC 时间／日期,返回时间是格林威治标准时（GMT）。
     * @param string   $format    格式化
     * @param int|null $timestamp 时间戳
     * @return string
     */
    public static function gmstrftime(string $format, int $timestamp = null): string
    {
        return gmstrftime($format, $timestamp);
    }

    /**
     * 取得本地时间
     *
     * 参数 `$is_associative` :
     *   如果设为 FALSE 或未提供则返回的是普通的数字索引数组。
     *   如果该参数设为 TRUE 则 localtime() 函数返回包含有所有从 C 的 localtime 函数调用所返回的不同单元的关联数组。
     * @param int|null  $timestamp      时间戳
     * @param bool|null $is_associative 是否返回关联数组
     * @return array
     */
    public static function localtime(int $timestamp = null, bool $is_associative = null): array
    {
        return localtime($timestamp, $is_associative);
    }

    /**
     * 返回当前 Unix 时间戳和微秒数
     * @param bool|null $get_as_float 是否返回浮点数
     * @return string|float
     */
    public static function microtime(bool $get_as_float = null)
    {
        return microtime($get_as_float);
    }

    /**
     * 取得一个日期的 Unix 时间戳
     * @param int|null $hour   小时
     * @param int|null $minute 分钟
     * @param int|null $second 秒
     * @param int|null $month  月
     * @param int|null $day    日
     * @param int|null $year   年
     * @return int 失败时返回false
     */
    public static function mktime(int $hour = null, int $minute = null, int $second = null, int $month = null, int $day = null, int $year = null): int
    {
        return mktime($hour, $minute, $second, $month, $day, $year);
    }

    /**
     * 根据区域设置格式化本地时间／日期
     * @param string   $format    格式化
     * @param int|null $timestamp 时间戳
     * @return string
     */
    public static function strftime(string $format, int $timestamp = null): string
    {
        return strftime($format, $timestamp);
    }

    /**
     * 解析由 strftime() 生成的日期／时间
     * @notice 此函数未在 Windows 平台下实现
     * @param string $date   被解析的字符串
     * @param string $format date所使用的格式
     * @return array 失败返回false
     */
    public static function strptime(string $date, string $format): array
    {
        return strptime($date, $format);
    }

    /**
     * 将任何字符串的日期时间描述解析为 Unix 时间戳
     *
     * 参数 `$time` :
     *   正确格式的说明详见 日期与时间格式。
     * @param string   $time 日期/时间字符串
     * @param int|null $now  用来计算返回值的时间戳。
     * @return int 失败返回false
     */
    public static function strtotime(string $time, int $now = null): int
    {
        if (is_null($now)) {
            $now = time();
        }
        return strtotime($time, $now);
    }
}
