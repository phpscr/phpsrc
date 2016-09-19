<?php
!function_exists('adminmsg') && exit('Forbidden');
require_once(R_P.'require/pw_func.php');

function updatecache($array=''){
	if(!$array){
		updatecache_i();
		if(R_P==D_P){
			updatecache_c();
		}
		updatecache_p();
		updatecache_w();
		updatecache_sy();
		updatecache_g();
		updatecache_bk();
		updatecache_df();
		updatecache_h();
		updatecache_cy();
		updatecache_ol();
		updatecache_mddb();
		updatemedal_list();
		updatecache_ml();

		updatecache_f();
		updatecache_md();
		updatecache_l();
		updatecache_cr();
		updatecache_gr();

		updatecache_advert();
		updatecache_ad();
	} else{
		foreach($array as $value){
			$value();
		}
	}
}

/**
* 更新版块缓冲
*/
function updatecache_f(){
	global $db,$tplpath;
	
	updatecache_fd();
	$db->update("UPDATE pw_forums SET ifsub='0' WHERE type<>'sub'");
	$db->update("UPDATE pw_forums SET ifsub='1' WHERE type='sub'");
	$query=$db->query("SELECT * FROM pw_forums ORDER BY vieworder");
	$catedb=array();
	$forum_cache="\$forum=array(\n";
	while($forum=db_cv($db->fetch_array($query))){
		//$forum['name']=str_replace("'",'&#39;',$forum['name']);
		if($forum['type']=='category'){
			$catedb[]=$forum;
		} elseif($forum['type']=='forum'){
			$forumdb{$forum['fup']} || $subdb1{$forum['fup']}=array();
			$forumdb{$forum['fup']}[]=$forum;
		} else{
			$sub=db_cv($db->get_one("SELECT type FROM pw_forums WHERE fid='$forum[fup]'"));
			if($sub['type']=='forum'){
				$subdb1{$forum['fup']} || $subdb1{$forum['fup']}=array();
				$subdb1{$forum['fup']}[]=$forum;
			} else{
				$subdb2{$forum['fup']} || $subdb2{$forum['fup']}=array();
				$subdb2{$forum['fup']}[]=$forum;
			}
		}
	}
	$forumcache='';
	foreach($catedb as $cate){
		if(!$cate)continue;
		$cate['name']=preg_replace("/\<(.+?)\>/is","",$cate['name']);//去除html标签
		$forum_cache.="'$cate[fid]' => Array(\n\t\t'fid' => '$cate[fid]',\n\t\t'fup'=>'$cate[fup]',\n\t\t'type' => '$cate[type]',\n\t\t'name' => '$cate[name]',\n\t\t'f_type' => '$cate[f_type]',\n\t\t'cms' => '$cate[cms]',\n\t\t'ifhide' => '$cate[ifhide]',\n\t\t),\n";
		if($cate['cms']){
			$cmscache.="<option value=\"$cate[fid]\">>> {$cate[name]}</option>\n";
		} elseif($cate['f_type']!='hidden'){
			$forumcache.="<option value=\"$cate[fid]\">>> {$cate[name]}</option>\n";
		}
		if(!$forumdb{$cate['fid']})continue;
		foreach($forumdb{$cate['fid']} as $forum){
			$forum['name']=preg_replace("/\<(.+?)\>/is","",$forum['name']);
			$forum_cache.="'$forum[fid]' => Array(\n\t\t'fid' => '$forum[fid]',\n\t\t'fup'=>'$forum[fup]',\n\t\t'type' => '$forum[type]',\n\t\t'name' => '$forum[name]',\n\t\t'f_type' => '$forum[f_type]',\n\t\t'cms' => '$forum[cms]',\n\t\t'ifhide' => '$forum[ifhide]',\n\t\t),\n";
			if($forum['cms']){
				$cmscache.="<option value=\"$forum[fid]\"> &nbsp;|- $forum[name]</option>\n";
			} elseif($forum['f_type']!='hidden'){
				$forumcache.="<option value=\"$forum[fid]\"> &nbsp;|- $forum[name]</option>\n";
			}
			if(!$subdb1{$forum['fid']})continue;
			foreach($subdb1{$forum['fid']} as $sub1){
				$sub1['name']=preg_replace("/\<(.+?)\>/is","",$sub1['name']);
				$forum_cache.="'$sub1[fid]' => Array(\n\t\t'fid' => '$sub1[fid]',\n\t\t'fup'=>'$sub1[fup]',\n\t\t'type' => '$sub1[type]',\n\t\t'name' => '$sub1[name]',\n\t\t'f_type' => '$sub1[f_type]',\n\t\t'cms' => '$sub1[cms]',\n\t\t'ifhide' => '$sub1[ifhide]',\n\t\t),\n";			
				if($sub1['cms']){
					$cmscache.="<option value=\"$sub1[fid]\"> &nbsp; &nbsp;|-  $sub1[name]</option>\n";
				} elseif($sub1['f_type']!='hidden'){
					$forumcache.="<option value=\"$sub1[fid]\"> &nbsp; &nbsp;|-  $sub1[name]</option>\n";
				}
				if(!$subdb2{$sub1['fid']})continue;
				foreach($subdb2{$sub1['fid']} as $sub2){
					$sub2['name']=preg_replace("/\<(.+?)\>/is","",$sub2['name']);
					$forum_cache.="'$sub2[fid]' => Array(\n\t\t'fid' => '$sub2[fid]',\n\t\t'fup'=>'$sub2[fup]',\n\t\t'type' => '$sub2[type]',\n\t\t'name' => '$sub2[name]',\n\t\t'f_type' => '$sub2[f_type]',\n\t\t'cms' => '$sub2[cms]',\n\t\t'ifhide' => '$sub2[ifhide]',\n\t\t),\n";			
					if($sub2['cms']){
						$cmscache.="<option value=\"$sub2[fid]\">&nbsp;&nbsp; &nbsp; &nbsp;|-  $sub2[name]</option>\n";
					}elseif($sub2['f_type']!='hidden'){
						$forumcache.="<option value=\"$sub2[fid]\">&nbsp;&nbsp; &nbsp; &nbsp;|-  $sub2[name]</option>\n";
					}
				}
			}
		}
	}
	$forum_cache .= "\n);";
	$forumcache   = "\$forumcache='\n$forumcache';\n\$cmscache='\n$cmscache';";
	writeover(D_P."data/bbscache/forumcache.php","<?php\n".$forumcache."\n?>");
	writeover(D_P.'data/bbscache/forum_cache.php',"<?php\n".$forum_cache."\n?>");
}
function updatecache_fd(){
	global $db;
	$db->update("UPDATE pw_forums SET childid='0'");
	$query=$db->query("SELECT fid,forumadmin FROM pw_forums WHERE type='category' ORDER BY vieworder");
	while($cate=db_cv($db->fetch_array($query))){

		$query2=$db->query("SELECT fid,forumadmin FROM pw_forums WHERE type='forum' AND fup='$cate[fid]'");
		if($db->num_rows($query2)){
			$havechild[]=$cate['fid'];
			while($forum=db_cv($db->fetch_array($query2))){
				if(strpos($forum['forumadmin'],"\t")!==false){
					$forum['forumadmin']=substr($forum['forumadmin'],0,strpos($forum['forumadmin'],"\t"));
				}
				$forumadmin=$forum['forumadmin']."\t".$cate['forumadmin'];
				trim($forumadmin) && $db->update("UPDATE pw_forums SET forumadmin='$forumadmin' WHERE fid='$forum[fid]'");
				
				$query3=$db->query("SELECT fid,forumadmin FROM pw_forums WHERE type='sub' AND fup='$forum[fid]'");
				if($db->num_rows($query3)){
					$havechild[]=$forum['fid'];
					while($subinfo1=db_cv($db->fetch_array($query3))){
						if(strpos($subinfo1['forumadmin'],"\t")!==false){
							$subinfo1['forumadmin']=substr($subinfo1['forumadmin'],0,strpos($subinfo1['forumadmin'],"\t"));
						}
						$forumadmin=$subinfo1['forumadmin']."\t".$forum['forumadmin']."\t".$cate['forumadmin'];
						trim($forumadmin) && $db->update("UPDATE pw_forums SET forumadmin='$forumadmin' WHERE fid='$subinfo1[fid]'");
						$query4=$db->query("SELECT fid,forumadmin FROM pw_forums WHERE type='sub' AND fup='$subinfo1[fid]'");
						if($db->num_rows($query4)){
							$havechild[]=$subinfo1['fid'];
							while($subinfo2=db_cv($db->fetch_array($query4))){
								if(strpos($subinfo2['forumadmin'],"\t")!==false){
									$subinfo2['forumadmin']=substr($subinfo2['forumadmin'],0,strpos($subinfo2['forumadmin'],"\t"));
								}
								$forumadmin=$subinfo2['forumadmin']."\t".$subinfo1['forumadmin']."\t".$forum['forumadmin']."\t".$cate['forumadmin'];
								trim($forumadmin) && $db->update("UPDATE pw_forums SET forumadmin='$forumadmin' WHERE fid='$subinfo2[fid]'");
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


/**
* 更新公告缓冲,更新友情联接缓冲
*/

function updatecache_i(){
	global $db,$db_windpost;
	@include D_P.'data/bbscache/forum_cache.php';
	require_once(R_P.'require/bbscode.php');

	$notice_A=$notice_C=$notice_F='';
	$C_fid=$F_fid=array();
	$num=0;
	$db->update("UPDATE pw_announce SET ffid='0'");
	$query=$db->query("SELECT * FROM pw_announce ORDER BY vieworder,startdate DESC");
	while($notice=db_cv($db->fetch_array($query))){
		if($notice){
			if($notice['fid']=='-1'){
				if($num < 3){
					$num++;
					strlen($notice['subject'])>65 && $notice['subject']=substrs($notice['subject'],65);
					$notice_A.="'$notice[aid]'=>array(\n\t\t'aid'=>'$notice[aid]',\n\t\t'fid'=>'$notice[fid]',\n\t\t'author'=>'$notice[author]',\n\t\t'startdate'=>'$notice[startdate]',\n\t\t'subject'=>'$notice[subject]',\n\t\t),\n\t";
				}
			} elseif(!$forum[$notice['fid']]['cms'] && $forum[$notice['fid']]['type']=='category'){
				if(!in_array($notice['fid'],$C_fid)){
					$C_fid[]=$notice['fid'];
					strlen($notice['subject'])>65 && $notice['subject']=substrs($notice['subject'],65);
					$notice_C.="'$notice[fid]'=>array(\r\n\t\t'aid'=>'$notice[aid]',\r\n\t\t'fid'=>'$notice[fid]',\r\n\t\t'author'=>'$notice[author]',\r\n\t\t'startdate'=>'$notice[startdate]',\r\n\t\t'subject'=>'$notice[subject]',\r\n\t\t),\r\n\t";
				}
			} elseif($notice['fid']!='-2' && ($forum[$notice['fid']]['type'] != 'category' || $forum[$notice['fid']]['cms'])){
				if(!in_array($notice['fid'],$F_fid)){
					$F_fid[]=$notice['fid'];
					$rt=$db->get_one("SELECT content FROM pw_announce WHERE aid='$notice[aid]'");
					$rt['content']=convert($rt['content'],$db_windpost);
					$rt=db_cv($rt);
					$db->update("UPDATE pw_announce SET content='$rt[content]',ffid='$notice[fid]' WHERE aid='$notice[aid]'");
				}
			}
		}
	}
	$sharelink='';$sharelink2='';
	$query=$db->query("SELECT * FROM pw_sharelinks ORDER BY threadorder");
	while(@extract(db_cv($db->fetch_array($query)))){
		if($logo){
			$sharelink.="<a href=\"$url\" target=_blank><img src=\"$logo\" alt=\"$descrip\" width=\"88\" height=\"31\"></a> ";
		}else{
			$sharelink2.="<a href=\"$url\" target=\"_blank\" title=\"$descrip\">[$name]</a> ";
		}
	}
	$sharelink2&&$sharelink=$sharelink2.'<br>'.$sharelink;

	$cache="<?php\r\n\$notice_A=array(\r\n\t$notice_A\r\n\t);\r\n\$sharelink='$sharelink';\r\n?>";
	writeover(D_P.'data/bbscache/index_cache.php',$cache);
	writeover(D_P.'data/bbscache/thread_announce.php',"<?php\r\n\$notice_A=array(\r\n\t$notice_A\r\n\t);\r\n\$notice_C=array(\r\n\t$notice_C\r\n\t);\r\n?>");
}


/**
* 更新用户组缓冲
*/
function updatecache_g($gid='A'){
	global $db;
	if($gid=='A'){
		$query=$db->query("SELECT * FROM pw_usergroups WHERE ifdefault='0' OR gid='1'");
		while($group=db_cv($db->fetch_array($query))){
			updatecache_gp($group);
		}
	}else{
		$group=db_cv($db->get_one("SELECT * FROM pw_usergroups WHERE gid='$gid'"));
		updatecache_gp($group);
	}
}
function updatecache_gp($group){

	$groupcache="<?php\r\n";$sysstart=0;
	foreach($group as $key=>$value){
		if($sysstart==0){
			if($key=='mright'){
				$groupcache.="\$_G=array(\r\n";
				$mright = P_unserialize($value);
				if(is_array($mright)){
					foreach($mright as $key => $value){
						$groupcache.="'$key'=>'$value',\r\n";
					}
				}
				$groupcache.=");\r\n";
			} else{
				$groupcache.="\$gp_$key='$value';\r\n";
			}
		} else{
			if($group['gptype']=='member'||$group['gptype']=='default')break;
			
			
			if($key=='sright'){
				$sright = P_unserialize($value);
				if(is_array($sright)){
					foreach($sright as $key => $value){
						$sysdb.="\t'$key'=>'$value',\r\n";					
					}
				}
			} else{
				$sysdb.="\t'$key'=>'$value',\r\n";
			}
		}
		if($key=='ifdefault')$sysstart=1;
	}
	$sysdb=$sysdb ? "\$SYSTEM=array(\r\n".$sysdb."\t);" :"\r\n\$SYSTEM=array();";
	$groupcache=$groupcache."\r\n".$sysdb."\r\n?>";
	writeover(D_P."data/groupdb/group_$group[gid].php",$groupcache);
}
function updatecache_gr(){
	global $db;
	$gpright = "\$gp_right=array(\r\n";
	$query = $db->query("SELECT gid,mright FROM pw_usergroups ORDER BY gid");
	while($rt = db_cv($db->fetch_array($query))){
		$mright = P_unserialize($rt['mright']);
		if(is_array($mright)){
			$gpright .= "\t'$rt[gid]'=>array('imgwidth'=>'$mright[imgwidth]','imgheight'=>'$mright[imgheight]','fontsize'=>'$mright[fontsize]'),\r\n";
		}
	}
	$gpright .= ");";
	writeover(D_P."data/bbscache/gp_right.php","<?php\r\n".$gpright."\r\n?>");
}
/**
* 更新用户等级缓冲
*/
function updatecache_l(){
	global $db;
	$query=$db->query("SELECT gid,gptype,grouptitle,groupimg,grouppost FROM pw_usergroups ORDER BY grouppost,gid");
	$defaultdb="\$ltitle=\$lpic=\$lneed=array();\r\n/**\r\n* 默认组\r\n*/\r\n";
	$sysdb="\r\n/**\r\n* 管理组\r\n*/\r\n";
	$vipdb="\r\n/**\r\n* 特殊组\r\n*/\r\n";
	$memdb="\r\n/**\r\n* 会员组\r\n*/\r\n";
	while(@extract(db_cv($db->fetch_array($query)))){
		if($gptype=='member'){
			$memdb.="\$ltitle[$gid]='$grouptitle';\t\t\$lpic[$gid]='$groupimg';\t\t\$lneed[$gid]='$grouppost';\r\n";
		}elseif($gptype=='special'){
			$vipdb.="\$ltitle[$gid]='$grouptitle';\t\t\$lpic[$gid]='$groupimg';\r\n";
		}elseif($gptype=='system'){
			$sysdb.="\$ltitle[$gid]='$grouptitle';\t\t\$lpic[$gid]='$groupimg';\r\n";
		}elseif($gptype=='default'){
			$defaultdb.="\$ltitle[$gid]='$grouptitle';\t\t\$lpic[$gid]='$groupimg';\r\n";
		}
	}
	writeover(D_P.'data/bbscache/level.php',"<?php\r\n".$defaultdb.$sysdb.$vipdb.$memdb."\r\n?>");
}
/**
* 更新核心设置组缓冲
*/
function updatecache_c(){
	global $db;
	$query=$db->query("SELECT * FROM pw_config");
	$configdb=$regdb="<?php\r\n";
	while(@extract(db_cv($db->fetch_array($query)))){
		$db_name = key_cv($db_name);
		if ($db_name == 'db_thread' || $db_name == 'db_readad' || $db_name == 'db_article'){
			$db_value  = P_unserialize($db_value);
			if (is_array($db_value)){
				@ksort($db_value);
				$configdb .= "\$$db_name=array(\r\n";
				foreach ($db_value as $key => $value){
					if(is_array($value)){
						$configdb .= "\t'$key' => array(\r\n\t\t'$value[0]',\r\n\t\t'$value[1]',\r\n\t\t'$value[2]'\r\n\t),\r\n";
					}
				}
				$configdb .= ");\r\n";
			}
		} elseif (strpos($db_name,'db_')!==false){
			$db_name=stripslashes($db_name);
			$configdb.="\$$db_name='$db_value';\r\n";
		} elseif (strpos($db_name,'rg_')!==false){
			$regdb.="\$$db_name='$db_value';\r\n";
		}
	}
	$configdb.="?>";
	$regdb.="?>";
	writeover(D_P.'data/bbscache/config.php',$configdb);
	writeover(D_P.'data/bbscache/dbreg.php',$regdb);
}

/**
* 更新风格缓冲
*/
function updatecache_sy($name=''){
	global $db;
	if($name!='')
		$sqlwhere="WHERE name='$name'";
	$query=$db->query("SELECT * FROM pw_styles $sqlwhere");
	while(@extract(db_cv($db->fetch_array($query)))){	
		$stylecontent="<?php
\$stylepath  =     '$stylepath';
\$tplpath  =     '$tplpath';
\$yeyestyle = '$yeyestyle';
\$tablecolor	=	'$tablecolor';//table
\$tablewidth	=	'$tablewidth';
\$mtablewidth=		'$mtablewidth';
\$forumcolorone	=	'$forumcolorone';
\$forumcolortwo	=	'$forumcolortwo';
\$threadcolorone	=	'$threadcolorone';
\$threadcolortwo	=	'$threadcolortwo';
\$readcolorone=	'$readcolorone';
\$readcolortwo=	'$readcolortwo';
\$maincolor =     '$maincolor';
\?>";
		writeover(D_P."data/style/$name.php",str_replace("\?>","?>",$stylecontent));
	}
}
/**
* 更新动作表情缓冲
*/
function updatecache_p(){
	global $db;
	$momtiondb="<?php\n\$motion=array(\n";
	$facedb="\$face=array(\n";
	$query=$db->query("SELECT * FROM pw_actions");
	while(@extract(db_cv($db->fetch_array($query)))){
		$momtiondb.="'$id'=>array(\n\t'$name',\n\t'$descrip',\n\t'$images',\n\t),\n";
	}
	$query=$db->query("SELECT * FROM pw_smiles");
	while(@extract(db_cv($db->fetch_array($query)))){
		$facedb.="\t'$id'=>'$image',\n";
	}
	$momtiondb.=");\n";
	$facedb.=");";
	writeover(D_P."data/bbscache/postcache.php",$momtiondb.$facedb);
}
/**
* 更新禁用词语缓冲
*/
function updatecache_w(){
	global $db;
	$replace = "\$replace=array(\r\n";
	$wordsfb = "\$wordsfb=array(\r\n";
	$query=$db->query("SELECT * FROM pw_wordfb");
	while(@extract(db_cv($db->fetch_array($query)))){
		if($word){
			if($type ==0 ){
				$replace .= "\t'$word'=>'$wordreplace',\r\n";
			} else{
				$wordsfb .= "\t'$word'=>'$wordreplace',\r\n";
			}
		}
	}
	$replace .= ");";
	$wordsfb .= ");";
	writeover(D_P."data/bbscache/wordsfb.php","<?php\r\n".$replace."\r\n".$wordsfb."\r\n?>");
}

/*
* 更新自定义积分数据
*/
function updatecache_cr(){
	global $db;
	$creditdb="\$_CREDITDB=array(\r\n\t\t";
	$query=$db->query("SELECT * FROM  pw_credits");
	while($write=db_cv($db->fetch_array($query))){
		if($write){
			$creditdb.="'".$write['cid']."'=>array('$write[name]','$write[unit]','$write[description]'),\r\n\t\t";
		}
	}
	$creditdb.=");";
	writeover(D_P."data/bbscache/creditdb.php","<?php\r\n".$creditdb."\r\n?>");
}

function updatecache_bk(){
	global $db;
	$query=$db->query("SELECT * FROM pw_hack WHERE hk_name LIKE 'bk_%'");
	$configdb="<?php\r\n";
	while(@extract(db_cv($db->fetch_array($query)))){
		$hk_name = key_cv($hk_name);
		if($hk_name=='bk_A'){
			$hk_value=unserialize($hk_value);
			$bkdb='';
			foreach($hk_value as $key=>$value){
				$bkdb.="'$key'=>array('$value[0]','$value[1]','$value[2]','$value[3]','$value[4]','$value[5]'),\r\n\t\t";
			}
		} else{
			//$hk_value=addslashes($hk_value);
			$configdb.="\$$hk_name='$hk_value';\r\n";
		}
	}
	$configdb.="\$bk_A=array(\r\n\t\t".$bkdb.");\r\n";
	$configdb.="?>";
	writeover(D_P.'data/bbscache/bk_config.php',$configdb);
}
function updatecache_df(){
	global $db;
	$query=$db->query("SELECT * FROM pw_config WHERE db_name LIKE 'df_%'");
	$configdb="<?php\r\n";
	while(@extract(db_cv($db->fetch_array($query)))){
		$db_name = key_cv($db_name);
		if($db_name=='df_cache'){
			$db_value=P_unserialize($db_value);
			if(is_array($db_value)){
				$_cachedb='';
				foreach($db_value as $key=>$value){
					$value[0]  = trim($value[0]);
					$value[1]  = trim($value[1]);
					$_cachedb .= "'$key'=>array('$value[0]','$value[1]'),\r\n\t\t";
				}
			}
		} elseif($db_name=='df_NEW'){
			$db_value=P_unserialize($db_value);
			if(is_array($db_value)){
				$_newdb='';
				foreach($db_value as $value){
					$_newdb.="'$value',";
				}
			}
		} elseif($db_name=='df_CMS'){
			$db_value=P_unserialize($db_value);
			if(is_array($db_value)){
				$_cmsdb='';
				foreach($db_value as $value){
					$_cmsdb.="'$value',";
				}
			}
		}   elseif($db_name=='df_FID'){
			$db_value=P_unserialize($db_value);
			if(is_array($db_value)){
				$_fiddb='';
				foreach($db_value as $value){
					$_fiddb.="'$value',";
				}
			}
		} elseif($db_name=='df_forumlogo'){
			$db_value=P_unserialize($db_value);
			if(is_array($db_value)){
				$_forumlogodb = '';
				foreach($db_value as $key => $value){
					if($value[0]){
						$_forumlogodb .= "'$key'=>array('$value[0]','$value[1]','$value[2]','$value[3]'),\r\n\t\t";
					}
				}
			}
		} else{
			$configdb.="\$$db_name='$db_value';\r\n";
		}
	}
	$configdb.="\r\n\$df_cache=array(\r\n\t\t".$_cachedb.");\r\n";
	$configdb.="\r\n\$df_NEW=array(".$_newdb.");\r\n";
	$configdb.="\r\n\$df_CMS=array(".$_cmsdb.");\r\n";
	$configdb.="\r\n\$df_FID=array(".$_fiddb.");\r\n";
	$configdb.="\r\n\$df_forumlogo=array(\r\n\t\t".$_forumlogodb.");\r\n";
	$configdb.="?>";
	writeover(D_P.'data/bbscache/c_config.php',$configdb);
}
function updatecache_h(){
	include(D_P.'data/sql_config.php');
	$db_hackdb = db_cv($db_hackdb);
	foreach($db_hackdb as $key=>$value){
		$hackdb.="//===========$value[1]===========//\r\n if(\$H_name=='$value[1]'){\r\n\t require_once(R_P.'hack/$value[2]');\r\n}\r\n//===========$value[1]===========//\r\n\r\n";
		$hacksetdb.="//===========$value[1]===========//\r\n if(\$hackset=='$value[1]'){\r\n\t require_once(R_P.'hack/$value[3]');\r\n}\r\n//===========$value[1]===========//\r\n\r\n";
	}
	writeover(D_P."data/hack.php","<?php\r\n!function_exists('readover') && exit('Forbidden');\r\n".$hackdb."?>");
	writeover(D_P."data/hackset.php","<?php\r\n!function_exists('adminmsg') && exit('Forbidden');\r\n".$hacksetdb."?>");
}
function updatecache_cy(){
	global $db;
	$query = $db->query("SELECT * FROM pw_hack WHERE hk_name LIKE 'cn_%'");
	$colonydb = "<?php\r\n";
	while(@extract(db_cv($db->fetch_array($query)))){
		$hk_name = key_cv($hk_name);
		$colonydb .= "\$$hk_name='$hk_value';\r\n";
	}
	$colonydb .= "\n?>";
	writeover(D_P.'data/bbscache/cn_config.php', $colonydb);
}
function updatecache_ol(){
	global $db;
	$onlinedb="<?php\r\n";
	$query = $db->query("SELECT * FROM pw_config WHERE db_name LIKE 'ol_%'");
	while(@extract(db_cv($db->fetch_array($query)))){
		$db_name = key_cv($db_name);
		$onlinedb.="\$$db_name='$db_value';\r\n";
	}
	$onlinedb.="?>";
	writeover(D_P.'data/bbscache/ol_config.php',$onlinedb);
}
function updatecache_md(){
	global $db;
	$medaldb='';
	$query = $db->query("SELECT * FROM pw_hack WHERE hk_name LIKE 'md\_%'");
	while(@extract(db_cv($db->fetch_array($query)))){
		$hk_name = key_cv($hk_name);
		$medaldb.="\$$hk_name='$hk_value';";
	}
	writeover(D_P.'data/bbscache/md_config.php',"<?php\r\n".$medaldb."\r\n?>");
}
function updatecache_mddb(){
	global $db;
	$medaldb="<?php\r\n\$_MEDALDB=array(\r\n";
	$query = $db->query("SELECT * FROM pw_medalinfo ORDER BY id");
	while($rt=db_cv($db->fetch_array($query))){
		$medaldb.="'$rt[id]'=>array(\r\n";
		foreach($rt as $key=>$value){
			$medaldb.="\t'$key'=>'$value',\r\n";
		}
		$medaldb.="),\r\n";
	}
	$medaldb.=");\r\n?>";
	writeover(D_P.'data/bbscache/medaldb.php',$medaldb);
}
function updatemedal_list(){
	global $db;
	$query   = $db->query("SELECT uid,medals FROM pw_members WHERE medals!=''");
	$medaldb = '<?die;?>0';
	while($rt=$db->fetch_array($query)){
		if(str_replace(',','',$rt['medals'])){
			$medaldb .= ','.$rt['uid'];
		}
	}
	writeover(D_P.'data/bbscache/medals_list.php',$medaldb);
}
function updatecache_ml(){
	global $db;
	$maildb	= '';
	$query	= $db->query("SELECT * FROM pw_config WHERE db_name LIKE 'ml\_%'");
	while(@extract(db_cv($db->fetch_array($query)))){
		$db_name = key_cv($db_name);
		$maildb	.= "\$$db_name='$db_value';\r\n";
	}
	writeover(D_P.'data/bbscache/mail_config.php',"<?php\r\n".$maildb."?>");
}
function updatecache_advert(){
	global $db;
	$advertdb	= '';
	$query	= $db->query("SELECT * FROM pw_modules WHERE state=1 ORDER BY vieworder");
	while($rt=db_cv($db->fetch_array($query))){
		$conf = unserialize($rt['config']);
		if($conf['style'] == 'code'){
			$code = $conf['htmlcode'];
		}elseif($conf['style'] == 'txt'){
			$style='';
			if($conf['color']){
				$style .= "color:$conf[color]";
			}
			if($conf['size']){
				$style .= "font-size:$conf[size]";
			}
			if($style){
				$style = "style=\"$style\"";
			}
			$code = "<a href=\"$conf[link]\" target=\"_blank\" $style>$conf[title]</a>";
		}elseif($conf['style'] == 'img'){
			$style='';
			if($conf['width']){
				$style .= "width=\"$conf[width]\"";
			}
			if($conf['height']){
				$style .= "height=\"$conf[height]\"";
			}
			if($conf['descrip']){
				$style .= "alt=\"$conf[descrip]\"";
			}
			$code = "<a href=\"$conf[link]\" target=\"_blank\"><img src=\"$conf[url]\" $style></a>";
		}elseif($conf['style'] == 'flash'){
			$style='';
			if($conf['width']){
				$style .= "width=\"$conf[width]\"";
			}
			if($conf['height']){
				$style .= "height=\"$conf[height]\"";
			}
			$code = "<embed $style src=\"$conf[link]\" type=\"application/x-shockwave-flash\"></embed>";
		}
		$ad['starttime'] = $conf['starttime'];
		$ad['endtime']	 = $conf['endtime'];
		$ad['fid']		 = $conf['fid'];
		$ad['descrip']	 = $conf['descrip'];
		$ad['link']	 = $conf['link'];
		$ad['code']		 = $code;
		$advertdb[$rt['varname']][] = $ad;
	}
	$cachedb = "\$advertdb=".pw_var_export($advertdb).";\r\n";
	writeover(D_P.'data/bbscache/advert_data.php',"<?php\r\n".$cachedb."?>");
}
function updatecache_ad(){
	global $db;
	$maildb	= '';
	$query	= $db->query("SELECT * FROM pw_modules WHERE type=5 AND state=1 ORDER BY vieworder");
	while($rt=db_cv($db->fetch_array($query))){
		$conf = unserialize($rt['config']);
		if($conf['style'] == 'code'){
			$code = $conf['htmlcode'];
		}elseif($conf['style'] == 'txt'){
			$style='';
			if($conf['color']){
				$style .= "color:$conf[color]";
			}
			if($conf['size']){
				$style .= "font-size:$conf[size]";
			}
			if($style){
				$style = "style=\"$style\"";
			}
			$code = "<a href=\"$conf[link]\" target=\"_blank\" $style>$conf[title]</a>";
		}elseif($conf['style'] == 'img'){
			$style='';
			if($conf['width']){
				$style .= "width=\"$conf[width]\"";
			}
			if($conf['height']){
				$style .= "height=\"$conf[height]\"";
			}
			if($conf['descrip']){
				$style .= "alt=\"$conf[descrip]\"";
			}
			$code = "<a href=\"$conf[link]\" target=\"_blank\"><img src=\"$conf[url]\" $style></a>";
		}elseif($conf['style'] == 'flash'){
			$style='';
			if($conf['width']){
				$style .= "width=\"$conf[width]\"";
			}
			if($conf['height']){
				$style .= "height=\"$conf[height]\"";
			}
			$code = "<embed $style src=\"$conf[link]\" type=\"application/x-shockwave-flash\"></embed>";
		}
		$ad['starttime'] = $conf['starttime'];
		$ad['endtime']	 = $conf['endtime'];
		$ad['fid']		 = $conf['fid'];
		$ad['descrip']	 = $conf['descrip'];
		$ad['link']	 = $conf['link'];
		$ad['code']		 = $code;
		$advertdb[$rt['varname']][] = $ad;
	}
	$cachedb = "\$advertdb=".pw_var_export($advertdb).";\r\n";
	writeover(D_P.'data/bbscache/advert_config.php',"<?php\r\n".$cachedb."?>");
}
/*
function cache_read(){
	global $db;
	list($forum_cache,$forumcache) = updatecache_f();
	$medaldb = updatecache_md();
	$leveldb = updatecache_l();
	$creditdb= updatecache_cr();
	$gpright = updatecache_gr();
	$readdb = $forum_cache."\r\n".$forumcache."\r\n".$medaldb."\r\n".$leveldb."\r\n".$creditdb."\r\n".$gpright;
	writeover(D_P.'data/bbscache/cache_read.php',"<?php\r\n".$readdb."\r\n?>");
}*/

function db_cv($array){
	if(is_array($array)){
		foreach($array as $key=>$value){
			$array[$key]=str_replace(array("\\","'"),array("\\\\","\'"),$value);
		}
	}
	return $array;
	
}
function key_cv($key){
	$key = str_replace(
		array(';','\\','/','(',')','$'),
		'',
		$key
	);
	return $key;
}
function pw_var_export($array,$c=1,$t='',$var=''){
	$c && $var="array(\r\n";
	$t.="\t";
	if(is_array($array)){
		foreach($array as $key => $value){
			$var.="$t'".str_replace(array("\\","'"),array("\\\\","\'"),$key)."'=>";
			if(is_array($value)){
				$var.="array(\r\n";
				$var=pw_var_export($value,0,$t,$var);
				$var.="$t),\r\n";
			} else{
				$var.="'".str_replace(array("\\","'"),array("\\\\","\'"),$value)."',\r\n";
			}
		}
	}
	if($c){
		$var.=")";
	}
	return $var;
}
?>