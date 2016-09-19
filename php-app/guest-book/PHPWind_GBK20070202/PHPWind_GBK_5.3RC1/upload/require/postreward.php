<?php
!function_exists('readover') && exit('Forbidden');

/*******
* ÐüÉÍ *
*******/
$bonus['best']   = (int) $bonus['best'];
$bonus['active'] = (int) $bonus['active'];
$bonus['best']   < $rw_b_val && Showmsg('credit_limit');
$bonus['active'] < $rw_a_val && Showmsg('credit_limit');
if(!is_numeric($ctype['best']) && !in_array($ctype['best'],array('rvrc','money','credit')) || !is_numeric($ctype['active']) && !in_array($ctype['active'],array('rvrc','money','credit'))){
	Showmsg('reward_credit_error');
}
if($ctype['best']==$ctype['active']){
	$total_c=$bonus['best']*2+$bonus['active'];
	if(!is_numeric($ctype['best'])){
		if($ctype['best']=='rvrc'){
			$userrvrc<$total_c && Showmsg('reward_credit_limit');
			$reduce=$total_c*10;
		}else{
			$winddb[$ctype['best']]<$total_c && Showmsg('reward_credit_limit');
			$reduce=$total_c;
		}
		$db->update("UPDATE pw_memberdata SET $ctype[best]=$ctype[best]-'$reduce' WHERE uid='$winduid'");
	}else{
		require_once(R_P.'require/credit.php');
		$creditdb = GetCredit($winduid);
		$creditdb[$ctype['best']][1]<$total_c && Showmsg('reward_credit_limit');
		$db->update("UPDATE pw_membercredit SET value=value-'$total_c' WHERE uid='$winduid' AND cid='$ctype[best]'");
	}
} else{
	$sql_0='';
	$sql_1=array();
	foreach($ctype as $key=>$val){
		$i = $key=='best' ? 2 : 1;
		if($bonus[$key]<1)continue;
		if(!is_numeric($val)){
			if($val=='rvrc'){
				$userrvrc<$bonus[$key]*$i && Showmsg('reward_credit_limit');
				$reduce=$bonus[$key]*$i*10;
			}else{
				$winddb[$val]<$bonus[$key]*$i && Showmsg('reward_credit_limit');
				$reduce=$bonus[$key]*$i;
			}
			$sql_0 .= $sql_0 ? ",".$val."=".$val."-'".$reduce."'" : $val."=".$val."-'".$reduce."'";
		}else{
			require_once(R_P.'require/credit.php');
			$creditdb = GetCredit($winduid);
			$creditdb[$val][1]<$bonus[$key]*$i && Showmsg('reward_credit_limit');
			$reduce=$bonus[$key]*$i;
			$sql_1[$val]=$reduce;				
		}
	}
	$sql_0 && $db->update("UPDATE pw_memberdata SET $sql_0 WHERE uid='$winduid'");
	if($sql_1){
		foreach($sql_1 as $cid=>$value){
			$db->update("UPDATE pw_membercredit SET value=value-'$value' WHERE uid='$winduid' AND cid='$cid'");
		}
	}
}
$special=3;
$rewardinfo = "1\n".$ctype['best'].'|'.$bonus['best'];
$bonus['active']>0 && $rewardinfo.='|'.$ctype['active'].'|'.$bonus['active'];
/*******
* ÐüÉÍ *
*******/
?>