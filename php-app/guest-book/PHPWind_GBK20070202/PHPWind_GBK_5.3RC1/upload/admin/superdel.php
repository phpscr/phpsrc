<?php
!function_exists('adminmsg') && exit('Forbidden');

require_once(R_P.'require/forum.php');
if($a_type=='article'){
	require_once(R_P.'require/updateforum.php');
	$basename="$admin_file?adminjob=superdel&a_type=article";

	if ($admin_gid == 5){
		list($allowfid,$forumcache) = GetAllowForum($admin_name);
		$sql = "fid IN($allowfid)";
	} else {
		include(D_P.'data/bbscache/forumcache.php');
		list($hidefid,$hideforum) = GetHiddenForum();
		if($admin_gid == 3){
			$forumcache .= $hideforum;
			$sql = '1';
		} else{
			$sql = "fid NOT IN($hidefid)";
		}
	}

	if(empty($action)){
		if($db_plist){
			$p_table="<option value=\"0\">post</option>";
			$p_list=explode(',',$db_plist);
			foreach($p_list as $key=>$val){
				$p_table .= "<option value=\"$val\">post$val</option>";
			}
			$p_table=str_replace("<option value=\"$db_ptable\">","<option value=\"$db_ptable\" selected>",$p_table);
		}
		include PrintEot('superdel');exit;
	} elseif($action=='deltpc'){
		if(empty($_POST['step'])){
			$_POST['pstarttime'] && $pstarttime=PwStrtoTime($pstarttime);
			$_POST['pendtime']   && $pendtime=PwStrtoTime($pendtime);
			$_POST['lstarttime'] && $lstarttime=PwStrtoTime($lstarttime);
			$_POST['lendtime']   && $lendtime=PwStrtoTime($lendtime);
			$tstart=(int)$tstart;
			$tend=(int)$tend;

			if($fid=='-1' && !$pstarttime && !$pendtime && !$tcounts && !$counts && !$lstarttime && !$lendtime && !$hits && !$replies && !$author && !$keyword && !$userip && !$tstart && !$tend){
				adminmsg('noenough_condition');
			} else{
				if(is_numeric($fid) && $fid > 0){
					$sql .= " AND t.fid='$fid'";
				}
				if($ifkeep){
					$sql.=" AND t.topped=0 AND t.digest=0"; 
				}
				if($pstarttime){
					$sql.=" AND t.postdate>'$pstarttime'";
				}
				if($pendtime){
					$sql.=" AND t.postdate<'$pendtime'";
				}
				if($lstarttime){
					$sql.=" AND t.lastpost>'$lstarttime'";
				}
				if($lendtime){
					$sql.=" AND t.lastpost<'$lendtime'";
				}
				if($tstart){
					$sql.=" AND t.tid>'$tstart'";
				}
				if($tend){
					$sql.=" AND t.tid<'$tend'";
				}
				$hits    && $sql.=" AND t.hits<".(int)$hits;
				$replies && $sql.=" AND t.replies<".(int)$replies;
				if($tcounts){
					$sql.=" AND char_length(tm.content)>".(int)$tcounts;
				}elseif($counts){
					$sql.=" AND char_length(tm.content)<".(int)$counts;
				}
				if($author){
					$authorarray=explode(",",$author);
					foreach($authorarray as $value){
						$value=addslashes(str_replace('*','%',$value));
						$authorwhere.=" OR username LIKE '$value'";
					}
					$authorwhere=substr_replace($authorwhere,"",0,3);
					$authorids='-99';
					$query=$db->query("SELECT uid FROM pw_members WHERE $authorwhere");
					while($rt=$db->fetch_array($query)){
						$authorids .= ','.$rt['uid'];
					}
					$sql.=" AND t.authorid IN($authorids)";
				}
				if($keyword){
					$keyword=trim($keyword);
					$keywordarray=explode(",",$keyword);
					foreach($keywordarray as $value){
						$value=str_replace('*','%',$value);
						$keywhere.='OR';
						$keywhere.=" tm.content LIKE '%$value%' OR t.subject LIKE '%$value%' ";
					}
					$keywhere=substr_replace($keywhere,"",0,3);
					$sql.=" AND ($keywhere) ";	
				}
				if($userip){
					$userip=str_replace('*','%',$userip);
					$sql.=" AND (tm.userip LIKE '$userip') ";
					$ip_add=',tm.userip';
				}

				$rs=$db->get_one("SELECT COUNT(*) AS count FROM pw_threads t LEFT JOIN pw_tmsgs tm ON  tm.tid=t.tid WHERE $sql");
				$count=$rs['count'];
				if(!is_numeric($lines))$lines=100;
				(!is_numeric($page) || $page < 1) && $page=1;
				$numofpage=ceil($count/$lines);
				if($numofpage&&$page>$numofpage){
					$page=$numofpage;
				}
				$pages=numofpage($count,$page,$numofpage,"$admin_file?adminjob=superdel&a_type=article&action=$action&fid=$fid&ifkeep=$ifkeep&pstarttime=$pstarttime&pendtime=$pendtime&lstarttime=$lstarttime&lendtime=$lendtime&tstart=$tstart&tend=$tend&hits=$hits&replies=$replies&author=".rawurlencode($author)."&keyword=".rawurlencode($keyword)."&userip=$userip&lines=$lines&");
				$start=($page-1)*$lines;
				$limit="LIMIT $start,$lines";

				$topicdb=array();
				include(D_P.'data/bbscache/forum_cache.php');
				$query=$db->query("SELECT t.*,tm.userip FROM pw_threads t LEFT JOIN pw_tmsgs tm ON  tm.tid=t.tid WHERE $sql $limit");
				while($topic=$db->fetch_array($query)){
					if($_POST['direct']){
						$delid[$topic['tid']]=$topic['fid'];
					} else{
						$topic['forumname'] = $forum[$topic['fid']]['name'];
						$topic['postdate'] = get_date($topic['postdate']);
						$topic['lastpost'] = get_date($topic['lastpost']);
						$topicdb[]=$topic;
					}
				}
				if(!$_POST['direct']){
					include PrintEot('superdel');exit;
				}
			}
		}
		if($_POST['step']==2 || $_POST['direct']){
			!$delid && adminmsg('operate_error');
			$delids=$delaids=$pollids=$actids='';
			$fidarray=array();
			foreach($delid as $key=>$value){
				is_numeric($key) && $delids.=$key.',';
				if(!in_array($value,$fidarray)){
					$fidarray[]=$value;
				}
			}
			$delids=substr($delids,0,-1);
			/**
			* 删除帖子
			*/
			$ptable_a=array();
			$query=$db->query("SELECT t.tid,t.fid,t.postdate,t.special,t.ptable,tm.aid,t.ifupload FROM pw_threads t LEFT JOIN pw_tmsgs tm ON tm.tid=t.tid WHERE $sql AND t.tid IN($delids)");
			while(@extract($db->fetch_array($query))){
				$ptable_a[$ptable]=1;
				if($aid){
					$attachs= unserialize(stripslashes($aid));
					foreach($attachs as $key=>$value){
						is_numeric($key) && $delaids.=$key.',';
						P_unlink("$attachdir/$value[attachurl]");
					}
				}
				$special==1 && $pollids.=$tid.',';
				$special==2 && $actids .=$tid.',';
				if($ifupload){
					$pw_posts = GetPtable($ptable);
					$query2=$db->query("SELECT aid FROM $pw_posts WHERE tid='$tid'");
					while(@extract($db->fetch_array($query2))){
						if($aid){
							$attachs= unserialize(stripslashes($aid));
							foreach($attachs as $key=>$value){
								is_numeric($key) && $delaids.=$key.',';
								P_unlink("$attachdir/$value[attachurl]");
							}
						}
					}
				}
				$htmurl=$htmdir.'/'.$fid.'/'.date('ym',$postdate).'/'.$tid.'.html';
				if(file_exists(R_P.$htmurl)){
					P_unlink(R_P.$htmurl);
				}
			}
			if($pollids){
				$pollids=substr($pollids,0,-1);
				$db->update("DELETE FROM pw_polls WHERE tid IN($pollids)");
			}
			if($actids){
				$actids = substr($actids,0,-1);
				$db->update("DELETE FROM pw_activity WHERE tid IN($actids)");
				$db->update("DELETE FROM pw_actmember WHERE actid IN($actids)");
			}
			if($delaids){
				$delaids=substr($delaids,0,-1);
				$db->update("DELETE FROM pw_attachs WHERE aid IN($delaids)");
			}

			$db->update("DELETE FROM pw_threads WHERE tid IN ($delids)");
			foreach($patble_a as $key=>$val){
				$pw_posts = GetPtable($key);
				$db->update("DELETE FROM $pw_posts WHERE tid IN ($delids)");
			}
			$db->update("DELETE FROM pw_tmsgs   WHERE tid IN ($delids)");
			/**
			* 数据更新
			*/
			foreach($fidarray as $fid){
				updateforum($fid);
			}
			P_unlink(D_P.'data/bbscache/c_cache.php');
			adminmsg('operate_success');
		}
	} elseif($action=='delrpl'){
		if(empty($_POST['step'])){
			$pstart=(int)$pstart;
			$pend=(int)$pend;
			if(!$counts && !$tcounts && $fid=='-1' && !$keyword && !$tid && !$author && !$userip && !$pstart && !$pend){
				adminmsg('noenough_condition');
			}
			$pw_posts = GetPtable($ptable);
			if(is_numeric($fid) && $fid > 0){
				$sql .= " AND fid='$fid'";
			}
			if($tid){
				$tids = 0;
				$tid_array=explode(",",$tid);
				foreach($tid_array as $value){
					if(is_numeric($value)){
						$tids.=','.$value;
					}
				}
				$tids && $sql.=" AND tid IN($tids)";
			}
			if($pstart){
				$sql.=" AND pid>'$pstart'";
			}
			if($pend){
				$sql.=" AND pid<'$pend'";
			}
			if($author){
				$authorarray=explode(",",$author);
				foreach($authorarray as $value){
					$value=addslashes(str_replace('*','%',$value));
					$authorwhere.=" OR username LIKE '$value'";
				}
				$authorwhere=substr_replace($authorwhere,"",0,3);
				$authorids='-99';
				$query=$db->query("SELECT uid FROM pw_members WHERE $authorwhere");
				while($rt=$db->fetch_array($query)){
					$authorids .= ','.$rt['uid'];
				}
				$sql.=" AND authorid IN($authorids)";
			}
			if($keyword){
				$keyword=trim($keyword);
				$keywordarray=explode(",",$keyword);
				foreach($keywordarray as $value){
					$value=str_replace('*','%',$value);
					$keywhere.=" OR content LIKE '%$value%' ";
				}
				$keywhere=substr_replace($keywhere,"",0,3);
				$sql.=" AND ($keywhere) ";	
			}
			if($userip){
				$userip=str_replace('*','%',$userip);
				$sql.=" AND (userip LIKE '$userip') ";
			}

			if($tcounts){
				$sql.=" AND char_length(content)>".(int)$tcounts;
			}elseif($counts){
				$sql.=" AND char_length(content)<".(int)$counts;
			}
			$nums=is_numeric($nums) ? $nums : 20;

			(!is_numeric($page) || $page < 1) && $page = 1;
			$limit = " LIMIT ".($page-1)*$nums.",$nums";
			$rt    = $db->get_one("SELECT COUNT(*) AS sum FROM $pw_posts WHERE $sql");
			$pages = numofpage($rt['sum'],$page,ceil($rt['sum']/$nums),"$admin_file?adminjob=superdel&a_type=article&action=$action&fid=$fid&tid=$tid&pstart=$pstart&pend=$pend&author=".rawurlencode($author)."&keyword=".rawurlencode($keyword)."&userip=$userip&tcounts=$tcounts&counts=$counts&nums=$nums&ptable=$ptable&");
			$sql .= $_POST['direct'] ? " LIMIT $nums" : $limit;
			$query=$db->query("SELECT fid,pid,tid,author,authorid,content,postdate,userip FROM $pw_posts WHERE $sql");
			while($post=$db->fetch_array($query)){
				if($_POST['direct']){
					$delid[$post['pid']]=$post['fid'].'_'.$post['tid'];
				} else{
					$post['delid']=$post['fid'].'_'.$post['tid'];
					$post['forumname'] = $forum[$post['fid']]['name'];
					$post['postdate'] = get_date($post['postdate']);
					$post['content']=substrs($post['content'],30);
					$postdb[]=$post;
				}
			}
			if(!$_POST['direct']){
				include PrintEot('superdel');exit;
			}
		}
		if($_POST['step']==2 || $_POST['direct']){
			!$delid && adminmsg('operate_error');
			$delids=$dtids='';
			$fidarray=$tidarray=array();
			foreach($delid as $key=>$value){
				is_numeric($key) && $delids.=$key.',';
				list($dfid,$dtid)=explode('_',$value);
				$tidarray[]=$dtid;
				$dtids.=$dtid.',';
				if(!in_array($dfid,$fidarray)){
					$fidarray[]=$dfid;
				}
			}
			$delids=substr($delids,0,-1);
			$dtids=substr($dtids,0,-1);
			/**
			* 删除帖子
			*/
			$pw_posts = GetPtable($ptable);
			$query=$db->query("SELECT tid,fid,postdate,ifupload FROM pw_threads WHERE tid IN($dtids)");
			while(@extract($db->fetch_array($query))){
				$htmurl=$htmdir.'/'.$fid.'/'.date('ym',$postdate).'/'.$tid.'.html';
				if(file_exists(R_P.$htmurl)){
					P_unlink(R_P.$htmurl);
				}
			}

			$query=$db->query("SELECT aid FROM $pw_posts WHERE pid IN ($delids)");
			while(@extract($db->fetch_array($query))){
				if($aid){
					$attachs= unserialize(stripslashes($aid));
					foreach($attachs as $key=>$value){
						is_numeric($key) && $delaids.=$key.',';
						P_unlink("$attachdir/$value[attachurl]");
					}
				}
			}
			if($delaids){
				$delaids=substr($delaids,0,-1);
				$db->update("DELETE FROM pw_attachs WHERE aid IN($delaids)");
			}

			$db->update("DELETE FROM $pw_posts WHERE pid IN ($delids)");

			$tidarray=array_count_values($tidarray);
			foreach($tidarray as $key=>$value){
				$db->update("UPDATE pw_threads SET replies=replies-'$value' WHERE tid='$key'");
			}
			/**
			* 数据更新
			*/
			foreach($fidarray as $fid){
				updateforum($fid);
			}
			P_unlink(D_P.'data/bbscache/c_cache.php');
			adminmsg('operate_success');
		}
	}
} elseif($a_type=='member'){
	$basename="$admin_file?adminjob=superdel&a_type=member";
	require_once(R_P.'require/writelog.php');
	require_once GetLang('all');
	if(empty($action)){
		$groupselect="<option value='-1'>$lang[reg_member]</option>";
		$query=$db->query("SELECT gid,gptype,grouptitle FROM pw_usergroups WHERE gptype<>'member' AND gptype<>'default' ORDER BY gid");
		while($group=$db->fetch_array($query)){
			$groupselect.="<option value=$group[gid]>$group[grouptitle]</option>";
		}
		include PrintEot('superdel');exit;
	} elseif($action=='del'){
		if(empty($_POST['step'])){
			if(!$schname && !$schemail && !$groupid && $regdate=='all' && $schlastvisit='all'){
				adminmsg('noenough_condition');
			} else{
				if($groupid != '-1'){
					if($groupid=='3' && !If_manager){
						adminmsg('manager_right');
					} elseif(($groupid=='4' || $groupid=='5') && $admin_gid != 3){
						adminmsg('admin_right');
					}
					$sql="m.groupid='$groupid'";
				} else{
					$sql="m.groupid='-1'";
				}
				if($schname!=''){
					$schname=addslashes(str_replace('*','%',$schname));
					$sql.=" AND (m.username LIKE '%$schname%')";
				}
				if($schemail!=''){
					$schemail=str_replace('*','%',$schemail);
					$sql.=" AND (m.email LIKE '%$schemail%')";
				}
				if($postnum){
					$sql.=" AND md.postnum<'$postnum'";
				}
				if($onlinetime){
					$sql.=" AND md.onlinetime<'$onlinetime'";
				}
				if($userip){
					$userip=str_replace('*','%',$userip);
					$sql.=" AND (md.onlineip LIKE '$userip') ";
				}
				if($regdate!='all'){
					$schtime=$timestamp-$regdate;
					$sql.=" AND m.regdate<'$schtime'";
				}
				if($schlastvisit!='all'){
					$schtime=$timestamp-$schlastvisit;
					$sql.=" AND md.thisvisit<'$schtime'";
				}
				if($orderway){
					!in_array($orderway,array('regdate','lastvisit','postnum')) && $orderway='uid';
					$order=" ORDER BY ".($orderway=='regdate' ? "m.uid " : "md.$orderway ");
					 $asc && $order.=$asc;
				}

				$rs=$db->get_one("SELECT COUNT(*) AS count FROM pw_members m LEFT JOIN pw_memberdata md ON md.uid=m.uid WHERE $sql");
				$count=$rs['count'];
				if(!is_numeric($lines))$lines=100;
				(!is_numeric($page) || $page < 1) && $page=1;
				$numofpage=ceil($count/$lines);
				if($numofpage&&$page>$numofpage){
					$page=$numofpage;
				}
				$pages=numofpage($count,$page,$numofpage,"$admin_file?adminjob=superdel&a_type=member&action=$action&groupid=$groupid&schname=".rawurlencode($schname)."&schemail=$schemail&postnum=$postnum&onlinetime=$onlinetime&regdate=$regdate&schlastvisit=$schlastvisit&orderway=$orderway&asc=$asc&lines=$lines&");
				$start=($page-1)*$lines;
				$limit="LIMIT $start,$lines";
				$query=$db->query("SELECT m.uid,m.username,m.email,m.groupid,m.regdate,md.thisvisit,md.postnum,md.onlineip FROM pw_members m LEFT JOIN pw_memberdata md ON md.uid=m.uid WHERE $sql $order $limit");
				while($sch=$db->fetch_array($query)){
					if($_POST['direct']){
						$delid[]=$sch['uid'];
					} else{
						strpos($sch['onlineip'],'|') && $sch['onlineip']=substr($sch['onlineip'],0,strpos($sch['onlineip'],'|'));
						if($sch['groupid']=='-1'){
							$sch['group']=$lang['reg_member'];
						} else{
							$sch['group']=$ltitle[$sch['groupid']];
						}
						$sch['regdate']= get_date($sch['regdate']);
						$sch['thisvisit']= get_date($sch['thisvisit']);
						$schdb[]=$sch;
					}
				}
				if(!$_POST['direct']){
					include PrintEot('superdel');exit;
				}
			}
		}
		if($_POST['step']==2 || $_POST['direct']){
			!$delid && adminmsg('operate_error');
			$delids='';
			foreach($delid as $value){
				$member=$db->get_one("SELECT m.username,m.groupid,m.regdate,md.postnum FROM pw_members m LEFT JOIN pw_memberdata md ON md.uid=m.uid WHERE m.uid='$value'");
				Add_S($member);
				if($member['username']==$manager)adminmsg('manager_right');
				if($member['groupid']==3 && !If_manager)adminmsg('manager_right');
				if($member['groupid']==6){
					$db->update("DELETE FROM pw_banuser WHERE uid='$value'");
				}
				is_numeric($value) && $delids.=$value.',';

				$log = array(
					'type'      => 'deluser',
					'username1' => $member['username'],
					'username2' => $admin_name,
					'field1'    => $fid,
					'field2'    => $member['groupid'],
					'field3'    => '',
					'descrip'   => 'deluser_descrip',
					'timestamp' => $timestamp,
					'ip'        => $onlineip,
				);
				writelog($log);
			}
			$delids=substr($delids,0,-1);
			$db->update("DELETE FROM pw_members WHERE uid IN ($delids)");
			$db->update("DELETE FROM pw_memberdata WHERE uid IN ($delids)");
			$db->update("DELETE FROM pw_memberinfo WHERE uid IN ($delids)");

			@extract($db->get_one("SELECT count(*) AS count FROM pw_members"));
			@extract($db->get_one("SELECT username FROM pw_members ORDER BY uid DESC LIMIT 1"));
			$db->update("UPDATE pw_bbsinfo SET newmember='$username', totalmember='$count'  WHERE id='1'");
			adminmsg('operate_success');
		}
	}
} elseif($a_type=='message'){
	$basename="$admin_file?adminjob=superdel&a_type=message";
	if(empty($action)){
		$basename="$admin_file?adminjob=superdel&a_type=message";
		include PrintEot('superdel');exit;
	} elseif($action=='del'){
		if(empty($_POST['step'])){
			if(!$type && !$keepnew && !$username && !$msgdate){
				adminmsg('noenough_condition');
			} else{
				if($type!='all'){
					$sql="m.type='$type'";
				} else{
					$sql='1 ';
				}
				if($keepnew){
					$sql.=" AND m.ifnew='0'";
				}
				if($msgdate){
					$sql.=" ";
				}
				if($keyword){
					$keyword=trim($keyword);
					$keywordarray=explode(",",$keyword);
					foreach($keywordarray as $value){
						$value=str_replace('*','%',$value);
						$keywhere.='OR';
						$keywhere.=" m.content LIKE '%$value%' OR m.title LIKE '%$value%' ";
					}
					$keywhere=substr_replace($keywhere,"",0,3);
					$sql.=" AND ($keywhere) ";	
				}
				if($fromuser){
					if($fromuser=='SYSTEM'){
						$sql .= " AND m.username='SYSTEM'";
					}else{
						$fromuser = str_replace('*','_',$fromuser);
						$rt = $db->get_one("SELECT uid,username,groupid FROM pw_members WHERE username LIKE '$fromuser'");
						if(!$rt){
							$errorname = $fromuser;
							adminmsg('user_not_exists');
						}elseif($rt['username'] == $manager && !If_manager){
							adminmsg('msg_managerright');
						}elseif($rt['groupid'] == 3 && $admin_gid != 3){
							adminmsg('msg_adminright');
						}
						if($type == 'rebox' || $type=='sebox'){
							$sql .= " AND m.type='$type' AND m.fromuid='$rt[uid]'";
						} else{
							$sql .= " AND m.fromuid='$rt[uid]'";
						}
					}
				}
				if($touser){
					$touser = str_replace('*','_',$touser);
					$rt = $db->get_one("SELECT uid,username,groupid FROM pw_members WHERE username LIKE '$touser'");
					if(!$rt){
						$errorname = $touser;
						adminmsg('user_not_exists');
					}elseif($rt['username'] == $manager && !If_manager){
						adminmsg('msg_managerright');
					}elseif($rt['groupid'] == 3 && $admin_gid != 3){
						adminmsg('msg_adminright');
					}
					if($type == 'rebox' || $type=='sebox'){
						$sql .= " AND m.type='$type' AND m.touid='$rt[uid]'";
					} else{
						$sql .= " AND m.touid='$rt[uid]'";
					}
				}
				if($msgdate){
					$schtime=$timestamp-$msgdate*24*3600;
					$sql.=" AND m.mdate<'$schtime'";
				}

				$rs=$db->get_one("SELECT COUNT(*) AS count FROM pw_msg m WHERE $sql");
				$count=$rs['count'];
				if(!is_numeric($lines))$lines=100;
				(!is_numeric($page) || $page < 1) && $page=1;
				$numofpage=ceil($count/$lines);
				if($numofpage&&$page>$numofpage){
					$page=$numofpage;
				}
				$pages=numofpage($count,$page,$numofpage,"$admin_file?adminjob=superdel&a_type=message&action=$action&type=$type&keepnew=$keepnew&msgdate=$msgdate&fromuser=".rawurlencode($fromuser)."&touser=".rawurlencode($touser)."&lines=$lines&");
				$start=($page-1)*$lines;
				$limit="LIMIT $start,$lines";

				$query=$db->query("SELECT m.*,m1.username as fromuser,m2.username as touser FROM pw_msg m LEFT JOIN pw_members m1 ON m1.uid=m.fromuid LEFT JOIN pw_members m2 ON m2.uid=m.touid WHERE $sql ORDER BY mid DESC $limit");
				while($message=$db->fetch_array($query)){
					if($_POST['direct']){
						$delid[]=$message['mid'];
					} else{
						!$message['fromuser'] && $message['fromuser'] = $message['username'];
						$message['date']=get_date($message['mdate']);
						if($message['type']=='public' && $message['togroups']){
							$togroups = explode(',',$message['togroups']);
							foreach($togroups as $key=>$gid){
								$gid && $message['touser'].=$message['touser'] ? ','.$ltitle[$gid] : $ltitle[$gid];
							}
						}
						$messagedb[]=$message;
					}
				}
				if(!$_POST['direct']){
					include PrintEot('superdel');exit;
				}
			}
		}
		if($_POST['step']==2 || $_POST['direct']){
			!$delid && adminmsg('operate_error');
			foreach($delid as $value){
				is_numeric($value) && $delids.=$value.',';
			}
			$delids=substr($delids,0,-1);
			$db->update("DELETE FROM pw_msg WHERE mid IN ($delids)");
			adminmsg('operate_success');
		}
	}
}
?>