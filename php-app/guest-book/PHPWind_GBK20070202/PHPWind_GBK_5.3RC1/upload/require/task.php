<?php
!function_exists('readover') && exit('Forbidden');
include_once(D_P.'data/bbscache/plandb.php');
@set_time_limit(600);
@ignore_user_abort(TRUE);
foreach($plandb as $key=>$plan){
	if($timestamp>$plan['nexttime'] && file_exists(R_P.'require/plan/'.$plan['filename'].'.php')){
		$nexttime=nexttime($plan);
		$db->update("UPDATE pw_plan SET usetime='$timestamp',nexttime='$nexttime' WHERE id='$plan[id]'");
		updatecache_plan();
		require_once Pcv(R_P.'require/plan/'.$plan['filename'].'.php');
	}
}
unset($plandb);

function nexttime($plan){
	global $timestamp,$db_timedf;
	
	$t		= gmdate('G',$timestamp+$db_timedf*3600);
	$timenow= (floor($timestamp/3600)-$t)*3600;
	$minute = (int)get_date($timestamp,'i');
	$hour   = get_date($timestamp,'G');
	$day    = get_date($timestamp,'j');
	$month  = get_date($timestamp,'n');
	$year   = get_date($timestamp,'Y');
	$week   = get_date($timestamp,'w');
	$week==0 && $week=7;
	if(is_numeric($plan['month'])){
		$timenow += ($plan['month']-$day)*86400;
	}elseif(is_numeric($plan['week'])){
		$timenow += ($plan['week']-$week)*86400;
	}
	if(is_numeric($plan['day'])){
		$timenow += $plan['day']*3600;
	}
	if($plan['hour']!='*'){
		$hours=explode(',',$plan['hour']);
		asort($hours);
		if(is_numeric($plan['month']) || is_numeric($plan['week']) || is_numeric($plan['day'])){
			foreach($hours as $key=>$value){
				if(($timenow+$value*60)>$plan['usetime'] && ($timenow+$value*60)>$timestamp){
					$timenow +=$value*60;
					return $timenow;
				}
			}
		}else{
			$timenow += $hour*3600;
			for($i=0;$i<2;$i++){
				foreach($hours as $key=>$value){
					if(($timenow+$value*60)>$plan['usetime'] && ($timenow+$value*60)>$timestamp){
						$timenow +=$value*60;
						return $timenow;
					}
				}
				$timenow +=3600;
			}
			return $timenow+$hours['0'];
		}
	}elseif($timenow>$plan['usetime'] && $timenow>$timestamp){
		return $timenow;
	}
	if(is_numeric($plan['month'])){
		if(in_array($month,array('1','3','5','7','8','10','12'))){
			$days=31;
		}elseif($month!=2){
			$days=30;
		}else{
			if(get_date($timestamp,'L')){
				$days=29;
			}else{
				$days=28;
			}
		}
		$timenow += $days*86400;
	}elseif(is_numeric($plan['week'])){
		$timenow += 604800;
	}elseif(is_numeric($plan['day'])){
		$timenow += 86400;
	}
	if($plan['hour']!='*'){
		$timenow += $hours[0]*60;
	}
	if($timenow>$timestamp){
		return $timenow;
	}
	return $timestamp+86400; //³ö´íÇé¿ö
}
function updatecache_plan(){
	global $db;
	$plandb="\$plandb=array(\r\n";
	$plantime=array();
	$query = $db->query("SELECT id,month,week,day,hour,nexttime,filename FROM pw_plan WHERE ifopen='1' ORDER BY id");
	while($rt=db_cv($db->fetch_array($query))){
		$plantime[]=$rt['nexttime'];
		$plandb.="\t'$rt[id]'=>array(\r\n\t";
		foreach($rt as $key=>$value){
			$plandb.="\t'$key'=>'$value',\r\n\t";
		}
		$plandb.="),\r\n";
	}
	$plandb.=");";
	writeover(D_P.'data/bbscache/plandb.php',"<?php\r\n".$plandb."\r\n?>");
	rsort($plantime);
	$plantime=array_pop($plantime);
	$db->update("UPDATE pw_bbsinfo SET plantime='$plantime' WHERE id='1'");
}
function db_cv($array){
	if(is_array($array)){
		foreach($array as $key=>$value){
			$array[$key]=str_replace(array("\\","'"),array("\\\\","\'"),$value);
		}
	}
	return $array;	
}
?>