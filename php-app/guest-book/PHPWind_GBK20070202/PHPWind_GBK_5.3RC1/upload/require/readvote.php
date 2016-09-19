<?php
!function_exists('readover') && exit('Forbidden');

@extract($db->get_one("SELECT voteopts,state,modifiable,previewable,timelimit FROM pw_polls WHERE tid='$tid'"));
$special    = 'vote';
$vote_close =($state || ($timelimit && $timestamp-$read['postdate']>$timelimit*86400)) ? 1 : 0;
$tpc_date   = get_date($read['postdate']);
$tpc_endtime= $timelimit ? get_date($read['postdate']+$timelimit*86400) : 0;
vote($voteopts);

function vote($voteopts){
	global $multi,$votetype,$ifview,$votedb,$action,$votesum,$havevote,$state,$viewvoter,$fid,$tid,$windid,$groupid,$admincheck,$_G,$onlineip,$modifiable,$previewable,$vote_close;
	$votearray = unserialize($voteopts);
	!$modifiable && $action=='modify' && Showmsg('vote_not_modify');
	$votetype = $votearray['multiple'][0] ? 'checkbox' : 'radio';
	$havevote = $print = $state = "";
	$votesum  = 0;
	$votedb   = $vt_name = $vt_num = $voteid = $voter = array();
	!$ifview && $ifview = 'yes';
	$ifview = $viewvoter == 'yes' ? 'no' : 'yes';
	$allvoter = array();
	foreach($votearray['options'] as $key => $option){
		$voterr='';
		if(is_array($option[2])){
			foreach($option[2] as $k => $val){
				$viewvoter == 'yes' && $voterr .= "<span class=bold>$val</span>".' ';
				$allvoter[] = $val;
			}
		}
		if($viewvoter == 'yes' && !$admincheck && $groupid!=3 && !$_G['viewvote']){
			require_once(R_P.'require/header.php');
			Showmsg('readvote_noright');
		}
		$voter[$key]   = $voterr;
		$vt_name[$key] = $option[0];
		$vt_num[$key]  = $option[1];
		$votesum      += $option[1];
		if(@in_array(($windid ? $windid : $onlineip),$option[2])){
			$havevote='havevote';
		}
	}
	foreach($vt_name as $key => $value){
		if($previewable==0 || $havevote || $vote_close){
			$vote['width'] = floor(500*$vt_num[$key]/($votesum+1));
			$vote['num']   = $vt_num[$key];			
		}else{
			$vote['width'] = 0;
			$vote['num']   = '*';
		}		
		$vote['name']  = stripslashes($value);		
		$vote['voter'] = $voter[$key];
		$votedb[$key]  = $vote;
	}
	$votesum=count(array_unique($allvoter));
	if ($groupid!='guest'){
		$multi=$votearray['multiple'][0] ?  $votearray['multiple'][1] :0;
	}
}
?>