<?php
!function_exists('readover') && exit('Forbidden');

$reward    = explode("\n",$read['rewardinfo']);
$rewardtype= $reward['0'];
list($rw_b_name,$rw_b_val,$rw_a_name,$rw_a_val)=explode('|',$reward['1']);
$endinfo   = strpos($reward['2'],'|')!==false ? explode('|',$reward['2']) : $reward['2'];
$rw_b_name = is_numeric($rw_b_name) ? $_CREDITDB[$rw_b_name][0] : ${'db_'.$rw_b_name.'name'};
$rw_a_name = is_numeric($rw_a_name) ? $_CREDITDB[$rw_a_name][0] : ${'db_'.$rw_a_name.'name'};

if($page==1 && $rewardtype=='1' && $rw_a_val){
	@extract($db->get_one("SELECT COUNT(*) as reward_num FROM $pw_posts WHERE tid='$tid' AND authorid!='$authorid' AND ifreward='1'"));
	$left_num = $reward_num<$rw_a_val ? $rw_a_val-$reward_num : 0;
}
/*** ÐüÉÍ ***/

function Getrewhtml($lou,$ifreward,$pid){
	global $rewardtype,$rw_b_name,$rw_b_val,$rw_a_name,$rw_a_val,$groupid,$admincheck,$authorid,$winduid,$lang,$tid,$endinfo,$left_num;
	require_once GetLang('bbscode');

	$html = "<div class=\"tips\" style=\"width:auto;\">";
	if($lou==0){
		if($rewardtype=='1'){
			$html .= "<font color=\"red\">$lang[rewarding]</font>";
			$html .= "<div align=\"center\">$lang[reward_bestanswer]:$rw_b_val&nbsp;$rw_b_name";
			if($rw_a_val){
				$html .= "&nbsp;&nbsp;$lang[reward_help]:$rw_a_val&nbsp;$rw_a_name</div>";
				$html .= "<div align=\"center\">$lang[reward_hlp]: $left_num $rw_a_name";
			}
			$html .= "</div>";
			if($groupid=='3' || $admincheck){
				$html .= "<div align=\"center\"><a href=\"job.php?action=endreward&tid=$tid\">$lang[reward_cancle]</a>&nbsp;</div>";
			}elseif($authorid==$winduid){
				$html .= "<div align=\"center\"><a href=\"job.php?action=rewardmsg&tid=$tid\" title=\"$lang[reward_title]\" onClick=\"javascript:if(confirm('$lang[reward_msgtoadmin]')){return true;}else{return false;}\">$lang[reward_toadmin]</a>&nbsp;</div>";
			}
		}elseif($rewardtype=='2'){
			$html .= "<font color=\"red\">$lang[reward_finished]</font><div align=\"center\">$lang[reward_bestanswer]:$rw_b_val&nbsp;$rw_b_name";
			if($rw_a_val){
				$html .= "&nbsp;&nbsp;$lang[reward_help]:$rw_a_val&nbsp;$rw_a_name";
			}
			$html .= "</div>";
			if(is_array($endinfo)){
				$html .= "<div align=\"center\">$lang[reward_author]£º$endinfo[0]</div>";
			}else{
				$html .= "<div align=\"center\">$endinfo&nbsp;&nbsp;</div>";
			}
		}
	} else{
		if($rewardtype=='2' && $ifreward>1){
			$html .= "<font color=\"red\">$lang[reward_best_get]</font>£º(+$rw_b_val)&nbsp;$rw_b_name";
		}elseif(($rewardtype=='1' && $authorid==$winduid || $rewardtype=='2') && $ifreward=='1'){
			$html .= "<font color=\"red\">$lang[reward_help_get]</font>£º(+1)&nbsp;$rw_a_name";
		}
		if($authorid==$winduid && $rewardtype=='1'){
			$html .= "&nbsp;<a href=\"job.php?action=reward&tid=$tid&pid=$pid\">$lang[reward_manager]</a>";
		}
	}
	$html .= "</div><div class=\"c\"></div>";
	return $html;
}
?>