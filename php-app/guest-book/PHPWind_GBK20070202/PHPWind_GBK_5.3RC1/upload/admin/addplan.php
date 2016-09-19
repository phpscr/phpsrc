<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=addplan";

if(!$action){
	$month=makeoption(1,31);
	$day=makeoption(0,23);
	$hour=makeoption(0,59);
	include PrintEot('addplan');exit;
}elseif($_POST['action']=='unsubmit'){
	!$title && adminmsg('operate_error');

	if(is_numeric($month)){
		$month = $month<1 ? 1 : ($month>31 ? 31 : $month);
		$week='*';
	}elseif(is_numeric($week)){
		$week = $week<1 ? 1 : ($week>7 ? 7 : $week);
		$month='*';
	}else{
		$month=$week='*';
	}
	if(is_numeric($day)){
		$day = $day<0 ? 0 : ($day>23 ? 23 : $day);
	}else{
		$day='*';
	}
	if(is_array($hours)){
		$hours=array_unique($hours);
		$hour_w='';
		foreach($hours as $key=>$hour){
			is_numeric($hour) && $hour_t = $hour<0 ? 0 : ($hour>59 ? 59 : $hour);
			$hour_w .= $hour_w ? ','.$hour_t : $hour_t;
		}
		!$hour_w && $hour_w='*'; 
	}else{
		$hour_w='*';
	}
	if($month=='*' && $week=='*' && $day=='*' && $hour_w=='*' && $ifopen==1){
		adminmsg('time_error');
	}
	$title = Char_cv($title);
	$plan=array(
		'month'=>$month,
		'week'=>$week,
		'day'=>$day,
		'hour'=>$hour_w,
		'usetime'=>'0',
		'ifopen'=>$ifopen,
		);
	$nexttime=nexttime($plan);
	if(strpos($filename,'..')!==false)adminmsg("undefined_action");
	$db->update("INSERT INTO pw_plan (id,subject,month,week,day,hour,nexttime,ifsave,ifopen,filename) VALUES('','$title','$month','$week','$day','$hour_w','$nexttime','0','$ifopen','$filename')");
	updatecache_plan();
	adminmsg("operate_success");
}

function makeoption($start,$end){
	$option="<option value=\"*\">*</option>";
	for($i=$start;$i<=$end;$i++){
		$option.="<option value=\"$i\">$i</option>";
	}
	return $option;
}
function nexttime($plan){
	if($plan['ifopen']==0) return 0;
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
	return $timestamp+86400;
}
?>