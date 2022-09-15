<?php

namespace Fize\Datetime;

use DateTimeZone;
use DomainException;
use InvalidArgumentException;

/**
 * 中国历法
 */
class ChineseCalendar
{

    /**
     * @var float 中国(东八区)时间相对UTC的偏移量(单位：天days)
     */
    private const CHINESE_TIME_OFFSET = 8 / 24.0;

    /**
     * @var string 中国时区名称
     */
    private const TIME_ZONE_NAME = 'Asia/Shanghai';

    /**
     * 格里历TT时间1582年10月15日中午12点的儒略日
     *
     * @var float
     */
    private const JULIAN_GREGORIAN_BOUNDARY = 2299161.0;

    /**
     * @var float 儒略历1年有多少天
     */
    private const JULIAN_DAYS_OF_A_YEAR = 365.25;

    /**
     * J2000.0的儒略日
     * TT时间2000年1月1日中午12点 (UTC时间2000年1月1日11:58:55.816)的儒略日
     * @var float
     */
    private const JULIAN_DAY_J2000 = 2451545.0;

    /**
     * 儒略历历法废弃日期
     * 英帝国(含美国等): 1752年9月2日，
     * 其实这个废弃日期特别乱，根本不好统一计算，
     * 目前暂无完整数据，请不要修改该值
     *
     * @var int[]
     */
    private const JULIAN_ABANDONMENT_DATE = ['year' => 1582, 'month' => 10, 'day' => 4];

    /**
     * 格里历历法实施日期
     * 英帝国(含美国等): 1752年9月14日
     * 其实这个实施日期特别乱，根本不好统一计算，
     * 目前暂无完整数据，请不要修改该值
     *
     *
     * @var int[]
     */
    private const GREGORIAN_ADOPTION_DATE = ['year' => 1582, 'month' => 10, 'day' => 15];

    /**
     * 均值朔望月长(mean length of synodic month)
     * @var float
     */
    private const MSM = 29.530588853;

    /**
     * 以2000年的第一个均值新月点为基准点，此基准点为2000年1月6日14时20分37秒(TT)，其对应真实新月点为2000年1月6日18时13分42秒(TT)
     * public const BNM = 2451550.0976504628;
     * public const BNM = 2451550.09765046;
     * public const BNM = 2451550.09765;
     * php7的float是16位(包括小数点)有效数,该值转为2000年1月6日14时20分36秒(TT)
     *
     * @var float
     */
    private const BNM = 2451550.0976504628;

    /**
     * 以前一年冬至为起点之连续16个中气
     *
     * @var array  [int年份数 => array[16]float]
     */
    private static $zqs = [];

    /**
     * 指定年份以春分开始的节气
     *
     * @var array  以年份数为索引的节气数组
     */
    private static $msts = [];

    /**
     * 农历转公历
     * @param int $year   农历年
     * @param int $month  农历月
     * @param int $day    农历日
     * @param int $isLeap 指定的月是否是闰月
     * @return DateTime
     */
    public static function lunar2gregorian(int $year, int $month, int $day, int $isLeap = 0): DateTime
    {
        if ($year < -1000 || $year > 3000) { // 适用于公元-1000至公元3000,超出此范围误差较大
            throw new DomainException('年份限-1000年至3000年');
        }
        if ($month < 1 || $month > 12) { // 月份须在1-12月之内
            throw new DomainException('月份错误');
        }
        if ($day < 1 || $day > 30) { //输入日期必须在1-30日之內
            throw new DomainException('日期错误');
        }

        [, $nm, $mc] = self::zQandSMandLunarMonthCode($year);

        $leap = self::mcLeap($mc);

        // 11月对应到1,12月对应到2,1月对应到3,2月对应到4,依此类推
        $month += 2;

        $nofd = [];
        // 求算农历各月之大小,大月30天,小月29天
        for ($i = 0; $i <= 14; $i++) {
            $nofd[$i] = floor($nm[$i + 1] + 0.5) - floor($nm[$i] + 0.5); // 每月天数,加0.5是因JD以正午起算
        }

        $jd = 0; // 儒略日时间
        $errMsg = null;

        if ($isLeap) { // 闰月
            if ($leap < 3) { // 而旗标非闰月或非本年闰月,则表示此年不含闰月.leap=0代表无闰月,=1代表闰月为前一年的11月,=2代表闰月为前一年的12月
                $errMsg = '闰月非该年，是上一年的闰月';
            } else { // 若该年內有闰月
                if ($leap != $month) { // 但不为指定的月份
                    $errMsg = '该月非该年的闰月'; // 该月非该年的闰月
                } else { // 若指定的月份即为闰月
                    if ($day <= $nofd[$month]) { // 若日期不大于当月天数
                        $jd = $nm[$month] + $day - 1; // 则将当月之前的JD值加上日期之前的天数
                    } else { // 日期超出范围
                        $errMsg = '日期超出范围';
                    }
                }
            }
        } else { // 若没有指明是闰月
            if ($leap == 0) { // 若旗标非闰月,则表示此年不含闰月(包括前一年的11月起之月份)
                if ($day <= $nofd[$month - 1]) { // 若日期不大于当月天数
                    $jd = $nm[$month - 1] + $day - 1; // 则将当月之前的JD值加上日期之前的天数
                } else { // 日期超出范围
                    $errMsg = '日期超出范围';
                }
            } else { // 若旗标为本年有闰月(包括前一年的11月起之月份) 公式nofd(mx - (mx > leap) - 1)的用意为:若指定月大于闰月,则索引用mx,否则索引用mx-1
                if ($day <= $nofd[$month + ($month > $leap) - 1]) { // 若日期不大于当月天数
                    $jd = $nm[$month + ($month > $leap) - 1] + $day - 1; // 则将当月之前的JD值加上日期之前的天数
                } else { // 日期超出范围
                    $errMsg = '日期超出范围';
                }
            }
        }

        // 去掉时分秒
        $jd = $jd - self::CHINESE_TIME_OFFSET; // 还原经过中国(东八区)时差处理的jd

        // 由于农历对应的是中国时间，故此在将农历转为公历时建议转为中国时间
        if (is_null($errMsg)) {
            return self::jdToDateTime($jd)->setTime(0, 0);
        } else {
            throw new InvalidArgumentException($errMsg);
        }
    }

