<?php
!function_exists('readover') && exit('Forbidden');
require_once(D_P.'data/bbscache/level.php');
require_once(R_P.'require/updateforum.php');
require_once(R_P.'require/tool.php');

!$db_toolifopen && Showmsg('toolcenter_close');
!$windid && Showmsg('not_login');

include_once(D_P.'data/bbscache/creditdb.php');
list($db_moneyname,$db_moneyunit,$db_rvrcname,$db_rvrcunit,$db_creditname,$db_creditunit)=explode("\t",$db_credits);
$userdb   = $db->get_one("SELECT postnum,digests,rvrc,money,credit,currency FROM pw_memberdata WHERE uid='$winduid'");

if(!$action){
	$query    = $db->query("SELECT * FROM pw_tools WHERE state=1 ORDER BY vieworder");
	while($rt = $db->fetch_array($query)){
		$rt['descrip'] = substrs($rt['descrip'],30);
		$tooldb[] = $rt;
	}
	require_once PrintHack('index');footer();
} elseif($action == 'mytool'){
	$query    = $db->query("SELECT u.*,t.name,t.price,t.stock FROM pw_usertool u LEFT JOIN pw_tools t ON t.id=u.toolid WHERE u.uid='$winduid'");
	while($rt = $db->fetch_array($query)){
		$tooldb[] = $rt;
	}
	require_once PrintHack('index');footer();
} elseif($action == 'user'){
	!$db_allowtrade && Showmsg('trade_close');
	if(is_numeric($uid)){
		$rt = $db->get_one("SELECT username FROM pw_members WHERE uid='$uid'");
		if(!$rt){
			Showmsg('user_not_exists');
		}
		$sqladd = "AND u.uid='$uid'";
		$owner  = $rt['username'];
	}else{
		$sqladd = $owner = '';
	}
	$query  = $db->query("SELECT u.*,t.name,t.descrip,t.logo,m.username FROM pw_usertool u LEFT JOIN pw_members m USING(uid) LEFT JOIN pw_tools t ON t.id=u.toolid WHERE sellnums!=0 $sqladd");
	while($rt = $db->fetch_array($query)){
		$rt['descrip'] = substrs($rt['descrip'],45);
		$tooldb[]      = $rt;
	}
	require_once PrintHack('index');footer();
} elseif($action == 'sell'){
	!$db_allowtrade && Showmsg('trade_close');
	if(!$step){
		$rt = $db->get_one("SELECT u.*,t.name,t.price,t.logo FROM pw_usertool u LEFT JOIN pw_tools t ON t.id=u.toolid WHERE uid='$winduid' AND toolid='$id'");
		!$rt && Showmsg('undefined_action');
		$rt['nums'] == 0 && Showmsg('unenough_toolnum');
		require_once PrintHack('index');footer();
	} else{
		$rt = $db->get_one("SELECT u.*,t.name FROM pw_usertool u LEFT JOIN pw_tools t ON t.id=u.toolid WHERE uid='$winduid' AND toolid='$id'");
		if($rt){
			$nums = (int)$nums;
			$price <= 0 && Showmsg('illegal_nums');
			$nums <= 0  && Showmsg('illegal_nums');
			$rt['nums'] < $nums && Showmsg('unenough_nums');

			$db->update("UPDATE pw_usertool SET nums=nums-'$nums',sellnums=sellnums+'$nums',sellprice='$price' WHERE uid='$winduid' AND toolid='$id'");
			$logdata = array(
				'type'		=>	'sell',
				'nums'		=>	$nums,
				'money'		=>	$price,
				'descrip'	=>	'sell_descrip',
				'uid'		=>	$winduid,
				'username'	=>	$windid,
				'ip'		=>	$onlineip,
				'time'		=>	$timestamp,
				'toolname'	=>	$rt['name'],
				'from'		=>	'',
			);
			writetoollog($logdata);
			refreshto("hack.php?H_name=toolcenter&action=mytool",'operate_success');
		} else{
			Showmsg('undefined_action');
		}
	}
} elseif($action == 'buyuser'){
	if(!$step){
		$rt = $db->get_one("SELECT * FROM pw_usertool u LEFT JOIN pw_tools t ON t.id=u.toolid WHERE u.toolid='$id' && u.uid='$uid'");
		if($rt){
			$condition = unserialize($rt['conditions']);
			$groupids  = $condition['group'];
			$fids      = $condition['forum'];

			foreach($condition[credit] as $key => $value){
				$key == 'rvrc' && $value /= 10;
				$condition['credit'][$key] = (int)$value;
			}
			include_once(D_P."data/bbscache/creditdb.php");
			$usergroup="";
			$num = 0;
			foreach($ltitle as $key=>$value){
				if($key != 1 && $key != 2){
					if(strpos($groupids,','.$key.',') !== false){
						$num++;
						$htm_tr = $num%5 == 0 ?  '</tr><tr>' : '';
						$usergroup .=" <td width='20%'>$value</td>$htm_tr";
					}
				}
			}

			$num        = 0;
			$forumcheck = "";
			$sqladd     = " AND f_type!='hidden' AND cms='0'";
			$query      = $db->query("SELECT fid,name FROM pw_forums WHERE type<>'category' AND cms='0'");
			while($fm = $db->fetch_array($query)){
				if(strpos($fids,','.$fm['fid'].',') !== false){
					$num ++;
					$htm_tr = $num % 5 == 0 ? '</tr><tr>' : '';
					$forumcheck .= "<td width='20%'>$fm[name]</td>$htm_tr";
				}
			}
			require_once PrintHack('index');footer();
		} else{
			Showmsg('undefined_action');
		}
	} else{
		$toolinfo = $db->get_one("SELECT u.*,t.name,m.username FROM pw_usertool u LEFT JOIN pw_members m USING(uid) LEFT JOIN pw_tools t ON t.id=u.toolid WHERE u.toolid='$id' && u.uid='$uid'");
		$userinfo = $db->get_one("SELECT currency FROM pw_memberdata WHERE uid='$winduid'");

		$nums  = (int)$nums;
		$nums <= 0 && Showmsg('illegal_nums');
		$price = $toolinfo['sellprice'] * $nums;
		$toolinfo['sellnums'] < $nums && Showmsg('unenough_sellnum');

		if ($winduid == $toolinfo['uid']){
			$logdata=array(
				'type'		=>	'buy',
				'nums'		=>	$nums,
				'money'		=>	$price,
				'descrip'	=>	'buyself_descrip',
				'uid'		=>	$winduid,
				'username'	=>	$windid,
				'ip'		=>	$onlineip,
				'time'		=>	$timestamp,
				'toolname'	=>	$toolinfo['name'],
				'from'		=>	'',
			);
			writetoollog($logdata);
			$db->update("UPDATE pw_usertool SET nums=nums+'$nums',sellnums=sellnums-'$nums' WHERE uid='$toolinfo[uid]' AND toolid='$id'");
		} else {
			if($userinfo['currency'] < $price){
				Showmsg('unenough_money');
			}
			$db->update("UPDATE pw_memberdata SET currency=currency-'$price' WHERE uid='$winduid'");
			$db->update("UPDATE pw_memberdata SET currency=currency+'$price' WHERE uid='$toolinfo[uid]'");
			$db->pw_update(
				"SELECT uid FROM pw_usertool WHERE uid='$winduid' AND toolid='$id'",
				"UPDATE pw_usertool SET nums=nums+'$nums' WHERE uid='$winduid' AND toolid='$id'",
				"INSERT INTO pw_usertool SET nums='$nums',uid='$winduid',toolid='$id'"
			);
			$db->update("UPDATE pw_usertool SET sellnums=sellnums-'$nums' WHERE uid='$toolinfo[uid]' AND toolid='$id'");
			$logdata=array(
				'type'		=>	'buy',
				'nums'		=>	$nums,
				'money'		=>	$price,
				'descrip'	=>	'buyuser_descrip',
				'uid'		=>	$winduid,
				'username'	=>	$windid,
				'ip'		=>	$onlineip,
				'time'		=>	$timestamp,
				'toolname'	=>	$toolinfo['name'],
				'from'		=>	$toolinfo['username'],
			);
			writetoollog($logdata);
		}
		refreshto("hack.php?H_name=toolcenter&action=user",'operate_success');
	}
} elseif($action == 'buy'){
	if(!$step){
		$rt = $db->get_one("SELECT * FROM pw_tools WHERE id='$id'");
		if($rt){
			$rt['stock'] == 0 && Showmsg('no_stock');
			$condition = unserialize($rt['conditions']);
			$groupids  = $condition['group'];
			$fids      = $condition['forum'];

			foreach($condition[credit] as $key => $value){
				$key == 'rvrc' && $value /= 10;
				$condition['credit'][$key] = (int)$value;
			}

			include_once(D_P."data/bbscache/creditdb.php");
			$usergroup="";
			$num = 0;
			foreach($ltitle as $key=>$value){
				if($key != 1 && $key != 2){
					if(strpos($groupids,','.$key.',') !== false){
						$num ++;
						$htm_tr = $num%5 == 0 ?  '</tr><tr>' : '';
						$usergroup .=" <td width='20%'>$value</td>$htm_tr";
					}
				}
			}

			$num        = 0;
			$forumcheck = "<table cellspacing='0' cellpadding='0' border='0' width='100%' align='center'><tr>";
			$sqladd     = " AND f_type!='hidden' AND cms='0'";
			$query      = $db->query("SELECT fid,name FROM pw_forums WHERE type<>'category' AND cms='0'");
			while($fm = $db->fetch_array($query)){
				if(strpos($fids,','.$fm['fid'].',') !== false){
					$num ++;
					$htm_tr = $num % 5 == 0 ? '</tr><tr>' : '';
					$forumcheck .= "<td width='20%'>$fm[name]</td>$htm_tr";
				}
			}
			$forumcheck.="</tr></table>";
			require_once PrintHack('index');footer();
		} else{
			Showmsg('undefined_action');
		}
	} else{
		$toolinfo = $db->get_one("SELECT * FROM pw_tools WHERE id='$id'");
		$userinfo = $db->get_one("SELECT currency FROM pw_memberdata WHERE uid='$winduid'");

		$nums  = (int)$nums;
		$nums <= 0 && Showmsg('illegal_nums');
		$price = $toolinfo['price'] * $nums;
		$toolinfo['stock'] < $nums && Showmsg('unenough_stock');
		if($userinfo['currency'] < $price){
			Showmsg('unenough_money');
		}
		$db->update("UPDATE pw_memberdata SET currency=currency-'$price' WHERE uid='$winduid'");
		$db->update("UPDATE pw_tools SET stock=stock-'$nums' WHERE id='$id'");
		$db->pw_update(
			"SELECT uid FROM pw_usertool WHERE uid='$winduid' AND toolid='$id'",
			"UPDATE pw_usertool SET nums=nums+'$nums' WHERE uid='$winduid' AND toolid='$id'",
			"INSERT INTO pw_usertool SET nums='$nums',uid='$winduid',toolid='$id'"
		);
		$logdata=array(
			'type'		=>	'buy',
			'nums'		=>	$nums,
			'money'		=>	$price,
			'descrip'	=>	'buy_descrip',
			'uid'		=>	$winduid,
			'username'	=>	$windid,
			'ip'		=>	$onlineip,
			'time'		=>	$timestamp,
			'toolname'	=>	$toolinfo['name'],
			'from'		=>	'',
		);
		writetoollog($logdata);
		refreshto("hack.php?H_name=toolcenter",'operate_success');
	}
} elseif($action == 'use'){
	$tid    = (int) $tid;
	$toolid = (int) $toolid;
	if(!$toolid){
		$tooldb = array();
		$query    = $db->query("SELECT * FROM pw_usertool u LEFT JOIN pw_tools t ON t.id=u.toolid WHERE u.uid='$winduid' ORDER BY vieworder");
		while($rt = $db->fetch_array($query)){
			$rt['descrip'] = substrs($rt['descrip'],45);
			$tooldb[] = $rt;
		}
		if(!$tooldb){
			Showmsg('no_tool');
		}
		require_once PrintHack('index');footer();
	}
	$tooldb = $db->get_one("SELECT u.nums,t.name,t.state,t.conditions FROM pw_usertool u LEFT JOIN pw_tools t ON t.id=u.toolid WHERE u.uid='$winduid' AND u.toolid='$toolid'");

	!$db_toolifopen && Showmsg('toolcenter_close');
	if(!$tooldb || $tooldb['nums'] <= 0){
		Showmsg('nothistool');
	}
	CheckUserTool($winduid,$tooldb);
	if($tid){
		$condition = unserialize($tooldb['conditions']);
		$tpcdb = $db->get_one("SELECT fid,subject,authorid,topped FROM pw_threads WHERE tid='$tid'");
		if(!$tpcdb){
			Showmsg('illegal_tid');
		}
		if($tpcdb['authorid'] != $winduid){
			Showmsg('tool_authorlimit');
		}elseif ($condition['forum'] && strpos($condition['forum'],",$tpcdb[fid],") === false){
			Showmsg('tool_forumlimit');
		}
	}

	if($toolid == 1){
		$rt = $db->get_one("SELECT rvrc FROM pw_memberdata WHERE uid='$winduid'");
		if($rt['rvrc'] < 0){
			$db->update("UPDATE pw_memberdata SET rvrc=0 WHERE uid='$winduid'");
			$db->update("UPDATE pw_usertool SET nums=nums-1 WHERE uid='$winduid' AND toolid='$toolid'");
			$logdata=array(
				'type'		=>	'use',
				'nums'		=>	'',
				'money'		=>	'',
				'descrip'	=>	'tool_1_descrip',
				'uid'		=>	$winduid,
				'username'	=>	$windid,
				'ip'		=>	$onlineip,
				'time'		=>	$timestamp,
				'toolname'	=>	$tooldb['name'],
				'from'		=>	'',
			);
			writetoollog($logdata);
			list(,,$db_rvrcname,)=explode("\t",$db_credits);
			Showmsg('toolmsg_1_success');
		} else{
			list(,,$db_rvrcname,)=explode("\t",$db_credits);
			Showmsg('toolmsg_1_failed');
		}
	} elseif($toolid == 2){
		$rt = $db->get_one("SELECT postnum,digests,rvrc,money,credit FROM pw_memberdata WHERE uid='$winduid'");
		$sqladd = '';
		if($rt['postnum'] < 0){
			$sqladd = "postnum=0";
		}
		if($rt['digests'] < 0){
			$sqladd .= $sqladd ? ",digests=0" : "digests=0";
		}
		if($rt['rvrc'] < 0){
			$sqladd .= $sqladd ? ",rvrc=0" : "rvrc=0";
		}
		if($rt['money'] < 0){
			$sqladd .= $sqladd ? ",money=0" : "money=0";
		}
		if($rt['credit'] < 0){
			$sqladd .= $sqladd ? ",credit=0" : "credit=0";
		}
		if ($sqladd){
			$db->update("UPDATE pw_memberdata SET $sqladd WHERE uid='$winduid'");
			$db->update("UPDATE pw_usertool SET nums=nums-1 WHERE uid='$winduid' AND toolid='$toolid'");
			$logdata=array(
				'type'		=>	'use',
				'nums'		=>	'',
				'money'		=>	'',
				'descrip'	=>	'tool_2_descrip',
				'uid'		=>	$winduid,
				'username'	=>	$windid,
				'ip'		=>	$onlineip,
				'time'		=>	$timestamp,
				'toolname'	=>	$tooldb['name'],
			);
			writetoollog($logdata);
			Showmsg('toolmsg_2_success');
		} else{
			Showmsg('toolmsg_2_failed');
		}
	} elseif($toolid == 3){
		!$tid && Showmsg('tool_error');
		if(!$step){
			require_once PrintHack('index');footer();
		} else{
			$titlefont = Char_cv("$title1~$title2~$title3~$title4~$title5~$title6~");
			$db->update("UPDATE pw_threads SET titlefont='$titlefont',toolinfo='$tooldb[name]' WHERE tid='$tid'");
			$db->update("UPDATE pw_usertool SET nums=nums-1 WHERE uid='$winduid' AND toolid='$toolid'");
			$logdata=array(
				'type'		=>	'use',
				'nums'		=>	'',
				'money'		=>	'',
				'descrip'	=>	'tool_3_descrip',
				'uid'		=>	$winduid,
				'username'	=>	$windid,
				'ip'		=>	$onlineip,
				'time'		=>	$timestamp,
				'toolname'	=>	$tooldb['name'],
				'subject'	=>	substrs($tpcdb['subject'],15),
				'tid'		=>	$tid,
			);
			writetoollog($logdata);
			refreshto("thread.php?fid=$tpcdb[fid]",'operate_success');
		}
	} elseif($toolid == 4){
		!$tid && Showmsg('tool_error');
		if($tpcdb['topped'] != 0){
			Showmsg('toolmsg_4_failed');
		}
		$toolfield = $timestamp + 3600*6;
		$db->update("UPDATE pw_threads SET topped='1',toolinfo='$tooldb[name]',toolfield='$toolfield' WHERE tid='$tid'");

		$db->update("UPDATE pw_usertool SET nums=nums-1 WHERE uid='$winduid' AND toolid='$toolid'");
		$logdata=array(
			'type'		=>	'use',
			'nums'		=>	'',
			'money'		=>	'',
			'descrip'	=>	'tool_4_descrip',
			'uid'		=>	$winduid,
			'username'	=>	$windid,
			'ip'		=>	$onlineip,
			'time'		=>	$timestamp,
			'toolname'	=>	$tooldb['name'],
			'subject'	=>	substrs($tpcdb['subject'],15),
			'tid'		=>	$tid,
		);
		writetoollog($logdata);
		refreshto("thread.php?fid=$tpcdb[fid]",'operate_success');
	} elseif($toolid == 5){
		!$tid && Showmsg('tool_error');
		if($tpcdb['topped'] > 1){
			Showmsg('toolmsg_5_failed');
		}		
		
		$toolfield = $timestamp + 3600*6;
		$db->update("UPDATE pw_threads SET topped='2',toolinfo='$tooldb[name]',toolfield='$toolfield' WHERE tid='$tid'");
		$db->update("UPDATE pw_usertool SET nums=nums-1 WHERE uid='$winduid' AND toolid='$toolid'");
		$logdata=array(
			'type'		=>	'use',
			'nums'		=>	'',
			'money'		=>	'',
			'descrip'	=>	'tool_5_descrip',
			'uid'		=>	$winduid,
			'username'	=>	$windid,
			'ip'		=>	$onlineip,
			'time'		=>	$timestamp,
			'toolname'	=>	$tooldb['name'],
			'subject'	=>	substrs($tpcdb['subject'],15),
			'tid'		=>	$tid,
		);
		updatetop();
		writetoollog($logdata);
		
		refreshto("thread.php?fid=$tpcdb[fid]",'operate_success');
	} elseif($toolid == 6){
		!$tid && Showmsg('tool_error');
		if($tpcdb['topped'] == 3){
			Showmsg('toolmsg_6_failed');
		}
		
		$toolfield = $timestamp + 3600*6;
		$db->update("UPDATE pw_threads SET topped='3',toolinfo='$tooldb[name]',toolfield='$toolfield' WHERE tid='$tid'");
		$db->update("UPDATE pw_usertool SET nums=nums-1 WHERE uid='$winduid' AND toolid='$toolid'");
		$logdata=array(
			'type'		=>	'use',
			'nums'		=>	'',
			'money'		=>	'',
			'descrip'	=>	'tool_6_descrip',
			'uid'		=>	$winduid,
			'username'	=>	$windid,
			'ip'		=>	$onlineip,
			'time'		=>	$timestamp,
			'toolname'	=>	$tooldb['name'],
			'subject'	=>	substrs($tpcdb['subject'],15),
			'tid'		=>	$tid,
		);
		updatetop();
		writetoollog($logdata);

		refreshto("thread.php?fid=$tpcdb[fid]",'operate_success');
	} elseif($toolid == 7){
		!$tid && Showmsg('tool_error');
		$db->update("UPDATE pw_threads SET lastpost='$timestamp',toolinfo='$tooldb[name]' WHERE tid='$tid'");
		$db->update("UPDATE pw_usertool SET nums=nums-1 WHERE uid='$winduid' AND toolid='$toolid'");
		$logdata=array(
			'type'		=>	'use',
			'nums'		=>	'',
			'money'		=>	'',
			'descrip'	=>	'tool_7_descrip',
			'uid'		=>	$winduid,
			'username'	=>	$windid,
			'ip'		=>	$onlineip,
			'time'		=>	$timestamp,
			'toolname'	=>	$tooldb['name'],
			'subject'	=>	substrs($tpcdb['subject'],15),
			'tid'		=>	$tid,
		);
		writetoollog($logdata);
		refreshto("thread.php?fid=$tpcdb[fid]",'operate_success');
	} elseif($toolid == 8){
		if(!$step){
			require_once PrintHack('index');footer();
		} else{
			include_once(D_P."data/bbscache/dbreg.php");
			!$pwuser && Showmsg('username_empty');
			if (strlen($pwuser)>$rg_regmaxname || strlen($pwuser)<$rg_regminname){
				Showmsg('reg_username_limit');
			}
			$S_key=array('&',' ',"'",'"','/','*',',','<','>',"\r","\t","\n",'#');
			foreach($S_key as $value){
				if (strpos($pwuser,$value)!==false){ 
					Showmsg('illegal_username'); 
				}
			}
			if(!$rg_rglower){
				for ($asc=65;$asc<=90;$asc++){ 
					if (strpos($pwuser,chr($asc))!==false){
						Showmsg('username_limit'); 
					} 
				}
			}
			$pwuser = Char_cv($pwuser);
			$pwuser=='guest' && Showmsg('illegal_username');
			$rg_banname=explode(',',$rg_banname);
			foreach($rg_banname as $value){
				if(strpos($pwuser,$value)!==false){
					Showmsg('illegal_username');
				}
			}

			$rt = $db->get_one("SELECT uid FROM pw_members WHERE username='$pwuser'");
			if($rt['uid']) {
				Showmsg('username_same'); 
			}
			$db->update("UPDATE pw_members SET username='$pwuser' WHERE uid='$winduid'");
			$db->update("UPDATE pw_threads SET author='$pwuser' WHERE authorid='$winduid'");
			$ptable_a=array('pw_posts');
			if($db_plist){
				$p_list=explode(',',$db_plist);
				foreach($p_list as $val){
					$ptable_a[]='pw_posts'.$val;
				}
			}
			foreach($ptable_a as $val){
				$db->update("UPDATE $val SET author='$pwuser' WHERE authorid='$winduid'");
			}
			$db->update("UPDATE pw_cmembers SET username='$pwuser' WHERE uid='$winduid'");
			$db->update("UPDATE pw_argument SET author='$pwuser' WHERE authorid='$winduid'");
			$db->update("UPDATE pw_colonys SET admin='$pwuser' WHERE admin='$windid'");
			$db->update("UPDATE pw_announce SET author='$pwuser' WHERE author='$windid'");

			$query = $db->query("SELECT fid,forumadmin,fupadmin FROM pw_forums WHERE forumadmin LIKE '%,".addslashes($windid).",%' OR fupadmin LIKE '%,".addslashes($windid).",%'");
			while($rt = $db->fetch_array($query)){
				$rt['forumadmin']	= str_replace(",$windid,",",$pwuser,",$rt['forumadmin']);
				$rt['fupadmin']		= str_replace(",$windid,",",$pwuser,",$rt['fupadmin']);
				$db->update("UPDATE pw_forums SET forumadmin='".addslashes($rt['forumadmin'])."',fupadmin='".addslashes($rt['fupadmin'])."' WHERE fid='$rt[fid]'");
			}

			$db->update("UPDATE pw_usertool SET nums=nums-1 WHERE uid='$winduid' AND toolid='$toolid'");
			$logdata=array(
				'type'		=>	'use',
				'nums'		=>	'',
				'money'		=>	'',
				'descrip'	=>	'tool_8_descrip',
				'uid'		=>	$winduid,
				'username'	=>	$windid,
				'ip'		=>	$onlineip,
				'time'		=>	$timestamp,
				'toolname'	=>	$tooldb['name'],
				'newname'	=>	$pwuser,
				'tid'		=>	$tid,
			);
			writetoollog($logdata);
			Showmsg('toolmsg_8_success');
		}
	} elseif($toolid == 9){
		!$tid && Showmsg('tool_error');
		$db->update("UPDATE pw_threads SET digest='1',toolinfo='$tooldb[name]' WHERE tid='$tid'");
		$db->update("UPDATE pw_memberdata SET digests=digests+1 WHERE uid='$winduid'");
		$db->update("UPDATE pw_usertool SET nums=nums-1 WHERE uid='$winduid' AND toolid='$toolid'");
		$logdata=array(
			'type'		=>	'use',
			'descrip'	=>	'tool_9_descrip',
			'uid'		=>	$winduid,
			'username'	=>	$windid,
			'ip'		=>	$onlineip,
			'time'		=>	$timestamp,
			'toolname'	=>	$tooldb['name'],
			'subject'	=>	substrs($tpcdb['subject'],15),
			'tid'		=>	$tid,
		);
		writetoollog($logdata);
		refreshto("thread.php?fid=$tpcdb[fid]",'operate_success');
	} elseif($toolid == 10){
		!$tid && Showmsg('tool_error');
		$db->update("UPDATE pw_threads SET digest='2',toolinfo='$tooldb[name]' WHERE tid='$tid'");
		$db->update("UPDATE pw_memberdata SET digests=digests+1 WHERE uid='$winduid'");
		$db->update("UPDATE pw_usertool SET nums=nums-1 WHERE uid='$winduid' AND toolid='$toolid'");
		$logdata=array(
			'type'		=>	'use',
			'descrip'	=>	'tool_10_descrip',
			'uid'		=>	$winduid,
			'username'	=>	$windid,
			'ip'		=>	$onlineip,
			'time'		=>	$timestamp,
			'toolname'	=>	$tooldb['name'],
			'subject'	=>	substrs($tpcdb['subject'],15),
			'tid'		=>	$tid,
		);
		writetoollog($logdata);
		refreshto("thread.php?fid=$tpcdb[fid]",'operate_success');
	} elseif($toolid == 11){
		!$tid && Showmsg('tool_error');
		$db->update("UPDATE pw_threads SET locked='1',toolinfo='$tooldb[name]' WHERE tid='$tid'");
		$db->update("UPDATE pw_usertool SET nums=nums-1 WHERE uid='$winduid' AND toolid='$toolid'");
		$logdata=array(
			'type'		=>	'use',
			'descrip'	=>	'tool_11_descrip',
			'uid'		=>	$winduid,
			'username'	=>	$windid,
			'ip'		=>	$onlineip,
			'time'		=>	$timestamp,
			'toolname'	=>	$tooldb['name'],
			'subject'	=>	substrs($tpcdb['subject'],15),
			'tid'		=>	$tid,
		);
		writetoollog($logdata);
		refreshto("thread.php?fid=$tpcdb[fid]",'operate_success');
	} elseif($toolid == 12){
		!$tid && Showmsg('tool_error');
		$db->update("UPDATE pw_threads SET locked='0',toolinfo='$tooldb[name]' WHERE tid='$tid'");
		$db->update("UPDATE pw_usertool SET nums=nums-1 WHERE uid='$winduid' AND toolid='$toolid'");
		$logdata=array(
			'type'		=>	'use',
			'descrip'	=>	'tool_12_descrip',
			'uid'		=>	$winduid,
			'username'	=>	$windid,
			'ip'		=>	$onlineip,
			'time'		=>	$timestamp,
			'toolname'	=>	$tooldb['name'],
			'subject'	=>	substrs($tpcdb['subject'],15),
			'tid'		=>	$tid,
		);
		writetoollog($logdata);
		refreshto("thread.php?fid=$tpcdb[fid]",'operate_success');
	}
}
?>