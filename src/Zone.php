<?php

namespace Fize\Datetime;

use DateTime;
use DateTimeZone;

/**
 * 时区
 */
class Zone extends DateTimeZone
{

    /**
     * 取得时间的时区
     * @param DateTime $dt 时间
     * @return DateTimeZone
     */
    public static function get(DateTime $dt): DateTimeZone
    {
        return $dt->getTimezone();
    }

    /**
     * 设置时间的时区
     * @param DateTime $dt       时间
     * @param mixed    $timezone 时区对象或者时区标识
     * @return DateTime
     */
    public static function set(DateTime $dt, $timezone): DateTime
    {
        if (is_string($timezone)) {
            $timezone = new DateTimeZone($timezone);
        }
        return $dt->setTimezone($timezone);
    }

    /**
     * 取得一个脚本中所有日期时间函数所使用的默认时区
     * @return string
     */
    public static function defaultGet(): string
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
    public static function defaultSet(string $timezone_identifier): bool
    {
        return date_default_timezone_set($timezone_identifier);
    }

    /**
     * 从缩写中返回时区名称
     * @param string   $abbr      时区缩写
     * @param int|null $gmtOffset 与格林尼治时间的偏差(以秒为单位)。
     * @param int|null $isdst     夏令时设置
     * @return string 失败返回false
     */
    public static function nameFromAbbr(string $abbr, int $gmtOffset = null, int $isdst = null): string
    {
        return timezone_name_from_abbr($abbr, $gmtOffset, $isdst);
    }

    /**
     * 获取 timezonedb 的版本
     * @return string
     */
    public static function versionGet(): string
    {
        return timezone_version_get();
    }

    /**
     * 计算两个时区间相差的时长,单位为秒
     *
     * $seconds = self::offset('America/Chicago', 'GMT');
     *
     * [!!] A list of time zones that PHP supports can be found at
     * <http://php.net/timezones>.
     *
     * @param string      $remote timezone that to find the offset of
     * @param string|null $local  timezone used as the baseline
     * @param int|string       $datetime    UNIX timestamp or date string
     * @return  int
     */
    public static function offset(string $remote, string $local = null, $datetime = 'now'): int
    {
        if ($local === null) {
            // Use the default timezone
            $local = date_default_timezone_get();
        }
        if (is_int($datetime)) {
            // Convert the timestamp into a string
            $datetime = date(DateTime::RFC2822, $datetime);
        }
        // Create timezone objects
        $zone_remote = new DateTimeZone($remote);
        $zone_local = new DateTimeZone($local);
        // Create date objects from timezones
        $time_remote = new DateTime($datetime, $zone_remote);
        $time_local = new DateTime($datetime, $zone_local);
        // Find the offset
        $offset = $zone_remote->getOffset($time_remote) - $zone_local->getOffset($time_local);
        return $offset;
    }
}