    /**
     * 公历日期转农历日期
     *
     * 中国(东八区)时区日期转农历日期
     * @param int $year  公历年份
     * @param int $month 公历月份
     * @param int $day   公历日
     * @return int[] map[string]int  [Y:int年,n:int月,j:int日,leap:int是否闰月(0不是闰月,1是闰月)]
     */
    public static function gregorian2lunar(int $year, int $month, int $day): array
    {
        if ($year < -1000 || $year > 3000) { // 适用于公元-1000至公元3000,超出此范围误差较大
            throw new DomainException('年份限-1000年至3000年');
        }
        if ($month < 1 || $month > 12) { // 月份须在1-12月之内
            throw new DomainException('月份错误');
        }
        if ($day < 1 || $day > 31) { //输入日期必须在1-30日之內
            throw new DomainException('日期错误');
        }

        $yy = $year; // 初始农历年等于公历年

        $prev = 0; // 是否跨年了,跨年了则减一
        $isLeap = 0;// 是否闰月

        [, $nm, $mc] = self::zQandSMandLunarMonthCode($year);

        $jd = self::julianDay($year, $month, $day, 0, 0, 0, 0); // 求出指定年月日之JD值
        $jdn = $jd + 0.5; // 加0.5是将起始点从正午改为0时开始

        // 如果公历日期的jd小于第一个朔望月新月点，表示农历年份是在公历年份的上一年
        if (floor($jdn) < floor($nm[0] + 0.5)) {
            $prev = 1;
            [, $nm, $mc] = self::zQandSMandLunarMonthCode($year - 1);
        }

        // 查询对应的农历月份索引
        $mi = 0;
        for ($i = 0; $i <= 14; $i++) { // 指令中加0.5是为了改为从0时算起而不是从中午算起
            if (floor($jdn) >= floor($nm[$i] + 0.5) && floor($jdn) < floor($nm[$i + 1] + 0.5)) {
                $mi = $i;
                break;
            }
        }

        // 农历的年
        // 如果月份属于上一年的11月或12月,或者农历年在上一年时
        if ($mc[$mi] < 2 || $prev == 1) {
            $yy -= 1;
        }

        // 农历月份是否是闰月
        if (($mc[$mi] - floor($mc[$mi])) * 2 + 1 != 1) { // 因mc(mi)=0对应到前一年农历11月,mc(mi)=1对应到前一年农历12月,mc(mi)=2对应到本年1月,依此类推
            $isLeap = 1;
        }
        // 农历的月
        $mm = floor($mc[$mi] + 10) % 12 + 1;

        // 农历的日
        $dd = floor($jdn) - floor($nm[$mi] + 0.5) + 1; // 日,此处加1是因为每月初一从1开始而非从0开始

        return ['Y' => $yy, 'n' => $mm, 'j' => (int)$dd, 'leap' => $isLeap];
    }

    /**
     * 农历某个月有多少天
     * @param int $year   农历年数字
     * @param int $month  农历月数字
     * @param int $isLeap 是否是闰月
     * @return int 农历某个月天数
     */
    public static function days(int $year, int $month, int $isLeap = 0): int
    {
        if ($year < -1000 || $year > 3000) { // 适用于公元-1000至公元3000,超出此范围误差较大
            throw new DomainException('年份限-1000年至3000年');
        }
        if ($month < 1 || $month > 12) { // 月份须在1-12月之内
            throw new DomainException('月份错误');
        }

        [, $nm, $mc] = self::zQandSMandLunarMonthCode($year);

        $leap = self::mcLeap($mc); // 闰几月，0无闰月

        // 11月对应到1,12月对应到2,1月对应到3,2月对应到4,依此类推
        $month += 2;

        $nofd = [];
        // 求算农历各月之大小,大月30天,小月29天
        for ($i = 0; $i <= 14; $i++) {
            $nofd[$i] = floor($nm[$i + 1] + 0.5) - floor($nm[$i] + 0.5); // 每月天数,加0.5是因JD以正午起算
        }

        // 当月天数
        if ($isLeap) { // 闰月
            if ($leap < 3) { // 而旗标非闰月或非本年闰月,则表示此年不含闰月.leap=0代表无闰月,=1代表闰月为前一年的11月,=2代表闰月为前一年的12月
                throw new InvalidArgumentException('该年无闰月'); // 该年不是闰年
            } else { // 若本年內有闰月
                if ($leap != $month) { // 但不为指定的月份
                    throw new InvalidArgumentException('闰月月份不正确'); // 该月非该年的闰月，此月不是闰月
                } else { // 若指定的月份即为闰月
                    $dy = $nofd[$month];
                }
            }
        } else { // 若没有指明是闰月
            if ($leap == 0) { // 若旗标非闰月,则表示此年不含闰月(包括前一年的11月起之月份)
                $dy = $nofd[$month - 1];
            } else { // 若旗标为本年有闰月(包括前一年的11月起之月份) 公式nofd(mx - (mx > leap) - 1)的用意为:若指定月大于闰月,则索引用mx,否则索引用mx-1
                $dy = $nofd[$month + ($month > $leap) - 1];
            }
        }

        return (int)$dy;
    }

    /**
     * 获取农历某年的闰月,0为无闰月
     * @param int $year 农历年
     * @return int 闰几月，返回值是几就闰几月，0为无闰月
     */
    public static function leap(int $year): int
    {
        [, , $mc] = self::zQandSMandLunarMonthCode($year);
        $leap = self::mcLeap($mc);
        return (int)max(0, $leap - 2);
    }

    /**
     * 儒略日转DateTime
     *
     * 注意: 该方法中$jd应为经过摄动值和deltaT调整后的jd,不需要对jd作时区调整
     * 在该方法的处理中，我们将UT==UTC
     * @param float $jd 儒略日
     * @return DateTime
     */
    protected static function jdToDateTime(float $jd): DateTime
    {
        $DateTimeZone = new DateTimeZone(self::TIME_ZONE_NAME);
        $dateArray = self::julianDayToDateArray($jd);
        $dt = new DateTime(
            sprintf("%d-%d-%d %d:%d:%d.%d", $dateArray['Y'], $dateArray['n'], $dateArray['j'], $dateArray['G'], $dateArray['i'], $dateArray['s'], $dateArray['u']),
            new DateTimeZone('UTC')
        );
        $dt->setTimezone($DateTimeZone);
        return $dt;
    }

