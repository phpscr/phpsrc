<?php
require_once('global.php');
require_once(R_P.'require/forum.php');
include_once(D_P."data/bbscache/forum_cache.php");
$groupid=='guest' && Showmsg('not_login');

if($action){
	!$fid && Showmsg('illegal_fid');	
	$forums = $db->get_one("SELECT name,forumadmin,fupadmin,allowreward FROM pw_forums WHERE fid='$fid' AND type!='category'");
	!$forums && Showmsg('data_error');
	if(!admincheck($forums['forumadmin'],$forums['fupadmin'],$windid)){
		Showmsg('not_forumadmin');
	}
	$first_admin = $db_adminset && strpos($forums['forumadmin'],",".$windid.",")===0 ? 1 : 0;
}else{
	$fiddb=array();
	$query=$db->query("SELECT fid,forumadmin,fupadmin FROM pw_forums WHERE cms=0 AND type!='category' AND (forumadmin!='' or fupadmin!='')");
	while($rt=$db->fetch_array($query)){
		if(admincheck($rt['forumadmin'],$rt['fupadmin'],$windid)){
			$fiddb[]=$rt['fid'];
		}
	}
	!$fiddb && Showmsg('not_forumadmin');
}

require_once(R_P.'require/header.php');
if(!$action){
	$forum_name = '';
	$fids=implode(',',$fiddb);
	$froumdb=array();
	$query=$db->query("SELECT * FROM pw_forums f LEFT JOIN pw_forumdata fd USING(fid) WHERE f.fid IN($fids)");
	while($rt=$db->fetch_array($query)){
		$forumdb[]=$rt;
	}
	$i=count($forumdb);
	if($i>4){
		$j_sum=4;
		$j_wid='25%';
	}else{
		$j_sum=$i;
		$j_wid=(100/$i).'%';
	}
	require_once(PrintEot('forumcp'));footer();
}elseif($action=='edit'){
	$forum_name = $forums['name'];
	!$type && $type='notice';
	if($type=='notice'){
		$annoucedb = array();
		$query     = $db->query("SELECT * FROM pw_announce WHERE fid='$fid' AND ffid='' ORDER BY fid,vieworder,startdate DESC");
		while($rt  = $db->fetch_array($query)){			
			$rt['name'] = "<a href='thread.php?fid=$rt[fid]'>".$forum[$rt['fid']]['name']."</a>";
			$rt['subject']   = substrs($rt['subject'],30);
			$rt['startdate'] = get_date($rt['startdate']);
			$annoucedb[] = $rt;
		}
		require_once(PrintEot('forumcp'));footer();
	}elseif($type=='add' || $type=='edit'){
		if(!$step){
			$js_path=geteditor();
			if($type=='edit'){
				@extract($db->get_one("SELECT * FROM pw_announce WHERE aid='$aid'"));
				HtmlConvert($subject);
				HtmlConvert($content);
				$atc_content = $content;
			}
			require_once(PrintEot('forumcp'));footer();
		}elseif($_POST['step']==3){
			require_once(R_P.'require/bbscode.php');
			require_once(R_P.'require/postfunc.php');

			!is_numeric($vieworder) && $vieworder=0;
			if (empty($newsubject) || empty($atc_content)){
				Showmsg('annouce_empty');
			}
			
			$newsubject  = Char_cv($newsubject);
			$atc_content = Char_cv($atc_content);
			$atc_content = trim(autourl($atc_content));

			if($type=="add"){
				$db->update("INSERT INTO pw_announce(fid,vieworder,author,startdate,subject,content) VALUES('$fid','$vieworder','".addslashes($windid)."','$timestamp','$newsubject','$atc_content')");
			}else{
				$db->update("UPDATE pw_announce SET vieworder='$vieworder',subject='$newsubject',content='$atc_content' WHERE aid='$aid' AND fid='$fid'");
			}
			updatecache_i($fid);
			refreshto("forumcp.php?action=edit&fid=$fid",'operate_success');
		}
	}elseif($type=='report'){
		(!is_numeric($page) || $page < 1) && $page=1;
		$limit="LIMIT ".($page-1)*$db_perpage.",$db_perpage";
		
		$rt=$db->get_one("SELECT COUNT(*) AS count FROM pw_report r LEFT JOIN pw_threads t ON t.tid=r.tid WHERE t.fid='$fid'");
		$sum=$rt['count'];
		$numofpage=ceil($sum/$db_perpage);
		$pages=numofpage($sum,$page,$numofpage,"forumcp.php?action=edit&type=report&fid=$fid&");

		$query=$db->query("SELECT r.*,m.username,t.fid FROM pw_report r LEFT JOIN pw_members m ON m.uid=r.uid LEFT JOIN pw_threads t ON t.tid=r.tid WHERE t.fid='$fid' ORDER BY id $limit");
		while($rt=$db->fetch_array($query)){
			$rt['fname']=$forum[$rt['fid']]['name'];
			$reportdb[]=$rt;
		}
		require_once(PrintEot('forumcp'));footer();
	}elseif($type=='f_type'){
		$rt=$db->get_one("SELECT f.t_type,fe.fid,fe.forumset FROM pw_forums f LEFT JOIN pw_forumsextra fe USING(fid) WHERE f.fid='$fid'");
		$forumset=unserialize($rt['forumset']);
		if(!$step){
			$forumset['addtpctype'] ? $addtpctype_Y='checked' : $addtpctype_N='checked';
			$t_typedb=explode("\t",$rt['t_type']);/*主题分类*/
			$t_typedb[0] = (int)$t_typedb[0];
			${'t_type_'.$t_typedb[0]}='checked';
			require_once(PrintEot('forumcp'));footer();
		}elseif($_POST['step']==3){
			Add_S($forumset);
			$t_type=implode("\t",$t_db)."\t";/*主题分类*/
			$t_type = str_replace('"','&quot;',$t_type);
			if($t_type!=$rt['t_type']){
				$db->update("UPDATE pw_forums SET t_type='$t_type' WHERE fid='$fid'");
			}
			if($addtpctype!=$forumset['addtpctype']){
				$forumset['addtpctype']=$addtpctype;
				$forumset=serialize($forumset);
				if($rt['fid']){
					$db->update("UPDATE pw_forumsextra SET forumset='$forumset' WHERE fid='$fid'");
				}else{
					$db->update("INSERT INTO pw_forumsextra (fid,forumset) VALUES ('$fid','$forumset')");
				}
			}
			refreshto("forumcp.php?action=edit&type=f_type&fid=$fid",'operate_success');
		}
	}elseif($type=='reward'){
		require_once(D_P.'data/bbscache/creditdb.php');
		list($db_moneyname,$db_moneyunit,$db_rvrcname,$db_rvrcunit,$db_creditname,$db_creditunit)=explode("\t",$db_credits);
		(!is_numeric($page) || $page < 1) && $page=1;
		$limit="LIMIT ".($page-1)*$db_perpage.",$db_perpage";
		$rewtime=$forums['allowreward'];
		$rewtime==0 && $rewtime=3;

		$sql=$url_a='';
		$_POST['starttime'] && $starttime=PwStrtoTime($starttime);
		$_POST['endtime']   && $endtime  =PwStrtoTime($endtime);
		if($username){
			$sql.=" AND author='$username'";
			$url_a.="username=".rawurlencode($username)."&";
		}
		if($starttime){
			$sql.=" AND postdate>'$starttime'";
			$url_a.="starttime=$starttime&";
		}
		if($endtime){
			$sql.=" AND postdate<'$endtime'";
			$url_a.="endtime=$endtime&";
		}
		$sql .= " AND LEFT(rewardinfo,1)='1'";
		
		$rt=$db->get_one("SELECT COUNT(*) AS count FROM pw_threads WHERE fid='$fid' AND special='3' AND $timestamp-postdate>'$rewtime'*86400 $sql");
		$sum=$rt['count'];
		$numofpage=ceil($sum/$db_perpage);
		$pages=numofpage($sum,$page,$numofpage,"forumcp.php?action=edit&type=reward&fid=$fid&$url_a");

		$threaddb=array();
		$query=$db->query("SELECT tid,fid,subject,author,authorid,rewardinfo,postdate FROM pw_threads WHERE fid='$fid' AND special='3' AND $timestamp-postdate>'$rewtime'*86400 $sql ORDER BY postdate $limit");
		while($rt=$db->fetch_array($query)){
			$rt['postdate']=get_date($rt['postdate'],'Y-m-d');
			$reward=explode("\n",$rt['rewardinfo']);
			list($rw_b_name,$rw_b_val,$rw_a_name,$rw_a_val)=explode('|',$reward[1]);
			$rw_b_name = is_numeric($rw_b_name) ? $_CREDITDB[$rw_b_name][0] : ${'db_'.$rw_b_name.'name'};
			$rw_a_name = is_numeric($rw_a_name) ? $_CREDITDB[$rw_a_name][0] : ${'db_'.$rw_a_name.'name'};
			$rt['binfo']=$rw_b_val."&nbsp;".$rw_b_name;
			$rt['ainfo']=$rw_a_val."&nbsp;".$rw_a_name;
			$threaddb[]=$rt;
		}
		require_once(PrintEot('forumcp'));footer();
	}elseif($type=='thread'){
		(!is_numeric($page) || $page < 1) && $page=1;
		$limit="LIMIT ".($page-1)*$db_perpage.",$db_perpage";
		$sql=$url_a='';
		$_POST['starttime'] && $starttime=PwStrtoTime($starttime);
		$_POST['endtime']   && $endtime  =PwStrtoTime($endtime);
		if($username){
			$sql.=" AND author='$username'";
			$url_a.="username=".rawurlencode($username)."&";
		}
		if($starttime){
			$sql.=" AND postdate>'$starttime'";
			$url_a.="starttime=$starttime&";
		}
		if($endtime){
			$sql.=" AND postdate<'$endtime'";
			$url_a.="endtime=$endtime&";
		}
		if($t_type){
			switch ($t_type){
				case 'digest':
					$sql.=" AND digest>'0'";
					break;
				case 'active':
					$sql.=" AND special='2'";
					break;
				case 'reward':
					$sql.=" AND special='3'";
					break;
				case 'sale':
					$sql.=" AND special='4'";
					break;
				default :
					$sql.=" AND digest>'0'";
			}
			$url_a.="t_type=$t_type&";
		}

		$rt=$db->get_one("SELECT COUNT(*) AS sum FROM pw_threads WHERE fid='$fid' $sql");		$pages=numofpage($rt['sum'],$page,ceil($rt['sum']/$db_perpage),"forumcp.php?action=edit&type=thread&fid=$fid&$url_a");

		$query=$db->query("SELECT tid,subject,author,authorid,postdate,titlefont,topped,digest FROM pw_threads WHERE fid='$fid' $sql ORDER BY topped DESC,lastpost DESC $limit");

		$threaddb=array();
		while($rt=$db->fetch_array($query)){
			$rt['subject']=substrs($rt['subject'],35);
			if($rt['titlefont']){
				$titledetail=explode("~",$rt['titlefont']);
				if($titledetail[0])$rt['subject']="<font color=\"$titledetail[0]\">$rt[subject]</font>";
				if($titledetail[1])$rt['subject']="<b>$rt[subject]</b>";
				if($titledetail[2])$rt['subject']="<i>$rt[subject]</i>";
				if($titledetail[3])$rt['subject']="<u>$rt[subject]</u>";
			}
			$rt['postdate']=get_date($rt['postdate']);
			$threaddb[]=$rt;
		}

		require_once(PrintEot('forumcp'));footer();
	}elseif($type=='adminset'){
		!$first_admin && Showmsg('undefined_action');

		$admin_a = explode(',',substr($forums['forumadmin'],1,-1));
		$firstadmin = $admin_a[0];
		$firstadmin != $windid && Showmsg('undefined_action');

		if(!$_POST['step']){
			$s_admin = substr(str_replace(",$firstadmin,","",$forums['forumadmin']),0,-1);
			require_once(PrintEot('forumcp'));footer();
		}else{
			$errorname = '';
			if($forums['forumadmin'] != ",".$windid.",$forumadmin,"){
				$newadmin = array('0'=>$windid);
				$newadmin_a = explode(",",$forumadmin);
				$newadmin_a = array_unique($newadmin_a);
				foreach($newadmin_a as $aid=>$value){
					$value = trim($value);
					if($value && !in_array($value,$newadmin)){
						$mb=$db->get_one("SELECT uid FROM pw_members WHERE username='$value'");
						if($mb){
							$newadmin[] = $value;
						}else{
							$errorname .= ','.$value;
						}
					}
				}
				$newadmin=implode(',',$newadmin);
				$db->update("UPDATE pw_forums SET forumadmin=',$newadmin,' WHERE fid='$fid'");
				updatecache_fd();
				updateadmin();
			}
			if($errorname){
				$errorname = substr($errorname,1);
				Showmsg('user_not_exists');
			}else{
				refreshto("forumcp.php?action=edit&type=$type&fid=$fid",'operate_success');
			}
		}
	}
}elseif($action=='del'){
	$selids='';
	foreach($selid as $key=>$value){
		is_numeric($value) && $selids .= $selids ? ','.$value : $value;
	}
	!$selids && Showmsg('id_error');
		
	if($type=='notice'){
		$db->update("DELETE FROM pw_announce WHERE fid='$fid' AND aid IN($selids)");
		updatecache_i($fid);
		refreshto("forumcp.php?action=edit&fid=$fid",'operate_success');
	}elseif($type=='report'){
		$db->update("DELETE FROM pw_report WHERE id IN ($selids)");
		refreshto("forumcp.php?action=edit&type=report&fid=$fid",'operate_success');
	}
}

