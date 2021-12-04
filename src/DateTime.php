<?php

namespace Fize\Datetime;

use DateTime as Common;

/**
 * 日期时间
 */
class DateTime extends Common
{
    /**
     * 格式化 UNIX 时间戳为人易读的字符串
     *
     * @param int      $remote Unix 时间戳
     * @param int|null $local  本地时间
     * @param bool     $zh     中文显示
     * @return    string    格式化的日期字符串
     */
    public static function human(int $remote, int $local = null, bool $zh = true): string
    {
        $timediff = (is_null($local) || $local ? time() : $local) - $remote;
        if ($zh) {
            $chunks = [
                [60 * 60 * 24 * 365, '年'],
                [60 * 60 * 24 * 30, '个月'],
                [60 * 60 * 24 * 7, '周'],
                [60 * 60 * 24, '天'],
                [60 * 60, '小时'],
                [60, '分钟'],
                [1, '秒']
            ];
            $ago = '前';
        } else {
            $chunks = [
                [60 * 60 * 24 * 365, 'year'],
                [60 * 60 * 24 * 30, 'month'],
                [60 * 60 * 24 * 7, 'week'],
                [60 * 60 * 24, 'day'],
                [60 * 60, 'hour'],
                [60, 'minute'],
                [1, 'second']
            ];
            $ago = 'ago';
        }

        $name = $chunks[6][1];
        $count = 0;
        for ($i = 0, $j = count($chunks); $i < $j; $i++) {
            $seconds = $chunks[$i][0];
            $name = $chunks[$i][1];
            if (($count = floor($timediff / $seconds)) != 0) {
                break;
            }
        }
        if ($zh) {
            return sprintf("%d$name$ago", $count);
        } else {
            return sprintf("%d $name%s $ago", $count, ($count > 1 ? 's' : ''));
        }
    }

    /**
     * 时间是否在区间内
     * @param string $time       指定时间
     * @param string $begin_time 开始时间
     * @param string $end_time   结束时间
     * @return bool
     */
    public static function isBetween(string $time, string $begin_time, string $end_time): bool
    {
        $time = strtotime($time);
        $begin_time = strtotime($begin_time);
        $end_time = strtotime($end_time);
        return $time >= $begin_time && $time <= $end_time;
    }
}