    /**
     * 儒略日计算对应的日期时间(TT)
     *
     * @param float $jd 儒略日
     *
     * @return array 一个包含日期数据的数组，['Y' int年, 'n' int月, 'j' int日, 'G' int时, 'i' int分, 's' int秒, 'u' int毫秒]
     */
    protected static function julianDayToDateArray(float $jd): array
    {
        $jdn = $jd + 0.5;

        // 计算公式: https://blog.csdn.net/weixin_42763614/article/details/82880007
        $Z = floor($jdn); // 儒略日的整数部分
        $F = $jdn - $Z; // 儒略日的小数部分

        // 2299161 是1582年10月15日12时0分0秒
        if ($Z < self::JULIAN_GREGORIAN_BOUNDARY) {
            //儒略历
            $A = $Z;
        } else {
            $a = floor(($Z - 2305507.25) / 36524.25);

            // 10 是格里历比儒略历多出来的10天，这个数应对儒略历应废弃日期与格里历实施日期的差数
            //$gregorian_adoption_time = gmmktime(12,0,0,(self::GREGORIAN_ADOPTION_DATE)['month'], (self::GREGORIAN_ADOPTION_DATE)['day'], (self::GREGORIAN_ADOPTION_DATE)['year']);
            //$julian_abandonment_time = gmmktime(12,0,0,(self::JULIAN_ABANDONMENT_DATE)['month'], (self::JULIAN_ABANDONMENT_DATE)['day'], (self::JULIAN_ABANDONMENT_DATE)['year']);
            //$IntervalDays = floor(($gregorian_adoption_time - $julian_abandonment_time) / 86400);
            $IntervalDays = 10;
            $A = $Z + $IntervalDays + $a - floor($a / 4);
        }

        $k = 0;
        while (true) {
            $B = $A + 1524; // 以BC4717年3月1日0时为历元
            $C = floor(($B - 122.1) / 365.25); // 积年
            $D = floor(365.25 * $C); // 积年的日数
            $E = floor(($B - $D) / 30.6); // B-D为年内积日，E即月数
            $dayF = $B - $D - floor(30.6 * $E) + $F;
            if ($dayF >= 1) break; // 否则即在上一月，可前置一日重新计算
            $A -= 1;
            $k += 1;
        }

        $month = $E < 14 ? $E - 1 : $E - 13; // 月
        $year = $month > 2 ? $C - 4716 : $C - 4715; // 年
        $dayF += $k;
        if (intval($dayF) === 0) $dayF += 1;

        // 天数分开成天与时分秒
        $day = floor($dayF); // 天
        $dayD = $dayF - $day;
        $hh = $ii = $ss = $ms = 0.0;
        if ($dayD > 0) {
            $hhF = $dayD * 24;
            $hh = floor($hhF); // 时
            $hhD = $hhF - $hh;
            if ($hhD > 0) {
                $iiF = $hhD * 60;
                $ii = floor($iiF); // 分
                $iiD = $iiF - $ii;
                if ($iiD > 0) {
                    $ssF = $iiD * 60;
                    $ss = floor($ssF); // 秒
                    $ssD = $ssF - $ss;
                    if ($ssD > 0) {
                        $ms = $ssD / 1000;
                    }
                }
            }
        }

        return [
            'Y' => (int)$year,       // 年
            'n' => (int)$month,      // 月
            'j' => (int)$day,        // 日
            'G' => (int)$hh,         // 时
            'i' => (int)$ii,         // 分
            's' => (int)$ss,         // 秒
            'u' => (int)$ms          // 毫秒
        ];
    }

    /**
     * 以比较日期法求算冬月及其余各月名称代码,包含闰月,冬月为0,腊月为1,正月为2,其余类推.闰月多加0.5
     *
     * @param int $year 指定的年
     *
     * @return array   array[3]array [float 以前一年冬至为起点之连续15个中气, float 以含冬至中气为阴历11月(冬月)开始的连续16个朔望月的新月点, float 月名称代码]
     */
    private static function zQandSMandLunarMonthCode(int $year): array
    {
        $mc = [];

        // 取得以前一年冬至为起点之连续16个中气
        $zq = self::qiSinceWinterSolstice($year);

        $nm = self::sMsinceWinterSolstice($year, $zq[0]); // 求出以含冬至中气为阴历11月(冬月)开始的连续16个朔望月的新月点

        $yz = 0; // 设定旗标,0表示未遇到闰月,1表示已遇到闰月
        if (floor($zq[12] + 0.5) >= floor($nm[13] + 0.5)) { // 若第13个中气zq(12)大于或等于第14个新月nm(13)
            for ($i = 1; $i <= 14; $i++) { // 表示此两个冬至之间的11个中气要放到12个朔望月中,
                // 至少有一个朔望月不含中气,第一个不含中气的月即为闰月
                // 若阴历腊月起始日大於冬至中气日,且阴历正月起始日小于或等于大寒中气日,则此月为闰月,其余同理
                if (floor($nm[$i] + 0.5) > floor($zq[$i - 1 - $yz] + 0.5)
                    && floor($nm[$i + 1] + 0.5) <= floor($zq[$i - $yz] + 0.5)) {
                    $mc[$i] = $i - 0.5;
                    $yz = 1; // 标示遇到闰月
                } else {
                    $mc[$i] = floatval($i - $yz); // 遇到闰月开始,每个月号要减1
                }
            }

        } else { // 否则表示两个连续冬至之间只有11个整月,故无闰月
            for ($i = 0; $i <= 12; $i++) { // 直接赋予这12个月月代码
                $mc[$i] = (float)$i;
            }
            for ($i = 13; $i <= 14; $i++) { // 处理次一置月年的11月与12月,亦有可能含闰月
                // 若次一阴历腊月起始日大于附近的冬至中气日,且农历正月起始日小于或等于大寒中气日,则此月为腊月,次一正月同理.
                if (($nm[$i] + 0.5) > floor($zq[$i - 1 - $yz] + 0.5)
                    && floor($nm[$i + 1] + 0.5) <= floor($zq[$i - $yz] + 0.5)) {
                    $mc[$i] = $i - 0.5;
                    $yz = 1; // 标示遇到闰月
                } else {
                    $mc[$i] = floatval($i - $yz); // 遇到闰月开始,每个月号要减1
                }
            }
        }

        return [$zq, $nm, $mc];
    }

