<?php

namespace Fize\Datetime;

use RuntimeException;

/**
 * 节假日
 */
class Holiday
{

    /**
     * 获取指定年份的所有节假日及休息日
     * @see http://timor.tech/api/holiday/
     * @param int $year 年份
     * @return array
     */
    public static function year(int $year): array
    {
        $result = file_get_contents("http://timor.tech/api/holiday/year/$year");
        if (!$result) {
            throw new RuntimeException("HTTP请求时发生错误。");
        }
        $result = json_decode($result, true);
        if (!isset($result['holiday'])) {
            throw new RuntimeException('请求节假日接口时发生错误');
        }

        $holidays = [];  //节假日
        $api_holidays = $result['holiday'];
        foreach ($api_holidays as $key => $holiday) {
            $date = "{$year}-{$key}";
            $holidays[$date] = $holiday;
        }

        $alldays = [];  //休息日
        $dates = Date::dates("$year-01-01", "$year-12-31");
        foreach ($dates as $date) {
            if (isset($holidays[$date])) {  //节假日即节假日，不需要再判断休息日
                if ($holidays[$date]['holiday'] === false) {  //调休
                    continue;
                }
                $alldays[$date] = [
                    'date' => $date,
                    'year' => $year,
                    'name' => $holidays[$date]['name'],
                    'type' => 1
                ];
            } elseif (date('w', strtotime($date)) == 6) {
                $alldays[$date] = [
                    'date' => $date,
                    'year' => $year,
                    'name' => '星期六',
                    'type' => 2
                ];
            } elseif (date('w', strtotime($date)) == 0) {
                $alldays[$date] = [
                    'date' => $date,
                    'year' => $year,
                    'name' => '星期日',
                    'type' => 2
                ];
            }
        }

        return $alldays;
    }

    public static function get()
    {

    }
}