function ieconvert($msg){
	$msg = str_replace("\t","",$msg);
	$msg = str_replace("\r","",$msg);
	$msg = str_replace("   "," &nbsp; ",$msg);#编辑时比较有效
	return $msg;
}

function updatecache_i($fid){
	global $db,$db_windpost;
	@include(D_P.'data/bbscache/forum_cache.php');
	require_once(R_P.'require/bbscode.php');
	$notice=$db->get_one("SELECT * FROM pw_announce WHERE fid='$fid' AND ffid='' ORDER BY vieworder,startdate DESC LIMIT 1");
	if($notice){
		$notice['content']=convert($notice['content'],$db_windpost,2);
		$notice=db_cv($notice);
		$db->pw_update(
			"SELECT aid FROM pw_announce WHERE ffid='$fid'",
			"UPDATE pw_announce SET author='$notice[author]',startdate='$notice[startdate]',subject='$notice[subject]',content='$notice[content]' WHERE ffid='$fid'",
			"INSERT INTO pw_announce SET fid='$fid',author='$notice[author]',startdate='$notice[startdate]',subject='$notice[subject]',ffid='$fid',content='$notice[content]'"
		);
	}else{
		$db->update("DELETE FROM pw_announce WHERE ffid='$fid' AND fid='$fid'");
	}
} 