    /**
     * 求出自冬至点为起点的连续16个中气
     *
     * @param int $year
     *
     * @return array array[15]float  [儒略日...]
     */
    private static function qiSinceWinterSolstice(int $year): array
    {
        if (isset(self::$zqs[$year])) {
            return self::$zqs[$year];
        }

        $zq = [];

        $lastYearAsts = self::lastYearSolarTerms($year);

        for ($i = 18; $i <= 22; $i++) {
            if ($i % 2 != 0) continue;
            $zq[] = $lastYearAsts[$i] + self::CHINESE_TIME_OFFSET; // 农历计算需要，加上中国(东八区)时差
        }
        // $zq[0] = $lastYearAsts[18] + self::CHINESE_TIME_OFFSET; // 冬至(上一年)
        // $zq[1] = $lastYearAsts[20] + self::CHINESE_TIME_OFFSET; // 大寒
        // $zq[2] = $lastYearAsts[22] + self::CHINESE_TIME_OFFSET; // 雨水

        $asts = self::adjustedSolarTerms($year); // 求出指定年节气之JD值

        foreach ($asts as $k => $v) {
            if ($k % 2 != 0) {
                continue;
            }
            $zq[] = $v + self::CHINESE_TIME_OFFSET; // 农历计算需要，加上中国(东八区)时差
        }

        self::$zqs[$year] = $zq;

        return $zq;
    }

    /**
     * 取出上一年从冬至开始的6个节气
     *
     * @param int $year 当前年，方法内会自动减1
     *
     * @return array array[6]float  [儒略日...]
     */
    protected static function lastYearSolarTerms(int $year): array
    {
        return self::adjustedSolarTerms($year - 1, 18, 23);
    }

    /**
     * 获取指定年以春分开始的节气,
     * 经过摄动值和deltaT调整后的jd
     *
     * @param int $year  指定的年份数
     * @param int $start 取节气开始数
     * @param int $end   取节气结束数
     *
     * @return array array[]float [儒略日...]
     */
    protected static function adjustedSolarTerms(int $year, int $start = 0, int $end = 25): array
    {
        if (isset(self::$msts[$year])) {
            $mst = self::$msts[$year];
        } else {
            $mst = self::meanSolarTerms($year);
            self::$msts[$year] = $mst;
        }

        $jq = [];
        foreach ($mst as $i => $jd) {
            if ($i < $start) {
                continue;
            }
            if ($i > $end) {
                continue;
            }

            $pert = self::perturbation($jd); // perturbation
            $dtd = self::deltaTDays($year, floor(($i + 1) / 2) + 3); // delta T(天)
            $jq[$i] = $jd + $pert - $dtd; // 加上摄动调整值ptb,减去对应的Delta T值(分钟转换为日)
        }

        return $jq;
    }

    /**
     * 获取指定年以春分开始的24节气(为了确保覆盖完一个公历年，该方法多取2个节气),
     * 注意：该方法取出的节气时间是未经微调的。
     *
     * @param int $year 年份数字
     *
     * @return array map[int]float
     */
    protected static function meanSolarTerms(int $year): array
    {
        // 该年的春分点jd
        $ve = self::vernalEquinox($year);

        // 该年的回归年长(天)
        // 两个春分点之间为一个回归年长
        $ty = self::vernalEquinox($year + 1) - $ve;

        // 多取2个节气确保覆盖完一个公历年 24+2
        $stNum = 26;

        // 以春分点为起点，可在轨道上每隔15度取一个点，将轨道划分为24个区，这24个区就是24节气。
        // 由Kepler's second law (law of areas)定律可知，
        // 地球在绕日的轨道上，单位时间内扫过的面积是固定值，因此，在不同节气内，地球的运行速度是不同的，
        // 在近日点附近的速度较快，在远日点附近的速度较慢

        $ath = 2 * M_PI / 24;

        $T = self::julianThousandYear($ve); // 计算标准历元起的儒略千年数
        $e = 0.0167086342
            - 0.0004203654 * $T
            - 0.0000126734 * pow($T, 2)
            + 0.0000001444 * pow($T, 3)
            - 0.0000000002 * pow($T, 4)
            + 0.0000000003 * pow($T, 5);

        // 春分点与近日点之夹角(度)
        $TT = $year / 1000;
        $d = 111.25586939
            - 17.0119934518333 * $TT
            - 0.044091890166673 * pow($TT, 2)
            - 4.37356166661345E-04 * pow($TT, 3)
            + 8.16716666602386E-06 * pow($TT, 4);
        // 将角度转成弧度
        $rvp = deg2rad($d);

        $peri = [];
        for ($i = 0; $i <= $stNum; $i++) {
            $flag = 0;
            $th = $ath * $i + $rvp;
            if ($th > M_PI && $th <= 3 * M_PI) {
                $th = 2 * M_PI - $th;
                $flag = 1;
            }
            if ($th > 3 * M_PI) {
                $th = 4 * M_PI - $th;
                $flag = 2;
            }

            $f1 = 2 * atan((sqrt((1 - $e) / (1 + $e)) * tan($th / 2)));
            $f2 = ($e * sqrt(1 - $e * $e) * sin($th)) / (1 + $e * cos($th));
            $f = ($f1 - $f2) * $ty / 2 / M_PI;
            if ($flag == 1) {
                $f = $ty - $f;
            }
            if ($flag == 2) {
                $f = 2 * $ty - $f;
            }
            $peri[$i] = $f;
        }

        $mst = [];
        for ($i = 0; $i < $stNum; $i++) {
            $mst[$i] = $ve + $peri[$i] - $peri[0];
        }

        return $mst;
    }

