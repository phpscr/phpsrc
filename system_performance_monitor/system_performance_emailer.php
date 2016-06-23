<?php

require_once 'json-format/format_json.php';
require_once 'smtp/sendmail.php';
require 'config.inc.php';

$readcpustathistory = 1;
exec("tail -n $readcpustathistory $filepath", $system_performance_monitor);
$readhistorymax = min(count($system_performance_monitor), $readcpustathistory);
$i = $readhistorymax;
foreach ($system_performance_monitor as $key => $value) {
    --$i;
    $monitor[$i] = json_decode($value, true);
}
$system_performance_info = format_json($value);
$time = $monitor[0]['TIME'];
sendemailbysmtp('Your system_performance_info at '.date('F j, Y, g:i a', $time), $system_performance_info, 'Receive Email address');
