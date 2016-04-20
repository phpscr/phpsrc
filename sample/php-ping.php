<?php
$to_ping='www.sina.com';
$count=2;
$psize=66;
echo "正在执行php ping命令，请等待...\n<br><br>";
#flush();
//$dt = new DateTime();
//echo $dt->format('Y-m-d H:i:s');
while(1){
 echo "<pre>";
 exec("ping -c $count -s $psize $to_ping", $list);
 for($i=0;$i<count($list);$i++){
  print $list[$i]."\n";
 }
 echo "</pre>";
# flush();
 echo "+++++++++++++++++++++++++++++++++++++++++";

 sleep(3);
}
?>
