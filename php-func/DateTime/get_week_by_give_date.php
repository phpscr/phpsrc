<?php
/**
 * 计算指定日期的一周开始及结束日期
 * @param  DateTime $date  日期
 * @param  Int      $start 周几作为一周的开始 1-6为周一~周六，0为周日，默认0
 * @retrun Array
 */
date_default_timezone_set('PRC');
function getWeekRange($date, $start=0){

    // 将日期转时间戳
    $dt = new DateTime($date);
    $timestamp = $dt->format('U');
    
    // 获取日期是周几
    $day = (new DateTime('@'.$timestamp))->format('w');

    // 计算开始日期
    if($day>=$start){
        $startdate_timestamp = mktime(0,0,0,date('m',$timestamp),date('d',$timestamp)-($day-$start),date('Y',$timestamp));
    }elseif($day<$start){
        $startdate_timestamp = mktime(0,0,0,date('m',$timestamp),date('d',$timestamp)-7+$start-$day,date('Y',$timestamp));
    }

    // 结束日期=开始日期+6
    $enddate_timestamp = mktime(0,0,0,date('m',$startdate_timestamp),date('d',$startdate_timestamp)+6,date('Y',$startdate_timestamp));

    $startdate = (new DateTime('@'.$startdate_timestamp))->format('Y-m-d');
    $enddate = (new DateTime('@'.$enddate_timestamp))->format('Y-m-d');

    return array($startdate, $enddate);
}

$date = '2016-07-1';
for($start=0; $start<=6; $start++){
    list($startdate, $enddate) = getWeekRange($date, $start);
    echo 'date:'.$date.' week start:'.$start.' range:'.$startdate.', '.$enddate.PHP_EOL;
}
?>