    /**
     * 计算指定年的春分点
     *
     * @param int $year
     *
     * @return float 春分点的儒略日
     */
    protected static function vernalEquinox(int $year): float
    {

        // 算法公式摘自Jean Meeus在1991年出版的《Astronomical Algorithms》第27章 Equinoxes and solsticesq (第177页)
        // http://www.agopax.it/Libri_astronomia/pdf/Astronomical%20Algorithms.pdf
        // 此公式在-1000年至3000年之间比较准确
        // 在公元前1000年之前或公元3000年之后也可以延申使用，但因外差法求值，年代越远，算出的结果误差就越大。

        if ($year >= 1000 && $year <= 3000) {
            $m = ($year - 2000) / 1000.0;
            return 2451623.80984 + 365242.37404 * $m + 0.05169 * pow($m, 2) - 0.00411 * pow($m, 3) - 0.00057 * pow($m, 4);
        } else {
            $m = $year / 1000.0;
            return 1721139.29189 + 365242.1374 * $m + 0.06134 * pow($m, 2) + 0.00111 * pow($m, 3) - 0.00071 * pow($m, 4);
        }
    }

    /**
     * 计算标准历元起的儒略千年数
     *
     * @param float $jd 要计算的儒略日
     *
     * @return float 儒略千年数
     */
    protected static function julianThousandYear(float $jd): float
    {
        return self::julianDayFromJ2000($jd) / self::JULIAN_DAYS_OF_A_YEAR / 1000.0;
    }

    /**
     * 计算标准历元起的儒略日
     *
     * @param float $jd TT时间的儒略日
     *
     * @return float 标准历元起的儒略日
     */
    protected static function julianDayFromJ2000(float $jd): float
    {
        return $jd - self::JULIAN_DAY_J2000;
    }

    /**
     * 地球在绕日运行时会因受到其他星球之影响而产生摄动(perturbation)
     *
     * @param float $jd 儒略日
     *
     * @return float
     */
    protected static function perturbation(float $jd): float
    {
        // 算法公式摘自Jean Meeus在1991年出版的《Astronomical Algorithms》第27章 Equinoxes and solsticesq (第177页)
        // http://www.agopax.it/Libri_astronomia/pdf/Astronomical%20Algorithms.pdf
        // 公式: 0.00001S/∆λ
        // S = Σ[A cos(B+CT)]
        // B和C的单位是度
        // T = JDE0 - J2000 / 36525
        // J2000 = 2451545.0
        // 36525是儒略历一个世纪的天数
        // ∆λ = 1 + 0.0334cosW+0.0007cos2W
        // W = (35999.373T - 2.47)π/180
        // 注释: Liu Min<liujiawm@163.com> https://github.com/liujiawm


        // 公式中A,B,C的值
        $ptsA = [485, 203, 199, 182, 156, 136, 77, 74, 70, 58, 52, 50, 45, 44, 29, 18, 17, 16, 14, 12, 12, 12, 9, 8];
        $ptsB = [324.96, 337.23, 342.08, 27.85, 73.14, 171.52, 222.54, 296.72, 243.58, 119.81, 297.17, 21.02, 247.54,
            325.15, 60.93, 155.12, 288.79, 198.04, 199.76, 95.39, 287.11, 320.81, 227.73, 15.45];
        $ptsC = [1934.136, 32964.467, 20.186, 445267.112, 45036.886, 22518.443, 65928.934, 3034.906, 9037.513,
            33718.147, 150.678, 2281.226, 29929.562, 31555.956, 4443.417, 67555.328, 4562.452, 62894.029, 31436.921,
            14577.848, 31931.756, 34777.259, 1222.114, 16859.074];

        $T = self::julianCentury($jd); // $T是以儒略世纪(36525日)为单位，以J2000(儒略日2451545.0)为0点

        $s = 0.0;
        for ($k = 0; $k <= 23; $k++) {
            // $s = $s + $ptsA[$k] * cos($ptsB[$k] * 2 * M_PI / 360 + $ptsC[$k] * 2 * M_PI / 360 * $t);
            $s = $s + $ptsA[$k] * cos(deg2rad($ptsB[$k]) + deg2rad($ptsC[$k]) * $T);
        }

        //$w = 35999.373 * $t - 2.47;
        // $l = 1 + 0.0334 * cos($w * 2 * M_PI / 360) + 0.0007 * cos(2 * $w * 2 * M_PI / 360);
        $W = deg2rad(35999.373 * $T - 2.47);
        $l = 1 + 0.0334 * cos($W) + 0.0007 * cos(2 * $W);

        return 0.00001 * $s / $l;
    }

    /**
     * 计算标准历元起的儒略世纪
     *
     * @param float $jd 要计算的儒略日
     *
     * @return float 儒略世纪数
     */
    protected static function julianCentury(float $jd): float
    {
        return self::julianDayFromJ2000($jd) / self::JULIAN_DAYS_OF_A_YEAR / 100.0;
    }

    /**
     * 地球自转速度调整值Delta T(以∆T表示)
     * 地球时和UTC的时差 单位:天(days)
     * 精确至月份
     *
     * @param int   $year  年
     * @param float $month 月
     *
     * @return float ∆T 单位:天(days)
     */
    protected static function deltaTDays(int $year, float $month): float
    {
        $dt = self::deltaTSeconds($year, $month);
        return $dt / 60.0 / 60.0 / 24.0; // 将秒转换为天
    }

