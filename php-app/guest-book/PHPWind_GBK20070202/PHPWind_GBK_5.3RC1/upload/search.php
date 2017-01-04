<?php
$wind_in = 'sc';
require_once('global.php');
@set_time_limit(0);
$gp_allowsearch == 0 && Showmsg('search_group_right');
list($db_opensch,$db_schstart,$db_schend) = explode("\t",$db_opensch);
if($db_opensch==1 && $groupid != 3 && $groupid != 4){
	if($db_schstart < $db_schend && ($t['hours'] < $db_schstart || $t['hours'] >= $db_schend)){
		Showmsg('search_opensch');
	}elseif($db_schstart > $db_schend && ($t['hours'] < $db_schstart && $t['hours'] >= $db_schend)){
		Showmsg('search_opensch');
	}
}

include_once(D_P.'data/bbscache/forum_cache.php');
include_once(D_P.'data/bbscache/forumcache.php');

$forumadd = '';
$fidout = '-999';
$query    = $db->query("SELECT fid,name,allowvisit,password,f_type FROM pw_forums WHERE type<>'category'");
while($forums = $db->fetch_array($query)){
	if ($forums['f_type'] == 'hidden' && strpos($forums['allowvisit'],','.$groupid.',') !== false){
		$forumadd.="<option value='$forums[fid]'> &nbsp;|- $forums[name]</option>";
	} elseif ($forums['password'] || ($forums['allowvisit'] && strpos($forums['allowvisit'],','.$groupid.',') === false)){
		$forumcache = preg_replace("/\<option value='$forums[fid]'\>(.+?)\<\/option\>\\n/is",'',$forumcache);
		$fidout .= ','.$forums['fid'];
	}
}
$db->free_result($query);

unset($forums);
$keyword = Char_cv($keyword);

require_once(R_P.'require/header.php');

