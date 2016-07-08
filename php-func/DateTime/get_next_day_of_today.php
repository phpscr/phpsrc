<?php
date_default_timezone_set('PRC'); 
$date=new DateTime('now');
echo $date->format('Y-m-d H-i-s').PHP_EOL;
echo $date->format('w').PHP_EOL;
$date->modify('+1 day');
echo $date->format('Y-m-d').PHP_EOL;
echo $date->format('w').PHP_EOL;
echo time().PHP_EOL;
echo date('m').PHP_EOL;
?> 
