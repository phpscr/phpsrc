<?php
!function_exists('adminmsg') && exit('Forbidden');
require(R_P.'require/forum.php');
require_once GetLang('all');

if($a_type=='adminlog'){
	$basename="$admin_file?adminjob=record&a_type=adminlog";
	$bbsrecordfile=D_P."data/bbscache/admin_record.php";
}elseif($a_type=='forumlog'){
	$basename="$admin_file?adminjob=record&a_type=forumlog";
	$bbsrecordfile=D_P."data/bbscache/log_forum.php";
}elseif($a_type=='creditlog'){
	$basename="$admin_file?adminjob=record&a_type=creditlog";
	$bbsrecordfile=D_P."data/bbscache/credit_log.php";
} else{
	adminmsg('undefine_action');
}
if(file_exists($bbsrecordfile)){
	$bbslogfiledata=readlog($bbsrecordfile);
} else{
	$bbslogfiledata=array();
}
$bbslogfiledata=array_reverse($bbslogfiledata);
$count=count($bbslogfiledata);
if($del=='Y'){
	PostCheck($verify);
	if ($admin_gid == 3){
		if($count>100){
			$output=array_slice($bbslogfiledata,0,100);
			$output=array_reverse($output);
			$output=implode("",$output);
			writeover($bbsrecordfile,$output);
			adminmsg('log_del');
		}else{
			adminmsg('log_min');
		}
	} else {
		adminmsg('record_aminonly');
	}
}
$db_perpage=50;
(!is_numeric($page) || $page < 1) && $page=1;
if ($count%$db_perpage==0){
	$numofpage=floor($count/$db_perpage);
}else{
	$numofpage=floor($count/$db_perpage)+1;
}
if ($page>$numofpage){
	$page=$numofpage;
}
$pagemin=min(($page-1)*$db_perpage , $count-1);  
$pagemax=min($pagemin+$db_perpage-1, $count-1);
if($a_type=='adminlog'){
	if($action=='search'){
		if(!$keyword){
			adminmsg('noenough_condition');
		}
		$num=0;
		$start=($page-1)*$db_perpage;
		foreach($bbslogfiledata as $value){
			if(strpos($value,$keyword)!==false){
				if($num >= $start && $num < $start+$db_perpage){
					$detail=explode("|",$value);
					$winddate=get_date($detail[5]);
					$detail[2] && !If_manager && $detail[2]=substr_replace($detail[2],'***',1,-1);
					$detail[6]=htmlspecialchars($detail[6]);
					$adlogfor.="
<tr class=b align='center'>
	<td><a href='$admin_file?adminjob=setuser&action=search&schname=$detail[1]&schname_s=1'>$detail[1]</a></td>
	<td>$detail[2]</td>
	<td class='smalltxt'>$detail[3]</td>
	<td>$detail[4]</td>
	<td class='smalltxt'>$winddate</td>
	<td class='smalltxt'>$detail[6]</td>
</tr>";
				}
				$num++;
			}
		}
		$numofpage=ceil($num/$db_perpage);
		$pages=numofpage($num,$page,$numofpage,"$admin_file?adminjob=record&a_type=adminlog&action=search&keyword=".rawurlencode($keyword)."&");
	} else{
		$pages=numofpage($count,$page,$numofpage,"$admin_file?adminjob=record&a_type=adminlog&");
		for($i=$pagemin; $i<=$pagemax; $i++){
			$detail=explode("|",$bbslogfiledata[$i]);
			$winddate=get_date($detail[5]);
			$detail[2] && !If_manager && $detail[2]=substr_replace($detail[2],'***',1,-1);
			$detail[6]=htmlspecialchars($detail[6]);
			$adlogfor.="
<tr class=b align='center'>
	<td><a href='$admin_file?adminjob=setuser&action=search&schname=$detail[1]&schname_s=1'>$detail[1]</a></td>
	<td>$detail[2]</td>
	<td class='smalltxt'>$detail[3]</td>
	<td>$detail[4]</td>
	<td class='smalltxt'>$winddate</td>
	<td class='smalltxt'>$detail[6]</td>
</tr>";
		}
	}
	include PrintEot('record');exit;
}elseif($a_type=='forumlog'){
	include(D_P.'data/bbscache/forum_cache.php');
	
	if($action=='search'){
		if(!$keyword){
			adminmsg('noenough_condition');
		}
		$num=0;
		$start=($page-1)*$db_perpage;
		foreach($bbslogfiledata as $value){
			if(strpos($value,$keyword)!==false){
				if($num >= $start && $num < $start+$db_perpage){
					$detail=explode("|",$value);
					$winddate=get_date($detail[9]);
					$where_log=$forum[$detail[2]]['name'];
					$adlogfor.="<tr class=b align='center'><td>$detail[10]</td><td>$detail[5]</td><td><a href=thread.php?fid=$detail[2]>$where_log</a></td><td class='smalltxt'  colspan=3><font color=green>$detail[1]</font>  <font color=green>$lang[reason]</font>$detail[6] <font color=green>$lang[operate]</font>$lang[record_rvrc] $detail[7] $lang[record_money] $detail[8]</td><td>$detail[11]</td><td>$winddate</td></tr>";
				}
				$num++;
			}
		}
		$numofpage=ceil($num/$db_perpage);
		$pages=numofpage($num,$page,$numofpage,"$admin_file?adminjob=record&a_type=forumlog&action=search&keyword=".rawurlencode($keyword)."&");
	} else{
		$pages=numofpage($count,$page,$numofpage,"$admin_file?adminjob=record&a_type=forumlog&");
		if($bbslogfiledata){
			for($i=$pagemin; $i<=$pagemax; $i++){
				$detail=explode("|",$bbslogfiledata[$i]);
				$winddate=get_date($detail[9]);
				$where_log=$forum[$detail[2]]['name'];
				$adlogfor.="<tr class=b align='center'><td>$detail[10]</td><td>$detail[5]</td><td><a href=thread.php?fid=$detail[2]>$where_log</a></td><td class='smalltxt'  colspan=3><font color=green>$detail[1]</font>  <font color=green>$lang[reason]</font>$detail[6] <font color=green>$lang[operate]</font>$lang[record_rvrc] $detail[7] $lang[record_money] $detail[8]</td><td>$detail[11]</td><td class='smalltxt'>$winddate</td></tr>";
			}
		} else{
			$adlogfor='';
		}
	}
	include PrintEot('record');exit;
}elseif($a_type=='creditlog'){
	require_once GetLang('all');
	$creditdb=array(); $credits=array('rvrc'=>$lang['record_rvrc'],'money'=>$lang['record_money'],'credit'=>$lang['record_credit']);
	include(D_P."data/bbscache/creditdb.php");
	foreach($_CREDITDB as $key=>$value){
		$credits[$key]=$value[0];
	}
	unset($credit);
	
	if($action=='search'){
		if(!$keyword){
			adminmsg('noenough_condition');
		}
		$num=0;
		$start=($page-1)*$db_perpage;
		foreach($bbslogfiledata as $value){
			if(strpos($value,$keyword)!==false){
				if($num >= $start && $num < $start+$db_perpage){
					$credit=explode("|",$value);			
					$credit['time']=get_date($credit[7]);
					$credit['name']=$credits[$credit[2]];
					$creditdb[]=$credit;
				}
				$num++;
			}
		}
		$numofpage=ceil($num/$db_perpage);
		$pages=numofpage($num,$page,$numofpage,"$admin_file?adminjob=record&a_type=creditlog&action=search&keyword=".rawurlencode($keyword)."&");
	} else{
		$pages=numofpage($count,$page,$numofpage,"$admin_file?adminjob=record&a_type=creditlog&");
		for($i=$pagemin; $i<=$pagemax; $i++){
			if($bbslogfiledata[$i]){
				$credit=explode("|",$bbslogfiledata[$i]);
				$credit['time']=get_date($credit[7]);
				$credit['name']=$credits[$credit[2]];
				$creditdb[]=$credit;
			}
		}
	}
	include PrintEot('record');exit;
}
?>