function db_cv($array){
	if(is_array($array)){
		foreach($array as $key=>$value){
			$array[$key]=str_replace(array("\\","'"),array("\\\\","\'"),$value);
		}
	}
	return $array;	
}

function HtmlConvert(&$array){
	if(is_array($array)){
		foreach($array as $key => $value){
			if(!is_array($value)){
				$array[$key]=htmlspecialchars($value);
			}else{
				HtmlConvert($array[$key]);
			}
		}
	} else{
		$array=htmlspecialchars($array);
	}
}

function updateadmin(){
	global $db,$fid;

	$f_admin=array();
	$query=$db->query("SELECT forumadmin FROM pw_forums");
	while($forum=$db->fetch_array($query)){
		$adminarray=explode(",",addslashes($forum['forumadmin']));
		foreach($adminarray as $key=>$value){
			$value=trim($value);
			if($value){
				$f_admin[]=$value;
			}
		}
	}
	$f_admin=array_unique($f_admin);

	$query=$db->query("SELECT uid,username,groupid,groups FROM pw_members WHERE groupid=5 OR groups LIKE '%,5,%'");
	while($rt=$db->fetch_array($query)){
		if(!in_array($rt['username'],$f_admin)){
			if($rt['groupid']=='5'){
				$db->update("UPDATE pw_members SET groupid='-1' WHERE uid='$rt[uid]'");
			}else{
				$groups=str_replace(',5,',',',$rt['groups']);
				$groups==',' && $groups='';
				$db->update("UPDATE pw_members SET groups='$groups' WHERE uid='$rt[uid]'");
			}
		}
	}

	foreach($f_admin as $key => $value){
		$rt=$db->get_one("SELECT uid,username,groupid,groups FROM pw_members WHERE username='$value'");
		if($rt['uid']){
			if($rt['groupid']=='-1'){
				$db->update("UPDATE pw_members SET groupid='5' WHERE uid='$rt[uid]'");
			} elseif($rt['groupid']!='5' && strpos($rt['groups'],',5,')===false){
				$groups=$rt['groups'] ? $rt['groups'].'5,' : ",5,";
				$db->update("UPDATE pw_members SET groups='$groups' WHERE uid='$rt[uid]'");
			}
		}
	}
}
function updatecache_fd(){
	global $db;
	$db->update("UPDATE pw_forums SET childid='0',fupadmin=''");
	$query=$db->query("SELECT fid,forumadmin FROM pw_forums WHERE type='category' ORDER BY vieworder");
	while($cate=db_cv($db->fetch_array($query))){
		$query2=$db->query("SELECT fid,forumadmin FROM pw_forums WHERE type='forum' AND fup='$cate[fid]'");
		if($db->num_rows($query2)){
			$havechild[]=$cate['fid'];
			while($forum=db_cv($db->fetch_array($query2))){
				$fupadmin = trim($cate['forumadmin']);
				if($fupadmin){
					$db->update("UPDATE pw_forums SET fupadmin='$fupadmin' WHERE fid='$forum[fid]'");
				}
				if(trim($forum['forumadmin'])){
					$fupadmin .= $fupadmin ? substr($forum['forumadmin'],1) : $forum['forumadmin']; //is
				}
				$query3=$db->query("SELECT fid,forumadmin FROM pw_forums WHERE type='sub' AND fup='$forum[fid]'");
				if($db->num_rows($query3)){
					$havechild[]=$forum['fid'];
					while($sub1=db_cv($db->fetch_array($query3))){
						$fupadmin1=$fupadmin;
						if($fupadmin1){
							$db->update("UPDATE pw_forums SET fupadmin='$fupadmin1' WHERE fid='$sub1[fid]'");
						}
						if(trim($sub1['forumadmin'])){
							$fupadmin1 .= $fupadmin1 ? substr($sub1['forumadmin'],1) : $sub1['forumadmin'];
						}
						$query4=$db->query("SELECT fid,forumadmin FROM pw_forums WHERE type='sub' AND fup='$sub1[fid]'");
						if($db->num_rows($query4)){
							$havechild[]=$sub1['fid'];
							while($sub2=db_cv($db->fetch_array($query4))){
								$fupadmin2=$fupadmin1;
								if($fupadmin2){
									$db->update("UPDATE pw_forums SET fupadmin='$fupadmin2' WHERE fid='$sub2[fid]'");
								}
							}
						}
					}
				}
			}
		}
	}
	if($havechild){
		$havechilds=implode(',',$havechild);
		$db->update("UPDATE pw_forums SET childid='1' WHERE fid IN($havechilds)");
	}
}
?>