    /**
     * 地球自转速度调整值Delta T(以∆T表示)
     * 地球时和UTC的时差 单位:秒(seconds)
     * 精确至月份
     *
     * @param int   $year  年
     * @param float $month 月
     *
     * @return float ∆T 单位:秒(seconds)
     */
    protected static function deltaTSeconds(int $year, float $month): float
    {
        // 计算方法参考: https://eclipse.gsfc.nasa.gov/SEhelp/deltatpoly2004.html
        // 此算法在-1999年到3000年之间有效

        if ($year < -1999 || $year > 3000) {
            throw new InvalidArgumentException('计算DeltaT值限-1999年至3000年之间有效');
        }

        $year = (float)$year;  // 转成float

        $y = $year + ($month - 0.5) / 12;

        if ($year < -500) {
            $u = ($year - 1820) / 100.0;
            $dt = -20 + 32 * $u * $u;
        } else if ($year < 500) {
            $u = $y / 100;
            $dt = 10583.6
                - 1014.41 * $u
                + 33.78311 * pow($u, 2)
                - 5.952053 * pow($u, 3)
                - 0.1798452 * pow($u, 4)
                + 0.022174192 * pow($u, 5)
                + 0.0090316521 * pow($u, 6);
        } else if ($year < 1600) {
            $u = ($y - 1000) / 100;
            $dt = 1574.2
                - 556.01 * $u
                + 71.23472 * pow($u, 2)
                + 0.319781 * pow($u, 3)
                - 0.8503463 * pow($u, 4)
                - 0.005050998 * pow($u, 5)
                + 0.0083572073 * pow($u, 6);
        } else if ($year < 1700) {
            $t = $y - 1600;
            $dt = 120
                - 0.9808 * $t
                - 0.01532 * pow($t, 2)
                + pow($t, 3) / 7129;
        } else if ($year < 1800) {
            $t = $y - 1700;
            $dt = 8.83
                + 0.1603 * $t
                - 0.0059285 * pow($t, 2)
                + 0.00013336 * pow($t, 3)
                - pow($t, 4) / 1174000;
        } else if ($year < 1860) {
            $t = $y - 1800;
            $dt = 13.72
                - 0.332447 * $t
                + 0.0068612 * pow($t, 2)
                + 0.0041116 * pow($t, 3)
                - 0.00037436 * pow($t, 4)
                + 0.0000121272 * pow($t, 5)
                - 0.0000001699 * pow($t, 6)
                + 0.000000000875 * pow($t, 7);
        } else if ($year < 1900) {
            $t = $y - 1860;
            $dt = 7.62
                + 0.5737 * $t
                - 0.251754 * pow($t, 2)
                + 0.01680668 * pow($t, 3)
                - 0.0004473624 * pow($t, 4)
                + pow($t, 5) / 233174;
        } else if ($year < 1920) {
            $t = $y - 1900;
            $dt = -2.79
                + 1.494119 * $t
                - 0.0598939 * pow($t, 2)
                + 0.0061966 * pow($t, 3)
                - 0.000197 * pow($t, 4);
        } else if ($year < 1941) {
            $t = $y - 1920;
            $dt = 21.2
                + 0.84493 * $t
                - 0.0761 * pow($t, 2)
                + 0.0020936 * pow($t, 3);
        } else if ($year < 1961) {
            $t = $y - 1950;
            $dt = 29.07
                + 0.407 * $t
                - pow($t, 2) / 233
                + pow($t, 3) / 2547;
        } else if ($year < 1986) {
            $t = $y - 1975;
            $dt = 45.45
                + 1.067 * $t
                - pow($t, 2) / 260
                - pow($t, 3) / 718;
        } else if ($year < 2005) {
            $t = $y - 2000;
            $dt = 63.86
                + 0.3345 * $t
                - 0.060374 * pow($t, 2)
                + 0.0017275 * pow($t, 3)
                + 0.000651814 * pow($t, 4)
                + 0.00002373599 * pow($t, 5);
        } else if ($year < 2050) {
            $t = $y - 2000;
            $dt = 62.92
                + 0.32217 * $t
                + 0.005589 * pow($t, 2);
        } else if ($year < 2150) {
            $u = ($y - 1820) / 100;
            $dt = -20
                + 32 * pow($u, -2)
                - 0.5628 * (2150 - $y);
        } else {
            $u = ($y - 1820) / 100;
            $dt = -20 + 32 * pow($u, 2);
        }

        // 以上的∆T值均假定月球的长期加速度为-26弧秒/cy^2
        // 而Canon中使用的ELP-2000/82月历使用的值略有不同，为-25.858弧秒/cy^2
        // 因此，必须在∆T多项式表达式得出的值上加上一个小的修正“c”，然后才能将其用于标准中
        // 由于1955年至2005年期间的ΔT值是独立于任何月历而得出的，因此该期间无需校正。
        if ($year < 1955 || $year >= 2005) {
            $c = -0.000012932 * ($y - 1955) * ($y - 1955);

            $dt += $c;
        }

        return (float)$dt;
    }

    /**
     * 求算以含冬至中气为阴历11月开始的连续16个朔望月
     *
     * @param int   $year 指定的年份数
     * @param float $dzjd 上一年的冬至jd
     * @return array
     */
    private static function sMsinceWinterSolstice(int $year, float $dzjd): array
    {
        $tjd = [];

        $novemberJd = self::julianDay($year - 1, 11, 1, 0); // 求年初前两个月附近的新月点(即前一年的11月初)

        $kn = self::referenceLunarMonthNum($novemberJd); // 求得自2000年1月起第kn个平均朔望日及期JD值

        for ($i = 0; $i <= 19; $i++) { // 求出连续20个朔望月
            $k = $kn + $i;

            $tjd[$i] = self::trueNewMoon($k) + self::CHINESE_TIME_OFFSET; // 以k值代入求瞬时朔望日,农历计算需要，加上中国(东八区)时差

            // 修正dynamical time to Universal time
            // 1为1月，0为前一年12月，-1为前一年11月(当i=0时，i-1代表前一年11月)
            $tjd[$i] = $tjd[$i] - self::deltaTDays($year, $i - 1.0);
        }

        $jj = 0;
        for ($j = 0; $j <= 18; $j++) {
            if (floor($tjd[$j] + 0.5) > floor($dzjd + 0.5)) {
                $jj = $j;
                break;
            } // 已超过冬至中气(比较日期法)
        }

        $nm = [];
        for ($k = 0; $k <= 15; $k++) { // 取上一步的索引值
            $nm[$k] = $tjd[$jj - 1 + $k]; // 重排索引,使含冬至朔望月的索引为0
        }

        return $nm;
    }

