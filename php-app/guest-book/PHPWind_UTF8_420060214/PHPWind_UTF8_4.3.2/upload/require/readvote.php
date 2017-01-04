<?php
!function_exists('readover') && exit('Forbidden');
function vote($voteopts)
{
	global $multi,$pollid,$votetype,$ifview,$votedb,$action,$votesum,$havevote,$state,$viewvoter,$fid,$tid,$windid,$groupid,$admincheck,$_G,$onlineip;
	$votearray = unserialize($voteopts);
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
		$vote['width'] = floor(500*$vt_num[$key]/($votesum+1));
		$vote['name']  = stripslashes($value);
		$vote['num']   = $vt_num[$key];
		$vote['voter'] = $voter[$key];
		$votedb[$key]      = $vote;
	}
	$votesum=count(array_unique($allvoter));
	if ($groupid!='guest'){
		$multi=$votearray['multiple'][0] ?  $votearray['multiple'][1] :0;
	}
}
?>