if ($newatc == 1 || is_numeric($authorid) || $digest == 1){
	$step = 2;
}
if(!$step){
	list($f,$db_searchinfo)=explode("\t",readover(D_P.'data/bbscache/info.txt'));
	$disable = $gp_allowsearch == 1 ? 'disabled' : '';
	if($gp_allowsearch==2 && $db_plist){
		$p_table="<option value=\"0\">post</option>";
		$p_list=explode(',',$db_plist);
		foreach($p_list as $key=>$val){
			$p_table .= "<option value=\"$val\">post$val</option>";
		}
	}
	require_once PrintEot('search');
	footer();
}else{
	$_POST && empty($keyword) && empty($pwuser) && $sch_time == 'all' && Showmsg('no_condition');

	$seekfid = $s_type == 'all' ? 'all':($s_type == 'forum' ? $f_fid : $c_fid);
	$admincheck = 0;
	if (is_numeric($seekfid)){
		$rt = $db->get_one("SELECT forumadmin,fupadmin FROM pw_forums WHERE fid='$seekfid'");
		if (admincheck($rt['forumadmin'],$rt['fupadmin'],$windid)){
			$admincheck = 1;
		} elseif ($groupid != 5 && ($SYSTEM['tpctype'] || $SYSTEM['delatc'] || $SYSTEM['moveatc'] || $SYSTEM['copyatc'])){
			$admincheck = 1;
		}
	}

	$keyword && strlen(trim($keyword)) <= 2  && Showmsg('search_word_limit');
	$authorid && !is_numeric($authorid) && Showmsg('user_not_exists');
	!is_numeric($sch_area) && $sch_area = 0;
	$method   = $method == 'AND' ? 'AND' : 'OR';
	$schline  = trim($keyword).'|'.trim($method).'|'.trim($sch_area).'|'.trim($seekfid).'|'.trim($pwuser).'|'.trim($authorid).'|'.trim($sch_time).'|'.trim($digest);
	$db_plist && $schline.='|'.$ptable;
	$orderway = ($orderway == 'replies' || $orderway == 'hits') ? $orderway : 'lastpost';
	$asc      = $asc == 'ASC'? 'ASC' :'DESC';
	$orderby  = "ORDER BY $orderway $asc";
	$schedid  = '';

	if (isset($_GET['sid']) && $_GET['sid']){
		@extract($db->get_one("SELECT schtime,total,schedid FROM pw_schcache WHERE sid='$sid'"));
	} else {
		if (!$authorid){
			@extract($db->get_one("SELECT sid,schline AS schlinee, schtime,total,schedid FROM pw_schcache WHERE schline='$schline' LIMIT 1"));
		}
		if($newatc && $timestamp - $schtime > 1800){
			$db->update("DELETE FROM pw_schcache WHERE sid='$sid'");
			$schedid = '';
		}
		if (empty($schedid)){
			$cachetime = 3600;
			$db->update("DELETE FROM pw_schcache WHERE schtime<$timestamp-$cachetime");
			if ($_POST && $gp_searchtime != 0){
				if ($timestamp - GetCookie('lasttime') < $gp_searchtime){
					Showmsg('search_limit');
				}
				Cookie('lasttime',$timestamp,0);
			}
			if (is_numeric($seekfid)){
				if ($forum[$seekfid]['type'] == 'category'){
					Showmsg('search_cate');
				}
				if (strpos(','.$fidout.',',','.$seekfid.',')  === false){
					$sqlwhere = "t.fid='$seekfid' AND ifcheck=1 ";
				} else{
					Showmsg('search_forum_right');
				}
			} else{
				$sqlwhere = "t.fid NOT IN ($fidout) AND ifcheck=1 ";
			}

			if ($sch_area == '1' && $gp_allowsearch == 2){
				$sqltable = "pw_threads t LEFT JOIN pw_tmsgs tm ON tm.tid=t.tid";
			} elseif ($sch_area == '2' && $gp_allowsearch == 2){
				$pw_posts = GetPtable($ptable);
				$sqltable = "$pw_posts t";
				$orderby  = "ORDER BY tid $asc";
			} else{
				$sqltable = "pw_threads t";
			}

			$uids=$keywhere='';
			if ($keyword){
				$keyword      = str_replace("%",'\%',$keyword);
				$keyword      = str_replace("_",'\_',$keyword);
				$keyword      = trim($keyword);
				$keywordarray = explode("|",$keyword);
				foreach($keywordarray as $value){
					if ($value){
						$value     = addslashes($value);
						$keywhere .= $method;
						if ($sch_area == '0'){
							$keywhere .= " t.subject LIKE '%$value%'";
						} elseif ($sch_area == '1' && $gp_allowsearch == 2){
							$keywhere .= " (t.subject LIKE '%$value%' OR tm.content LIKE '%$value%') ";
						} elseif ($sch_area == '2' && $gp_allowsearch == 2){
							$keywhere .= " (t.subject LIKE '%$value%' OR t.content LIKE '%$value%') ";
						}
					}
				}
				if ($keywhere){
					$keywhere = substr_replace($keywhere,"",0,3);
					$keywhere && $sqlwhere .= "AND ($keywhere)";
				} else{
					Showmsg('illegal_keyword');
				}
			}elseif ($pwuser){
				if (!str_replace('*','',$pwuser)){
					Showmsg('illegal_author');
				}
				$pwuser = str_replace("%",'\%',$pwuser);
				$pwuser = str_replace("_",'\_',$pwuser);
				$pwuser = addslashes(trim($pwuser));
				$pwuser = str_replace('*','_',$pwuser);
				$query=$db->query("SELECT uid FROM pw_members WHERE username LIKE '$pwuser'");
				while($member=$db->fetch_array($query)){
					$uids .= $member['uid'].',';
				}
				$uids ? $uids=substr($uids,0,-1) : $sqlwhere.=' AND 0 ';
			} elseif (is_numeric($authorid)){
				$uids = $authorid;
			}
			$uids   && $sqlwhere .= "AND t.authorid IN($uids)";
			$digest && $sch_area != '2' && $sqlwhere .= "AND t.digest>'0'";

			if (is_numeric($sch_time) && strlen($sch_time)<10){
				$sch_time  = $timestamp-$sch_time;
				$sqlwhere .= "AND t.postdate>'$sch_time'";
			}
			if ($newatc){
				$limit = 'LIMIT 50';
			} else{
				!$db_maxresult && $db_maxresult=500;
				$limit = "LIMIT $db_maxresult";
			}
			$query   = $db->query("SELECT DISTINCT t.tid FROM $sqltable WHERE $sqlwhere $orderby $limit");
			$total   = $db->num_rows($query);
			$schedid = $extra = '';
			while($sch = $db->fetch_array($query)){
				if ($sch['tid']){
					$schedid .= $extra.$sch['tid'];
					$extra    = ',';
				}
			}
			$db->free_result($query);
			if ($schedid && !$authorid){
				$db->update("INSERT INTO pw_schcache(schline,schtime,total,schedid) VALUES('$schline','$timestamp','$total','$schedid')");
				$sid = $db->insert_id();
			}
		}
	}
	if ($schedid){
		if (!is_numeric($page) || $page<1){
			$page = 1;
		}
		$start = ($page-1)*$db_perpage;
		$limit = "LIMIT $start,$db_perpage";
		require R_P.'require/forum.php';
		$numofpage = ceil($total/$db_perpage);
		if (substr($schedid,-1) == ','){
			$schedid = substr($schedid,0,-1);
		}
		$rawkeyword = rawurlencode($keyword);
		$pages = numofpage($total,$page,$numofpage,"search.php?step=$step&sid=$sid&keyword=$rawkeyword&method=$method&pwuser=".rawurlencode($pwuser)."&authorid=$authorid&orderway=$orderway&s_type=$s_type&f_fid=$f_fid&c_fid=$c_fid&sch_time=$sch_time&sch_area=$sch_area&digest=$digest&");

		$schdb = array();
		$query = $db->query("SELECT * FROM pw_threads WHERE tid IN ($schedid) AND fid NOT IN ($fidout) $orderby $limit");
		while($sch = $db->fetch_array($query)){
			//$sch['subject'] = substrs($sch['subject'],35);
			if ($sch['titlefont']){
				$titledetail=explode("~",$sch['titlefont']);
				if ($titledetail[0])$sch['subject'] = "<font color=$titledetail[0]>$sch[subject]</font>";
				if ($titledetail[1])$sch['subject'] = "<b>$sch[subject]</b>";
				if ($titledetail[2])$sch['subject'] = "<i>$sch[subject]</i>";
				if ($titledetail[3])$sch['subject'] = "<u>$sch[subject]</u>";
			}
			$keywords = explode("|",$keyword);
			foreach($keywords as $value){
				$sch['subject'] = str_replace($value,"<font color='red'><u>$value</u></font>",$sch['subject']);
			}
			if($sch['special']==1){
				$p_status = $sch['locked'] == 0 ? 'vote' : 'votelock';
			}elseif($sch['locked']>0){
				$p_status = $sch['locked'] == 1 ? 'topiclock' : 'topicclose';
			}else{
				$p_status = $sch['replies']>=10 ? 'topichot' : 'topicnew';
			}
			$sch['status'] = "<img src='$imgpath/$stylepath/thread/".$p_status.".gif' border=0>";

			$sch['forumname'] = $forum[$sch['fid']]['name'];
			$sch['postdate'] = get_date($sch['postdate'],"Y-m-d");
			$sch['lastpost'] = get_date($sch['lastpost']);
			$sch['lastposterraw'] = rawurlencode($sch['lastposter']);
			$sch['anonymous'] && $sch['author']!=$windid && $groupid!='3' && $sch['author']=$db_anonymousname;

			$schdb[] = $sch;
		}
		$db->free_result($query);
		require_once PrintEOT('search');footer();
	}else{
		Showmsg('search_none');
	}
}
?>