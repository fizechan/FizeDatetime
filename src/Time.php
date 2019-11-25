<?php


namespace fize\datetime;

/**
 * 时间类
 */
class Time extends Date
{

    /**
     * 取得当前时间
     * @param bool $return_float 是否返回浮点型
     * @return array|float 默认返回一个 array。如果 return_float 设置了则会返回一个 float。
     */
    public static function gettimeofday($return_float = null)
    {
        return gettimeofday($return_float);
    }

    /**
     * 取得 GMT 日期的 UNIX 时间戳
     * @param int $hour 小时
     * @param int $minute 分钟
     * @param int $second 秒
     * @param int $month 月
     * @param int $day 日
     * @param int $year 年
     * @return int
     */
    public static function gmmktime($hour = null, $minute = null, $second = null, $month = null, $day = null, $year = null)
    {
        return gmmktime($hour, $minute, $second, $month, $day, $year);
    }

    /**
     * 根据区域设置格式化 GMT/UTC 时间／日期,返回时间是格林威治标准时（GMT）。
     * @param string $format 格式化
     * @param int $timestamp 时间戳
     * @return string
     */
    public static function gmstrftime($format, $timestamp = null)
    {
        return gmstrftime($format, $timestamp);
    }

    /**
     * 取得本地时间
     *
     * 参数 `$is_associative` :
     *   如果设为 FALSE 或未提供则返回的是普通的数字索引数组。
     *   如果该参数设为 TRUE 则 localtime() 函数返回包含有所有从 C 的 localtime 函数调用所返回的不同单元的关联数组。
     * @param int $timestamp 时间戳
     * @param bool $is_associative 是否返回关联数组
     * @return array
     */
    public static function localtime($timestamp = null, $is_associative = null)
    {
        return localtime($timestamp, $is_associative);
    }

    /**
     * 返回当前 Unix 时间戳和微秒数
     * @param bool $get_as_float 是否返回浮点数
     * @return mixed
     */
    public static function microtime($get_as_float = null)
    {
        return microtime($get_as_float);
    }

    /**
     * 取得一个日期的 Unix 时间戳
     * @param int $hour 小时
     * @param int $minute 分钟
     * @param int $second 秒
     * @param int $month 月
     * @param int $day 日
     * @param int $year 年
     * @return int 失败时返回false
     */
    public static function mktime($hour = null, $minute = null, $second = null, $month = null, $day = null, $year = null)
    {
        return mktime($hour, $minute, $second, $month, $day, $year);
    }

    /**
     * 根据区域设置格式化本地时间／日期
     * @param string $format 格式化
     * @param int $timestamp 时间戳
     * @return string
     */
    public static function strftime($format, $timestamp = null)
    {
        return strftime($format, $timestamp);
    }

    /**
     * 解析由 strftime() 生成的日期／时间
     * @notice 此函数未在 Windows 平台下实现
     * @param string $date 被解析的字符串
     * @param string $format date所使用的格式
     * @return array 失败返回false
     */
    public static function strptime($date, $format)
    {
        return strptime($date, $format);
    }

    /**
     * 将任何字符串的日期时间描述解析为 Unix 时间戳
     *
     * 参数 `$time` :
     *   正确格式的说明详见 日期与时间格式。
     * @param string $time 日期/时间字符串
     * @param int $now 用来计算返回值的时间戳。
     * @return int 失败返回false
     */
    public static function strtotime($time, $now = null)
    {
        if(is_null($now)) {
            $now = time();
        }
        return strtotime($time, $now);
    }
}