    /**
     * 计算TT时间的儒略日
     *
     * @param int $year        年
     * @param int $month       月 默认: 1
     * @param int $day         日 默认: 1
     * @param int $hours       时 默认: 12
     * @param int $minute      分 默认: 0
     * @param int $second      秒 默认: 0
     * @param int $millisecond 毫秒 默认: 0
     *
     * @return float 儒略日(JD)
     */
    protected static function julianDay(int $year, int $month = 1, int $day = 1, int $hours = 12, int $minute = 0, int $second = 0, int $millisecond = 0): float
    {
        // 依据儒略历废弃日期和格里历实施日期，使用两个不同的公式计算儒略日
        if ($year < (self::JULIAN_ABANDONMENT_DATE)['year'] || ($year === (self::JULIAN_ABANDONMENT_DATE)['year'] && $month < (self::JULIAN_ABANDONMENT_DATE)['month']) || ($year === (self::JULIAN_ABANDONMENT_DATE)['year'] && $month === (self::JULIAN_ABANDONMENT_DATE)['month'] && $day <= (self::JULIAN_ABANDONMENT_DATE)['day'])) {
            // 儒略历日期
            $jd = self::julianDayInJulian($year, $month, $day, $hours, $minute, $second, $millisecond);
        } else if ($year > (self::GREGORIAN_ADOPTION_DATE)['year'] || ($year === (self::GREGORIAN_ADOPTION_DATE)['year'] && $month > (self::GREGORIAN_ADOPTION_DATE)['month']) || ($year === (self::GREGORIAN_ADOPTION_DATE)['year'] && $month === (self::GREGORIAN_ADOPTION_DATE)['month'] && $day >= (self::GREGORIAN_ADOPTION_DATE)['day'])) {
            // 格里历日期
            $jd = self::julianDayInGregorian($year, $month, $day, $hours, $minute, $second, $millisecond);
        } else {
            // 在儒略历废弃与格里历实施这中间有一段日期，这段日期的儒略日计算使用格里历实施的起始日计算
            $jd = self::julianDayInGregorian((self::GREGORIAN_ADOPTION_DATE)['year'], (self::GREGORIAN_ADOPTION_DATE)['month'], (self::GREGORIAN_ADOPTION_DATE)['day']);
        }

        return $jd;
    }

    /**
     * 儒略历日期(TT)转儒略日
     *
     * @param int $year        年
     * @param int $month       月 默认: 1
     * @param int $day         日 默认: 1
     * @param int $hours       时 默认: 12
     * @param int $minute      分 默认: 0
     * @param int $second      秒 默认: 0
     * @param int $millisecond 毫秒 默认: 0
     *
     * @return float 儒略日(JD)
     */
    private static function julianDayInJulian(int $year, int $month = 1, int $day = 1, int $hours = 12, int $minute = 0, int $second = 0, int $millisecond = 0): float
    {
        // 计算公式参见: https://zh.wikipedia.org/wiki/%E5%84%92%E7%95%A5%E6%97%A5
        // 或参见: https://blog.csdn.net/weixin_42763614/article/details/82880007

        // 算式适用于儒略历日期(中午12点 UT)
        // JDN表达式与JD的关系是: JDN = floor(JD + 0.5)

        $a = floor((14 - $month) / 12);
        $y = $year + 4800 - $a;
        $m = $month + 12 * $a - 3;
        $second += $millisecond / 1000.0;
        $d = $day + $hours / 24.0 + $minute / 1440.0 + $second / 86400.0;

        $jdn = $d + floor((153 * $m + 2) / 5) + 365 * $y + floor($y / 4) - 32083;

        return $jdn - 0.5; // jd值是JDN-0.5
    }

    /**
     * 格里历日期(TT)转儒略日
     *
     * @param int $year        年
     * @param int $month       月 默认: 1
     * @param int $day         日 默认: 1
     * @param int $hours       时 默认: 12
     * @param int $minute      分 默认: 0
     * @param int $second      秒 默认: 0
     * @param int $millisecond 毫秒 默认: 0
     *
     * @return float 儒略日(JD)
     */
    private static function julianDayInGregorian(int $year, int $month = 1, int $day = 1, int $hours = 12, int $minute = 0, int $second = 0, int $millisecond = 0): float
    {
        // 计算公式参见: https://zh.wikipedia.org/wiki/%E5%84%92%E7%95%A5%E6%97%A5
        // 或参见: https://blog.csdn.net/weixin_42763614/article/details/82880007

        // 算式适用于格里历日期(中午12点 UT)
        // JDN表达式与JD的关系是: JDN = floor(JD + 0.5)

        $a = floor((14 - $month) / 12);
        $y = $year + 4800 - $a;
        $m = $month + 12 * $a - 3;
        $second += $millisecond / 1000.0;
        $d = $day + $hours / 24.0 + $minute / 1440.0 + $second / 86400.0;

        $jdn = $d + floor((153 * $m + 2) / 5) + 365 * $y + floor($y / 4) - floor($y / 100) + floor($y / 400) - 32045;

        return $jdn - 0.5; // jd值是JDN-0.5
    }

    /**
     * 对于指定的日期时刻JD值jd,算出其为相对于基准点(之后或之前)的第几个朔望月
     *
     * @param float $jd
     *
     * @return int 反回相对于基准点(之后或之前)的朔望月序数
     */
    protected static function referenceLunarMonthNum(float $jd): int
    {
        $k = floor(($jd - self::BNM) / self::MSM);
        return (int)$k;
    }

