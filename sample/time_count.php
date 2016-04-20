<?php 
date_default_timezone_set('Asia/Hong_Kong');
$startDate = '2016-4-1';
$endDate = '2016-6-1';
 
// 将日期转换为Unix时间戳
$startDateStr = strtotime($startDate);
$endtDateStr = strtotime($endDate);
$total = $endtDateStr-$startDateStr;
 
$now = strtotime(date('Y-m-d'));
$remain = $endtDateStr-$now;
 
echo '为期：'.$total/(3600*24).'天<br>';
echo '剩余：'.$remain/(3600*24).'天';

//简单的剩余时间计算：
/* 
date_default_timezone_set('Asia/Hong_Kong');
$startTime = '09:00:00';
$endTime = '18:00:00';
 
// 将时间转化为unix时间戳
$startTimeStr = strtotime($startTime);
$endTimeStr = strtotime($endTime);
$total = $endTimeStr - $startTimeStr;
 
$restHours = 1; // 休息1小时
 
$now = strtotime(date('H:i:s'));
$remain = $endTimeStr - $now;
 
echo '上班时间：'.($total/3600-$restHours).'小时<br>';
echo '还有：'.floor(($remain/3600)).'小时'.floor($remain/60).'分钟下班';
 
*/
?>
