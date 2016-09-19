<?php
//Copyright (c) 2003-06 PHPWind
!function_exists('readover') && exit('Forbidden');

($_SERVER['HTTP_X_FORWARDED_FOR'] || $_SERVER['HTTP_CLIENT_IP']) && $c_agentip=1;
$c_banedip=readover(D_P.'data/ccbanip.txt');
if($c_ipoffset=strpos($c_banedip."\n","\t".$onlineip."\n")){
	$c_ltt=(int)substr($c_banedip,$c_ipoffset-10,10);
	if($timestamp-$c_ltt<15){
		if($timestamp-$c_ltt>5){
			writeover(D_P.'data/ccbanip.txt',str_replace($c_ltt."\t".$onlineip,$timestamp."\t".$onlineip,$c_banedip));
		}
		exit("Turn off CC, Please refresh after 15 secs");
	}else{
		writeover(D_P.'data/ccbanip.txt',str_replace("\n".$c_ltt."\t".$onlineip,'',$c_banedip));
	}
}
$c_banip_a=explode("\n",$c_banedip);
if($c_agentip==1 || $db_cc==2){
	$c_time=0;
	if($c_fp=@fopen(D_P.'data/ccip.txt','rb')){
		flock($c_fp,LOCK_SH);
		$c_size=27*500;
		fseek($c_fp,-$c_size,SEEK_END);
		while (!feof ($c_fp)) {
			$c_value=explode("\t",fgets($c_fp,29));
			if($timestamp-$c_value[0]<10 && trim($c_value[1])==$onlineip){
				$c_time++;
				if($c_time>15) break;
			}
		}
		fclose($c_fp);
	}
	if($c_time>15){
		array_push($c_banip_a,$timestamp."\t".$onlineip);
		$c_banip_a=array_slice($c_banip_a,-150);
		writeover(D_P.'data/ccbanip.txt',implode("\n",$c_banip_a));
		exit('Turn off CC');
	}
	if(@filesize(D_P.'data/ccip.txt')>27*1000){
		P_unlink(D_P.'data/ccip.txt');
	}
	writeover(D_P.'data/ccip.txt',$timestamp."\t".$onlineip."\n",'ab');
}
?>