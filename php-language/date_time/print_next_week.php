<?php
date_default_timezone_set("Asia/Shanghai"); 
$nextWeek = time() + (7 * 24 * 60 * 60);
$nextDay = time() + (1 * 24 * 60 * 60);
$next2Day = time() + (2 * 24 * 60 * 60);
$next3Day = time() + (3 * 24 * 60 * 60);
$next4Day = time() + (4 * 24 * 60 * 60);
$next5Day = time() + (5 * 24 * 60 * 60);
$next6Day = time() + (6 * 24 * 60 * 60);
                   // 7 days; 24 hours; 60 mins; 60secs
echo 'Now:       '. date('Y-m-d') ."\n";
echo 'Next Week: '. date('Y-m-d', $nextWeek) ."\n";
// or using strtotime():
//echo 'Next Week: '. date('Y-m-d', strtotime('+1 week')) ."\n";
echo 'Next 1 Day: '. date('Y-m-d', $nextDay) ."\n";
echo 'Next 2 Day: '. date('Y-m-d', $next2Day) ."\n";
echo 'Next 3 Day: '. date('Y-m-d', $next3Day) ."\n";
echo 'Next 4 Day: '. date('Y-m-d', $next4Day) ."\n";
echo 'Next 5 Day: '. date('Y-m-d', $next5Day) ."\n";
echo 'Next 6 Day: '. date('Y-m-d', $next6Day) ."\n";
?> 