    /**
     * 求出实际新月点,
     * 以k值代入求瞬时朔望日
     *
     * @param float $k
     *
     * @return float
     */
    protected static function trueNewMoon(float $k): float
    {
        // 对于指定的日期时刻JD值jd,算出其为相对于基准点(之后或之前)的第k个朔望月之内。
        // k=INT(jd-bnm)/msm
        // 新月点估值(new moon estimated)为：nme=bnm+msm×k
        // 估计的世纪变数值为：t = (nme - J2000) / 36525
        // 此t是以2000年1月1日12时(TT)为0点，以100年为单位的时间变数，
        // 由于朔望月长每个月都不同，msm所代表的只是其均值，所以算出新月点后，还需要加上一个调整值。
        // adj = 0.0001337×t×t - 0.00000015×t×t×t + 0.00000000073×t×t×t×t
        // 指定日期时刻所属的均值新月点JD值(mean new moon)：mnm=nme+adj

        $nme = self::BNM + self::MSM * $k;

        $t = self::julianCentury($nme);
        $t2 = pow($t, 2); // square for frequent use
        $t3 = pow($t, 3); // cube for frequent use
        $t4 = pow($t, 4); // to the fourth

        // mean time of phase
        $mnm = $nme + 0.0001337 * $t2 - 0.00000015 * $t3 + 0.00000000073 * $t4;

        // Sun's mean anomaly(地球绕太阳运行均值近点角)(从太阳观察)
        $m = 2.5534 + 29.10535669 * $k - 0.0000218 * $t2 - 0.00000011 * $t3;

        // Moon's mean anomaly(月球绕地球运行均值近点角)(从地球观察)
        $ms = 201.5643 + 385.81693528 * $k + 0.0107438 * $t2 + 0.00001239 * $t3 - 0.000000058 * $t4;

        // Moon's argument of latitude(月球的纬度参数)
        $f = 160.7108 + 390.67050274 * $k - 0.0016341 * $t2 - 0.00000227 * $t3 + 0.000000011 * $t4;

        // Longitude of the ascending node of the lunar orbit(月球绕日运行轨道升交点之经度)
        $omega = 124.7746 - 1.5637558 * $k + 0.0020691 * $t2 + 0.00000215 * $t3;

        // 乘式因子
        $e = 1 - 0.002516 * $t - 0.0000074 * $t2;

        $apt1 = -0.4072 * sin((M_PI / 180) * $ms);
        $apt1 += 0.17241 * $e * sin((M_PI / 180) * $m);
        $apt1 += 0.01608 * sin((M_PI / 180) * 2 * $ms);
        $apt1 += 0.01039 * sin((M_PI / 180) * 2 * $f);
        $apt1 += 0.00739 * $e * sin((M_PI / 180) * ($ms - $m));
        $apt1 -= 0.00514 * $e * sin((M_PI / 180) * ($ms + $m));
        $apt1 += 0.00208 * $e * $e * sin((M_PI / 180) * (2 * $m));
        $apt1 -= 0.00111 * sin((M_PI / 180) * ($ms - 2 * $f));
        $apt1 -= 0.00057 * sin((M_PI / 180) * ($ms + 2 * $f));
        $apt1 += 0.00056 * $e * sin((M_PI / 180) * (2 * $ms + $m));
        $apt1 -= 0.00042 * sin((M_PI / 180) * 3 * $ms);
        $apt1 += 0.00042 * $e * sin((M_PI / 180) * ($m + 2 * $f));
        $apt1 += 0.00038 * $e * sin((M_PI / 180) * ($m - 2 * $f));
        $apt1 -= 0.00024 * $e * sin((M_PI / 180) * (2 * $ms - $m));
        $apt1 -= 0.00017 * sin((M_PI / 180) * $omega);
        $apt1 -= 0.00007 * sin((M_PI / 180) * ($ms + 2 * $m));
        $apt1 += 0.00004 * sin((M_PI / 180) * (2 * $ms - 2 * $f));
        $apt1 += 0.00004 * sin((M_PI / 180) * (3 * $m));
        $apt1 += 0.00003 * sin((M_PI / 180) * ($ms + $m - 2 * $f));
        $apt1 += 0.00003 * sin((M_PI / 180) * (2 * $ms + 2 * $f));
        $apt1 -= 0.00003 * sin((M_PI / 180) * ($ms + $m + 2 * $f));
        $apt1 += 0.00003 * sin((M_PI / 180) * ($ms - $m + 2 * $f));
        $apt1 -= 0.00002 * sin((M_PI / 180) * ($ms - $m - 2 * $f));
        $apt1 -= 0.00002 * sin((M_PI / 180) * (3 * $ms + $m));
        $apt1 += 0.00002 * sin((M_PI / 180) * (4 * $ms));

        $apt2 = 0.000325 * sin((M_PI / 180) * (299.77 + 0.107408 * $k - 0.009173 * $t2));
        $apt2 += 0.000165 * sin((M_PI / 180) * (251.88 + 0.016321 * $k));
        $apt2 += 0.000164 * sin((M_PI / 180) * (251.83 + 26.651886 * $k));
        $apt2 += 0.000126 * sin((M_PI / 180) * (349.42 + 36.412478 * $k));
        $apt2 += 0.00011 * sin((M_PI / 180) * (84.66 + 18.206239 * $k));
        $apt2 += 0.000062 * sin((M_PI / 180) * (141.74 + 53.303771 * $k));
        $apt2 += 0.00006 * sin((M_PI / 180) * (207.14 + 2.453732 * $k));
        $apt2 += 0.000056 * sin((M_PI / 180) * (154.84 + 7.30686 * $k));
        $apt2 += 0.000047 * sin((M_PI / 180) * (34.52 + 27.261239 * $k));
        $apt2 += 0.000042 * sin((M_PI / 180) * (207.19 + 0.121824 * $k));
        $apt2 += 0.00004 * sin((M_PI / 180) * (291.34 + 1.844379 * $k));
        $apt2 += 0.000037 * sin((M_PI / 180) * (161.72 + 24.198154 * $k));
        $apt2 += 0.000035 * sin((M_PI / 180) * (239.56 + 25.513099 * $k));
        $apt2 += 0.000023 * sin((M_PI / 180) * (331.55 + 3.592518 * $k));

        return $mnm + $apt1 + $apt2;
    }

    /**
     * 从农历的月代码$mc中找出闰月
     * @param array $mc
     * @return int 0无闰月，1表示上一年的11月，2表示上一年的12月，3表示本年正月，4表示本年二月，5表示本年三月...依此类推
     */
    private static function mcLeap(array $mc): int
    {
        $leap = 0; // 若闰月旗标为0代表无闰月
        for ($j = 1; $j <= 14; $j++) { // 确认本年的上一年11月开始各月是否闰月
            if ($mc[$j] - floor($mc[$j]) > 0) { // 若是,则将此闰月代码放入闰月旗标内
                $leap = intval(floor($mc[$j] + 0.5)); // leap = 0对应农历上一年11月,1对应农历上一年12月,2对应农历本年1月,依此类推.
                break;
            }
        }
        return $leap;
    }


}