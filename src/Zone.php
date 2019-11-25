<?php


namespace fize\datetime;

use DateTimeZone;
use DateTime;

/**
 * 时区类
 */
class Zone extends DateTimeZone
{

    /**
     * 取得时间的时区
     * @param DateTime $dt 时间
     * @return DateTimeZone
     */
    public static function get(DateTime $dt)
    {
        return $dt->getTimezone();
    }

    /**
     * 设置时间的时区
     * @param DateTime $dt 时间
     * @param mixed $timezone 时区对象或者时区标识
     * @return DateTime
     */
    public static function set(DateTime $dt, $timezone)
    {
        if(is_string($timezone)) {
            $timezone = new DateTimeZone($timezone);
        }
        return $dt->setTimezone($timezone);
    }

    /**
     * 取得一个脚本中所有日期时间函数所使用的默认时区
     * @return string
     */
    public static function defaultGet()
    {
        return date_default_timezone_get();
    }

    /**
     * 设定用于一个脚本中所有日期时间函数的默认时区
     *
     * 参数 `$timezone_identifier` :
     *   例如 UTC 或 Europe/Lisbon。合法标识符列表见所支持的时区列表。
     * @param string $timezone_identifier 时区标识符
     * @return bool
     */
    public static function defaultSet($timezone_identifier)
    {
        return date_default_timezone_set($timezone_identifier);
    }

    /**
     * 从缩写中返回时区名称
     * @param string $abbr 时区缩写
     * @param int $gmtOffset 与格林尼治时间的偏差(以秒为单位)。
     * @param int $isdst 夏令时设置
     * @return string 失败返回false
     */
    public static function nameFromAbbr($abbr, $gmtOffset = null, $isdst = null)
    {
        return timezone_name_from_abbr($abbr, $gmtOffset, $isdst);
    }

    /**
     * 获取 timezonedb 的版本
     * @return string
     */
    public static function versionGet()
    {
        return timezone_version_get();
    }
}
