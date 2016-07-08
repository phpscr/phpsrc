<?php
//本周的第一天和最后一天
date_default_timezone_set('PRC');
$date=new DateTime();
$date->modify('this week');
$first_day_of_week=$date->format('Y-m-d');
echo $first_day_of_week;
$date->modify('this week +6 days');
$end_day_of_week=$date->format('Y-m-d');
echo $end_day_of_week;
//本日的下一日
$date = new DateTime('now');
$date->modify('+1 day');
echo $date->format('Y-m-d');
?> 
?>
