<?php
!function_exists('adminmsg') && exit('Forbidden');

$rt=$db->get_one("SELECT * FROM pw_plan WHERE filename='team'");
!$rt && adminmsg('operate_error');
if(!$action){
	$filename=$rt['filename'];
	$config=unserialize($rt['config']);
	$pcredit=$config['credit'];	
	${'st_'.$config['credittype']}='selected';
	ifcheck($rt['ifopen'],'open');
	$gids=$num=0;
	$usergroup=$usercredit="";
	$query=$db->query("SELECT gid,grouptitle FROM pw_usergroups WHERE gptype='system' AND gid NOT IN(6,7)");
	while($rt=$db->fetch_array($query)){	
		$checked = '';
		if(strpos(','.$config['groups'].',',','.$rt['gid'].',') !== false){
			$checked = 'checked';
		}
		$num++;
		$htm_tr = $num%4 == 0 ?  '</tr><tr>' : '';
		$usergroup .="<td width=\"20%\"><input type=\"checkbox\" name=\"groups[]\" value=\"$rt[gid]\" $checked>$rt[grouptitle]</td>$htm_tr";
		$usercredit.="<tr class=\"b\"><td width=\"18%\">".$rt['grouptitle']."</td><td><input type=\"text\" name=\"credit[$rt[gid]]\" value=\"".$pcredit[$rt['gid']]."\" size=\"5\"></td></tr>\r\n";
	}
	include_once(D_P.'data/bbscache/creditdb.php');
	include PrintHack('admin');exit;
}elseif($_POST['action']=='unsubmit'){	
	if (is_array($groups)){
		foreach($credit as $key=>$val){
			if(!in_array($key,$groups)){
				unset($credit[$key]);
			}
		}
		$config['credit'] = $credit;
		$config['groups'] = implode(',',$groups);
	} else {
		$config['credit'] = '';
		$config['groups'] = '';
	}
	$config=serialize($config);
	$rt['ifopen']=$ifopen;
	$nexttime=nexttime($rt);
	$db->update("UPDATE pw_plan SET nexttime='$nexttime',ifopen='$ifopen',config='$config' WHERE filename='team'");
	if($nexttime==0 && $ifopen=='1'){
		adminmsg('please_settime',"$admin_file?adminjob=plantodo&action=planset&id=$rt[id]");
	}else{
		updatecache_plan();
		adminmsg('operate_success');
	}
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