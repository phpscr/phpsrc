<?php 
$fp=fsockopen('time.nist.gov',13,$errno,$errstr,90);  
print_r($fp);
$ufc = explode(' ',fread($fp,date('Y')));  
$date = explode('-',$ufc[1]);  
$processdate = $date[1].'-'.$date[2].'-'. date('Y').' '.$ufc[2];  
   
switch($ufc[5])  
{  
    case 0: echo '精确'; break;  
   
    case 1: echo '误差：0-5s'; break;    
   
    case 2: echo '误差： > 5s'; break;  
   
    default: echo '硬件出错！'; break;  
}  
   
echo gmttolocal($processdate,8); // 中国  
   
function gmttolocal($mydate,$mydifference)    
{  
    $datetime = explode(" ",$mydate);  
    $dateexplode = explode("-",$datetime[0]);  
    $timeexplode = explode(":",$datetime[1]);  
    $unixdatetime = mktime($timeexplode[0]+$mydifference,$timeexplode[1],0,$dateexplode[0],$dateexplode[1],$dateexplode[2]);  
    return date("m/d/Y H:i:s",$unixdatetime);  
}
 

?>  
