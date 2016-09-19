<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=setforum";

include_once(D_P.'data/bbscache/forumcache.php');
include_once(D_P.'data/bbscache/creditdb.php');
require_once GetLang('all');
require_once(R_P.'require/updateforum.php');

list($hidefid,$hideforum) = GetHiddenForum();
//if(If_manager){
	$forumcache .= $hideforum;
//}
if(empty($action)){
	$catedb=$forumdb=$subdb1=$subdb2=array();
	$space="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";

	$query=$db->query("SELECT fid,fup,type,name,vieworder,forumadmin,f_type,cms FROM pw_forums WHERE cms!='1' ORDER BY vieworder");
	while($forums=$db->fetch_array($query)){
		$forums['name'] = preg_replace("/\<(.+?)\>/is","",$forums['name']);//去除html标签
		$forums['name'] = str_replace("<","&lt;",$forums['name']);
		$forums['name'] = str_replace(">","&gt;",$forums['name']);
		if($forums['forumadmin']){
			if(substr($forums['forumadmin'],0,1)==',' && substr($forums['forumadmin'],-1)==','){
				$forums['forumadmin']=substr($forums['forumadmin'],1,-1);
			}
		}
		if($forums['type']=='category'){
			$forums['space']=$space.$lang['forum_cate'];
			$catedb[]=$forums;
		} elseif($forums['type']=='forum'){
			$forums['space']=$space.$space.$lang['forum_cate1'];
			$forumdb[]=$forums;
		} else{
			if($forum[$forums['fup']]['type']=='forum'){
				$forums['space']=$space.$space.$space.$lang['forum_cate2'];
				$subdb1[]=$forums;
			} else{
				$forums['space']=$space.$space.$space.$space.$lang['forum_cate3'];
				$subdb2[]=$forums;
			}
		}
	}
	$threaddb=array();
	foreach($catedb as $cate){
		$threaddb[]=$cate;
		foreach($forumdb as $key2=>$forumss){
			if($forumss['fup']==$cate['fid']){
				$threaddb[]=$forumss;
				unset($forumdb[$key2]);
				foreach($subdb1 as $key3=>$sub1){
					if($sub1['fup']==$forumss['fid']){
						$threaddb[]=$sub1;
						unset($subdb1[$key3]);
						foreach($subdb2 as $key4=>$sub2){
							if($sub2['fup']==$sub1['fid']){
								$threaddb[]=$sub2;
								unset($subdb2[$key4]);
							}
						}
					}
				}
			}
		}
	}
	$forum_L=array();
	if($forumdb){
		foreach($forumdb as $value){
			$value['space']=$space.$space.$lang['forum_cate1'];
			$forum_L[]=$value;
		}
	}
	if($subdb1){
		foreach($subdb1 as $value){
			$value['space']=$space.$space.$lang['forum_cate1'];
			$forum_L[]=$value;
			//$db->update("UPDATE pw_forums SET fup=0 WHERE fid='$value[fid]'");
		}
	}
	if($subdb2){
		foreach($subdb2 as $value){
			$value['space']=$space.$space.$lang['forum_cate1'];
			$forum_L[]=$value;
			//$db->update("UPDATE pw_forums SET fup=0 WHERE fid='$value[fid]'");
		}
	}
	include PrintEot('setforum');exit;
}elseif($_POST['action']=='addforum'){
	if(empty($name)){
		adminmsg('setforum_empty');
	}
	if(!is_numeric($fup) && $forumtype!='category'){
		$fupfid=$db->get_one("SELECT fid FROM pw_forums WHERE type='category' ORDER BY fid LIMIT 1");
		$fup=$fupfid['fid'];
	}
	
	if($forumtype=='forum' && $forum[$fup]['type']!='category' && $ifsave){
		$forumtype='sub';
		$fupset=$db->get_one("SELECT f.allowhide,f.allowsell,f.allowreward,f.copyctrl,f.allowencode,f.viewsub,f.allowvisit,f.allowpost,f.allowrp,f.allowdownload,f.allowupload,f.f_type,f.f_check,f.cms,f.ifhide,fe.creditset,fe.forumset FROM pw_forums f LEFT JOIN pw_forumsextra fe USING(fid) WHERE f.fid='$fup'");
		Add_S($fupset);
		@extract($fupset,EXTR_OVERWRITE);
		$db->update("INSERT INTO pw_forums (fup,type,name,allowhide,allowsell,allowreward,copyctrl,allowencode,viewsub,allowvisit,allowpost,allowrp,allowdownload,allowupload,f_type,f_check,cms,ifhide) VALUES ('".(int)$fup."','$forumtype','$name','$allowhide','$allowsell','$allowreward','$copyctrl','$allowencode','$viewsub','$allowvisit','$allowpost','$allowrp','$allowdownload','$allowupload','$f_type','$f_check','$cms','$ifhide')");
		$fid=$db->insert_id();
		if($creditset || $forumset){
			$db->update("INSERT INTO pw_forumsextra (fid,creditset,forumset) VALUES ('$fid','$creditset','$forumset')");
		}
	} else{
		$forumtype=='forum' && $forum[$fup]['type']!='category' && $forumtype='sub';
		$f_type = $forum[$fup]['f_type'] == 'hidden' ? 'hidden' : 'forum';
		$db->update("INSERT INTO pw_forums (fup,type,name,f_type,cms,ifhide) VALUES ('".(int)$fup."', '$forumtype', '$name','$f_type','0','1')");
		$fid=$db->insert_id();
	}
	$db->update("INSERT INTO pw_forumdata (fid) VALUES ('$fid')");

	P_unlink(D_P.'data/bbscache/c_cache.php');
	updatecache_f();
	adminmsg('operate_success');
}elseif($_POST['action']=='editforum'){
	$errorname='';
	foreach($order as $key=>$vieworder){
		if($forumadmin[$key]){
			$newadmin = array();
			$admin_a  = explode(",",$forumadmin[$key]);
			foreach($admin_a as $aid=>$value){
				$value = trim($value);
				if($value && !in_array($value,$newadmin)){
					$mb=$db->get_one("SELECT uid FROM pw_members WHERE username='$value'");
					if($mb){
						$newadmin[] = $value;
					}else{
						$errorname .= $value.',';
					}
				}
			}
			$newadmin=implode(',',$newadmin);
			$newadmin && $newadmin=','.$newadmin.',';
		} else{
			$newadmin='';
		}
		$db->update("UPDATE pw_forums SET vieworder='$vieworder',forumadmin='$newadmin' WHERE fid='$key'");
	}
	updatecache_f();
	updateadmin();
	$errorname && adminmsg('user_not_exists');
	adminmsg('operate_success');
}elseif($action=='delete'){
	if(!$_POST['step']){
		include PrintEot('setforum');exit;
	}else{
		if($fid==$db_recycle){
			adminmsg('recycle_del');
		}
		@extract($db->get_one("SELECT fid,fup,type FROM pw_forums WHERE fid='$fid'"));
		@extract($db->get_one("SELECT count(*) AS count FROM pw_forums WHERE fup='$fid' AND type<>'category'"));
		if($count){
			adminmsg('forum_havesub');
		}
		delforum($fid);
		P_unlink(D_P.'data/bbscache/c_cache.php');
		updatecache_f();
		adminmsg('operate_success');
	}
} elseif($action=='credit'){
	$basename="$admin_file?adminjob=setforum&action=credit&fid=$fid";
	$rt=$db->get_one("SELECT fid FROM pw_forums WHERE fid='$fid' AND type!='category'");
	if(!$rt){
		adminmsg('forum_not_exists');
	}
	if(!$_POST['step']){
		@extract($db->get_one("SELECT * FROM pw_forumsextra WHERE fid='$fid'"));
		if($creditset){
			$credit=unserialize($creditset);
			foreach($credit as $key => $value){
				foreach($value as $k => $val){
					if($val!=='' && $key == 'rvrc' && $k != 'Reply' && $k != 'Deleterp'){
						$val /= 10;
					}
					$val!=='' && $val = (int)$val;
					$credit[$key][$k] = $val;
				}
			}
		}
		include PrintEot('setforum');exit;
	} else{
		foreach($creditdb as $key => $value){
			foreach($value as $k => $val){
				if($val!=='' && $key == 'rvrc' && $k != 'Reply' && $k != 'Deleterp'){
					$val *= 10;
				}					
				$val!=='' && $val = (int)$val;
				$creditdb[$key][$k] = $val;
			}
		}
		$creditset=$creditdb ? serialize($creditdb) : '';

		$db->pw_update(
			"SELECT fid FROM pw_forumsextra WHERE fid='$fid'",
			"UPDATE pw_forumsextra SET creditset='$creditset' WHERE fid='$fid'",
			"INSERT INTO pw_forumsextra SET creditset='$creditset',fid='$fid'"
		);
		if(is_array($otherfid) && is_array($otherforum)){
			foreach($otherfid as $key => $selfid){
				if(!$selfid || !is_numeric($selfid) || $selfid == $fid || $forum[$selfid]['type'] == 'category'){
					continue;
				}
				$rt = $db->get_one("SELECT fid,creditset FROM pw_forumsextra WHERE fid='$selfid'");
				if($rt['fid']){
					if($rt['creditset']){
						$newcreditset = unserialize($rt['creditset']);
						foreach($newcreditset as $key => $value){
							foreach($value as $k => $val){
								if($otherforum[$key][$k]){
									$newcreditset[$key][$k] = $creditdb[$key][$k];
								}
							}
						}
						$creditset = serialize($newcreditset);
						$db->update("UPDATE pw_forumsextra SET creditset='$creditset' WHERE fid='$selfid'");
					} else{
						$newcreditset = array();
						foreach($creditdb as $key => $value){
							foreach($value as $k => $val){
								if($otherforum[$key][$k]){
									$newcreditset[$key][$k] = $creditdb[$key][$k];
								} else{
									$newcreditset[$key][$k] = 0;
								}
							}
						}
						$creditset = serialize($newcreditset);
						$db->update("UPDATE pw_forumsextra SET creditset='$creditset' WHERE fid='$selfid'");
					}
				} else{
					$newcreditset = array();
					foreach($creditdb as $key => $value){
						foreach($value as $k => $val){
							if($otherforum[$key][$k]){
								$newcreditset[$key][$k] = $creditdb[$key][$k];
							} else{
								$newcreditset[$key][$k] = 0;
							}
						}
					}
					$creditset = serialize($newcreditset);
					$db->update("INSERT INTO pw_forumsextra SET creditset='$creditset',fid='$selfid'");
				}
			}
		}
		adminmsg('operate_success');
	}
} elseif($action=='edit'){
	if(!$_POST['step']){
		@extract($db->get_one("SELECT f.*,fe.forumset FROM pw_forums f LEFT JOIN pw_forumsextra fe USING(fid) WHERE f.fid='$fid'"));
		$forumset=unserialize($forumset);
		$forumset['newtime']  /= 60;
		$forumset['rvrcneed'] /= 10;
		$forumset['addtpctype']		? $addtpctype_Y='checked'	: $addtpctype_N='checked';
		$forumset['allowsale']!=2	? $allowsale_Y='checked'	: $allowsale_N='checked';
		$forumset['allowsoft']		? $allowsoft_Y='checked'	: $allowsoft_N='checked';
		ifcheck($forumset['allowactive'],'active');
		ifcheck($forumset['anonymous'],'anonymous');
		list($rw_b_val,$rw_a_val)=explode(',',$forumset['rewarddb']);

		$name = str_replace("<","&lt;",$name);
		$name = str_replace(">","&gt;",$name);
		if($forumadmin){
			if(substr($forumadmin,0,1)==',' && substr($forumadmin,-1)==','){
				$forumadmin=substr($forumadmin,1,-1);
			}
		}

		$cms ? $check_c='checked' : $check_f='checked';

		require_once(R_P."require/forum.php");
		$setfid_style=getstyles($style);

		if($type!='category'){
			${'sel_'.$forumset['orderway']}='selected';
			${'sel_'.$forumset['asc']}='selected';
			$name    = str_replace("<","&lt;",$name);  
			$name    = str_replace(">","&gt;",$name);
			$descrip = str_replace("<","&lt;",$descrip);  
			$descrip = str_replace(">","&gt;",$descrip);
			$viewsub?$sub_open='checked':$sub_close='checked';
			$allowhide?$hide_open='checked':$hide_close='checked';
			$allowsell?$sell_open='checked':$sell_close='checked';
			$allowencode?$encode_open='checked':$encode_close='checked';
			$copyctrl?$copyctrl_open='checked':$copyctrl_close='checked';
			ifcheck($showsub,'showsub');
			${'check_'.$f_check}='checked';
			$ftype[$f_type]='selected';
			$ifhide ? $ifhide_close='checked' :$ifhide_open='checked';
			$forumcache = str_replace("<option value=\"$fup\">","<option value=\"$fup\" selected>",$forumcache);

			$usergroup="<table cellspacing='0' cellpadding='0' border='0' width='100%' align='center'><tr>";
			foreach($ltitle as $key=>$value){
				if($key==1||$key==2)continue;
				$htm_tr='';$num++;$num%5==0?$htm_tr='</tr><tr>':'';
				$usergroup.="<td><input type='checkbox' name='permit[]' value='$key' _{$key}_>$value</td>$htm_tr";
			}
			$usergroup.="</tr></table>";
			$viewvisit   =str_replace('permit','allowvisit',$usergroup);
			$viewpost    =str_replace('permit','allowpost',$usergroup);
			$viewrp   =str_replace('permit','allowrp',$usergroup);
			$viewupload  =str_replace('permit','allowupload',$usergroup);
			$viewdownload=str_replace('permit','allowdownload',$usergroup);
			$visitper =explode(",",$allowvisit);
			$postper  =explode(",",$allowpost);
			$rpper	  =explode(",",$allowrp);
			$uploadper=explode(",",$allowupload);
			$downper  =explode(",",$allowdownload);
			$t_typedb =explode("\t",$t_type);/*主题分类*/
			$t_typedb[0] = (int)$t_typedb[0];
			${'t_type_'.$t_typedb[0]}='checked';
			foreach($visitper as $value)
				$viewvisit =str_replace("_{$value}_",'checked',$viewvisit);			
			foreach($postper as $value)
				$viewpost  =str_replace("_{$value}_",'checked',$viewpost);
			foreach($rpper as $value)
				$viewrp  =str_replace("_{$value}_",'checked',$viewrp);
			foreach($uploadper as $value)
				$viewupload=str_replace("_{$value}_",'checked',$viewupload);			
			foreach($downper as $value)
				$viewdownload  =str_replace("_{$value}_",'checked',$viewdownload);			
		}
		include PrintEot('setforum');exit;
	} elseif($_POST['step']==2){
		$forum   = $db->get_one("SELECT type,fup,forumadmin FROM pw_forums WHERE fid='$fid'");
		$name    = Quot_cv($name);
		//$descrip = Quot_cv($descrip);
		$name    = str_replace('<iframe','&lt;iframe',$name);
		$descrip = str_replace('<iframe','&lt;iframe',$descrip);
		strlen($descrip)>250 && adminmsg('descrip_long');
		if($forum['forumadmin'] != ",$forumadmin,"){
			$newadmin = array();
			$admin_a  = explode(",",$forumadmin);
			foreach($admin_a as $aid=>$value){
				$value = trim($value);
				if($value && !in_array($value,$newadmin)){
					$mb=$db->get_one("SELECT uid FROM pw_members WHERE username='$value'");
					if($mb){
						$newadmin[] = $value;
					}else{
						$errorname .= $value.',';
					}
				}
			}
			$newadmin=implode(',',$newadmin);
			$newadmin && $newadmin=','.$newadmin.',';
			$sqladd = ",forumadmin='$newadmin'";
		} else{
			$sqladd = '';
		}
		if($forum['type']=='category'){
			$db->update("UPDATE pw_forums SET name='$name',vieworder='$vieworder' $sqladd ,dirname='$dirname',style='$style',across='$across',cms='$cms' WHERE fid='$fid'");
		} else{
			$forumsetdb['newtime'] *= 60;
			foreach($forumsetdb as $key => $value){
				if($key == 'link'){
					$forumsetdb[$key] = str_replace(array('"',"'",'\\'),array('','',''),$value);
				} elseif($key!='orderway' && $key!='asc') {
					$forumsetdb[$key] = (int)$value;
				}
			}
			$forumsetdb['rvrcneed'] *= 10;
			foreach($rewarddb as $key=>$val){
				(!is_numeric($val) || $val<0) && $rewarddb[$key] = 0;
			}
			$forumsetdb['rewarddb']=implode(',',$rewarddb);

			$forumsextradb=serialize($forumsetdb);
			$db->pw_update(
				"SELECT fid FROM pw_forumsextra WHERE fid='$fid'",
				"UPDATE pw_forumsextra SET forumset='$forumsextradb' WHERE fid='$fid'",
				"INSERT INTO pw_forumsextra SET forumset='$forumsextradb',fid='$fid'"
			);

			$fup=$cms=='1' ? $cfup : $ffup;
			$fup == $fid && adminmsg('setforum_fupsame');
			if(!$fup || !is_numeric($fup)){
				$fupfid=$db->get_one("SELECT fid FROM pw_forums WHERE type='category' ORDER BY fid LIMIT 1");
				$fup=$fupfid['fid'];
			}
			if(!empty($password)&&strlen($password)!=32){
				$password=md5($password);
			}
			$allowvisit	  && $allowvisit   =','.implode(",",$allowvisit).',';
			$allowpost	  && $allowpost	   =','.implode(",",$allowpost).',';
			$allowrp	  && $allowrp	   =','.implode(",",$allowrp).',';
			$allowupload  && $allowupload  =','.implode(",",$allowupload).',';
			$allowdownload&& $allowdownload=','.implode(",",$allowdownload).',';
			$rt=$db->get_one("SELECT type,cms FROM pw_forums WHERE fid='$fup'");
			if($rt['type']=='category'){
				$type='forum';
			} else{
				if(($rt['cms'] && !$cms) || (!$rt['cms'] && $cms)){
					adminmsg('setforum_cms');
				}
				$type='sub';
			}

			$t_type=implode("\t",$t_db)."\t";/*主题分类*/
			$t_type=Quot_cv($t_type);
			if($forum['fup']!=$fup){
				$rt = $db->get_one("SELECT fid FROM pw_forums WHERE fup='$fid'");
				if($rt && $rt['fid'] != $fid){
					adminmsg('forum_havesub');
				}
			}
			if($f_type=='hidden' && $allowvisit==''){
				$basename="$admin_file?adminjob=setforum&action=edit&fid=$fid";
				adminmsg('forum_hidden');
			}
			if($allowreward>9 || $allowreward<0)$allowreward=0;
			$db->update("UPDATE pw_forums SET fup='$fup',type='$type',name='$name',vieworder='$vieworder',logo='$logo',descrip='$descrip' $sqladd ,style='$style',across='$across',allowhide='$allowhide',allowsell='$allowsell',allowreward='$allowreward',copyctrl='$copyctrl',allowencode='$allowencode',password='$password' ,viewsub='$viewsub',allowvisit='$allowvisit',allowpost='$allowpost' ,allowrp='$allowrp',allowdownload='$allowdownload',allowupload='$allowupload',f_type='$f_type',f_check='$f_check',t_type='$t_type',cms='$cms',ifhide='".(int)$ifhide."',showsub='$showsub' WHERE fid='$fid'");
			updateforum($fup);
			updateforum($forum['fup']);
		}
		P_unlink(D_P.'data/bbscache/c_cache.php');

		$othersql = $otherfids = $extra = $update_m = $update_s = '';
		if(is_array($otherfid)){
			$otherfids = "'".implode("','",$otherfid)."'";
		}
		if(is_array($otherforum)){
			foreach($otherforum as $key => $value){
				if($key === 'forumsetdb'){
					$update_f = 1;
					continue;
				}
				$othersql .= "$extra$key='".$$key."'";
				$extra = ',';
			}
		}
		if($othersql && $otherfids){
			$db->update("UPDATE pw_forums SET $othersql WHERE fid IN($otherfids)");
		}
		if($otherfids && $update_f){
			include(D_P.'data/bbscache/forum_cache.php');
			foreach($otherfid as $key => $selfid){
				if(!$selfid || !is_numeric($selfid) || $selfid == $fid || $forum[$selfid]['type'] == 'category'){
					continue;
				}
				$rt = $db->get_one("SELECT fid,forumset FROM pw_forumsextra WHERE fid='$selfid'");
				if($rt['fid']){
					$newforumset = unserialize($rt['forumset']);
					foreach($forumsetdb as $key => $value){
						if($otherforum['forumsetdb'][$key]){
							$newforumset[$key] = $value;
						} elseif(!isset($newforumset[$key])){
							$newforumset[$key] = 0;
						}
					}
					$forumset = serialize($newforumset);
					$db->update("UPDATE pw_forumsextra SET forumset='$forumset' WHERE fid='$selfid'");
				} else{
					$newforumset = array();
					foreach($forumsetdb as $key => $value){
						if($otherforum['forumsetdb'][$key]){
							$newforumset[$key] = $value;
						} else{
							$newforumset[$key] = 0;
						}
					}
					$forumset = serialize($newforumset);
					$db->update("INSERT INTO pw_forumsextra SET forumset='$forumset',fid='$selfid'");
				}
			}
		}
		updatecache_f();
		$sqladd && updateadmin();
		$basename="$admin_file?adminjob=setforum&action=edit&fid=$fid";
		adminmsg('operate_success');
	}
}
function delforum($fid){
	global $db;
	$foruminfo=$db->get_one("SELECT fid,fup,forumadmin FROM pw_forums WHERE fid='$fid'");
	$db->update("DELETE FROM pw_forums WHERE fid='$fid'");
	$db->update("DELETE FROM pw_forumdata WHERE fid='$fid'");
	$db->update("DELETE FROM pw_forumsextra WHERE fid='$fid'");
	if($foruminfo['forumadmin']){
		$forumadmin=explode(",",$foruminfo['forumadmin']);
		foreach($forumadmin as $key=>$value){
			if($value){
				$gid=$db->get_one("SELECT groupid FROM pw_members WHERE username='$value'");
				if($gid['groupid']==5 && !ifadmin($value)){
					$db->update("UPDATE pw_members SET groupid='-1' WHERE username='$value'");
				}
			}
		}
	}
	$tids='';
	$ptable_a = array();
	$query=$db->query("SELECT tid,ptable FROM pw_threads WHERE fid='$fid'");
	while($tpc=$db->fetch_array($query)){
		is_numeric($tpc['tid']) && $tids.=$tpc['tid'].',';
		$ptable_a[$tpc['ptable']]=1;
	}
	if($tids){
		$tids=substr($tids,0,-1);
		$db->update("DELETE FROM pw_tmsgs WHERE tid IN($tids)");
	}
	$db->update("DELETE FROM pw_threads WHERE fid='$fid'");
	foreach($ptable_a as $key=>$val){
		$pw_posts=GetPtable($key);
		$db->update("DELETE FROM $pw_posts WHERE fid='$fid'");
	}
	updateforum($foruminfo['fup']);
}
?>