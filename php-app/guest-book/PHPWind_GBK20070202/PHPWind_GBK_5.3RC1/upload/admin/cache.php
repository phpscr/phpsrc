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
		updatecache_inv();
		updatecache_plan();
		updatecache_ftp();
		updatecache_field();
	} else{
		foreach($array as $value){
			$value();
		}
	}
}

function updatecache_f($return=0){
	global $db,$tplpath;
	
	updatecache_fd();
	$db->update("UPDATE pw_forums SET ifsub='0' WHERE type<>'sub'");
	$db->update("UPDATE pw_forums SET ifsub='1' WHERE type='sub'");
	$query=$db->query("SELECT * FROM pw_forums ORDER BY vieworder");
	$catedb=array();
	$forum_cache="\$forum=array(\r\n";
	while($forum=db_cv($db->fetch_array($query))){
		$forum['name'] = preg_replace("/\<(.+?)\>/is","",$forum['name']);//去除html标签
		$forum['name'] = str_replace("<","&lt;",$forum['name']);
		$forum['name'] = str_replace(">","&gt;",$forum['name']);
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
		$forum_cache.="\t'$cate[fid]'=>array(\r\n\t\t'fid'=>'$cate[fid]',\r\n\t\t'fup'=>'$cate[fup]',\r\n\t\t'type' =>'$cate[type]',\r\n\t\t'name'=>'$cate[name]',\r\n\t\t'f_type'=>'$cate[f_type]',\r\n\t\t'cms'=>'$cate[cms]',\r\n\t\t'ifhide'=>'$cate[ifhide]',\r\n\t\t),\r\n";
		if($cate['cms']){
			$cmscache.="<option value=\"$cate[fid]\">>> {$cate[name]}</option>\r\n";
		} elseif($cate['f_type']!='hidden'){
			$forumcache.="<option value=\"$cate[fid]\">>> {$cate[name]}</option>\r\n";
		}
		if(!$forumdb{$cate['fid']})continue;
		foreach($forumdb{$cate['fid']} as $forum){
			$forum_cache.="\t'$forum[fid]'=>array(\r\n\t\t'fid'=>'$forum[fid]',\r\n\t\t'fup'=>'$forum[fup]',\r\n\t\t'type'=>'$forum[type]',\r\n\t\t'name'=>'$forum[name]',\r\n\t\t'f_type'=>'$forum[f_type]',\r\n\t\t'cms'=>'$forum[cms]',\r\n\t\t'ifhide'=>'$forum[ifhide]',\r\n\t\t),\r\n";
			if($forum['cms']){
				$cmscache.="<option value=\"$forum[fid]\"> &nbsp;|- $forum[name]</option>\r\n";
			} elseif($forum['f_type']!='hidden'){
				$forumcache.="<option value=\"$forum[fid]\"> &nbsp;|- $forum[name]</option>\r\n";
			}
			if(!$subdb1{$forum['fid']})continue;
			foreach($subdb1{$forum['fid']} as $sub1){
				$forum_cache.="\t'$sub1[fid]'=>array(\r\n\t\t'fid'=>'$sub1[fid]',\r\n\t\t'fup'=>'$sub1[fup]',\r\n\t\t'type'=>'$sub1[type]',\r\n\t\t'name'=>'$sub1[name]',\r\n\t\t'f_type'=>'$sub1[f_type]',\r\n\t\t'cms'=>'$sub1[cms]',\r\n\t\t'ifhide'=>'$sub1[ifhide]',\r\n\t\t),\r\n";
				if($sub1['cms']){
					$cmscache.="<option value=\"$sub1[fid]\"> &nbsp; &nbsp;|-  $sub1[name]</option>\r\n";
				} elseif($sub1['f_type']!='hidden'){
					$forumcache.="<option value=\"$sub1[fid]\"> &nbsp; &nbsp;|-  $sub1[name]</option>\r\n";
				}
				if(!$subdb2{$sub1['fid']})continue;
				foreach($subdb2{$sub1['fid']} as $sub2){
					$forum_cache.="\t'$sub2[fid]'=>array(\r\n\t\t'fid'=>'$sub2[fid]',\r\n\t\t'fup'=>'$sub2[fup]',\r\n\t\t'type'=>'$sub2[type]',\r\n\t\t'name'=>'$sub2[name]',\r\n\t\t'f_type'=>'$sub2[f_type]',\r\n\t\t'cms'=>'$sub2[cms]',\r\n\t\t'ifhide'=>'$sub2[ifhide]',\r\n\t\t),\r\n";			
					if($sub2['cms']){
						$cmscache.="<option value=\"$sub2[fid]\">&nbsp;&nbsp; &nbsp; &nbsp;|-  $sub2[name]</option>\r\n";
					}elseif($sub2['f_type']!='hidden'){
						$forumcache.="<option value=\"$sub2[fid]\">&nbsp;&nbsp; &nbsp; &nbsp;|-  $sub2[name]</option>\r\n";
					}
				}
			}
		}
	}
	$forum_cache .= "\r\n);";
	$forumcache   = "\$forumcache='\r\n$forumcache';\r\n\$cmscache='\r\n$cmscache';";
	if($return){
		return $forum_cache."\r\n".$forumcache;
	}else{
		writeover(D_P."data/bbscache/forumcache.php","<?php\r\n".$forumcache."\r\n?>");
		writeover(D_P.'data/bbscache/forum_cache.php',"<?php\r\n".$forum_cache."\r\n?>");
		cache_read();
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

/**
* 更新公告缓冲,更新友情联接缓冲
*/
function updatecache_i($return=0){
	global $db,$db_windpost;
	@include(D_P.'data/bbscache/forum_cache.php');
	require_once(R_P.'require/bbscode.php');

	$notice_A=$notice_C=$notice_F='';
	$C_fid=$F_fid=array();
	$num=0;
	$query=$db->query("SELECT * FROM pw_announce WHERE ffid='0' ORDER BY vieworder,startdate DESC");
	while($notice=db_cv($db->fetch_array($query))){
		if($notice){
			if($notice['fid']=='-1'){
				if($num < 5){
					$num++;
					strlen($notice['subject'])>65 && $notice['subject']=substrs($notice['subject'],65);
					$n_add = $notice['url'] ? "\r\n\t\t'url'=>'$notice[url]'," : '';
					$notice_A.="'$notice[aid]'=>array(\r\n\t\t'aid'=>'$notice[aid]',\r\n\t\t'fid'=>'$notice[fid]',\r\n\t\t'author'=>'$notice[author]',\r\n\t\t'startdate'=>'$notice[startdate]',\r\n\t\t'subject'=>'$notice[subject]',$n_add\r\n\t\t),\r\n\t";
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
					$notice['content']=convert(stripslashes($notice['content']),$db_windpost);
					$notice=db_cv($notice);
					$db->pw_update(
						"SELECT aid FROM pw_announce WHERE ffid='$notice[fid]'",
						"UPDATE pw_announce SET author='$notice[author]',startdate='$notice[startdate]',subject='$notice[subject]',content='$notice[content]' WHERE ffid='$notice[fid]'",
						"INSERT INTO pw_announce SET fid='$notice[fid]',author='$notice[author]',startdate='$notice[startdate]',subject='$notice[subject]',ffid='$notice[fid]',content='$notice[content]'"
					);
				}
			}
		}
	}
	if($GLOBALS['action']=='del'){
		$ffids = implode(',',$F_fid);
		$sqladd = $ffids ? " AND ffid NOT IN($ffids)" : '';
		$db->update("DELETE FROM pw_announce WHERE ffid>'0' $sqladd");
	}

	$sharelink1 = '';
	$sharelink  = array();
	$query = $db->query("SELECT * FROM pw_sharelinks ORDER BY threadorder");
	while(@extract(db_cv($db->fetch_array($query)))){
		if($threadorder<0){
			$logo && $logo = "<div class=\"fl\"><a href=\"$url\"><img src=\"$logo\" width=\"88\" height=\"31\" /></a></div>";
			$sharelink[0][]= $logo."<div><a href=\"$url\" target=\"_blank\">$name</a><br />$descrip</div>";
		} else {
			if($logo){
				$sharelink1.="<a href=\"$url\" target=\"_blank\"><img src=\"$logo\" alt=\"$descrip\" width=\"88\" height=\"31\"></a> ";
			} else{
				$sharelink[1].="<a href=\"$url\" target=\"_blank\" title=\"$descrip\">[$name]</a> ";
			}
		}
	}
	$sharelink1 && $sharelink[1] = $sharelink1.'<br />'.$sharelink[1];

	$indexdb="\$notice_A=array(\r\n\t$notice_A\r\n\t);\r\n\$sharelink=".pw_var_export($sharelink).";\r\n";
	$threaddb="\$notice_A=array(\r\n\t$notice_A\r\n\t);\r\n\$notice_C=array(\r\n\t$notice_C\r\n\t);";
	if($return){
		return array($indexdb,$threaddb);
	}else{
		writeover(D_P.'data/bbscache/index_cache.php',"<?php\r\n{$indexdb}\r\n?>");
		writeover(D_P.'data/bbscache/thread_announce.php',"<?php\r\n{$threaddb}\r\n?>");
		cache_read();
	}
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
function updatecache_gr($return=0){
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
	if($return){
		return $gpright;
	}else{
		writeover(D_P."data/bbscache/gp_right.php","<?php\r\n".$gpright."\r\n?>");
		cache_read();
	}
}
/**
* 更新用户等级缓冲
*/
function updatecache_l($return=0){
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
	if($return){
		return $defaultdb.$sysdb.$vipdb.$memdb;
	}else{
		writeover(D_P.'data/bbscache/level.php',"<?php\r\n".$defaultdb.$sysdb.$vipdb.$memdb."\r\n?>");
		cache_read();
	}
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
		} elseif (strpos($db_name,'db_')!==false || strpos($db_name,'passport_')!==false){
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
\$stylepath = '$stylepath';
\$tplpath = '$tplpath';
\$yeyestyle = '$yeyestyle';
\$bgcolor = '$bgcolor';
\$linkcolor = '$linkcolor';
\$tablecolor = '$tablecolor';
\$tdcolor = '$tdcolor';
\$tablewidth = '$tablewidth';
\$mtablewidth = '$mtablewidth';
\$headcolor	= '$headcolor';
\$headborder = '$headborder';
\$headfontone = '$headfontone';
\$headfonttwo = '$headfonttwo';
\$cbgcolor = '$cbgcolor';
\$cbgborder = '$cbgborder';
\$cbgfont = '$cbgfont';
\$forumcolorone	= '$forumcolorone';
\$forumcolortwo	= '$forumcolortwo';
\?>";
		writeover(R_P."data/style/$name.php",str_replace("\?>","?>",$stylecontent));
	}
}
/**
* 更新动作表情缓冲
*/
function updatecache_p($return=0){
	global $db;
	$momtiondb="\$motion=array(\r\n"; //动作
	$faces="\$faces=array(\r\n"; //表情组
	$face="\$face=array(\r\n"; //表情
	$jsface="var face=new Array();\n";
	$jsfaces="var faces=new Array();\n";
	$jsfacedb="var facedb=new Array();\n";

	$query=$db->query("SELECT * FROM pw_actions");
	while(@extract(db_cv($db->fetch_array($query)))){
		$momtiondb.="'$id'=>array(\r\n\t'$name',\r\n\t'$descrip',\r\n\t'$images',\r\n\t),\r\n";
	}
	
	$count=0;
	@extract($db->get_one("SELECT db_value AS fc_shownum FROM pw_config WHERE db_name='fc_shownum'"));
	$rs=$db->query("SELECT * FROM pw_smiles WHERE type=0 ORDER BY vieworder");
	while(@extract(db_cv($db->fetch_array($rs)))){
		if($count==0){
			$jsdefault="var defaultface='$path';\nvar fc_shownum='$fc_shownum';\n\n";
			$count=1;
		}
		$faces.="\t'$path'=>array(\r\n";
		$faces.="\t\t'name'=>'$name',\r\n";
		$faces.="\t\t'child'=>array(";
		$jsfaces.="faces['$path'] = [";
		$jsfacedb.="facedb['$path'] = '$name';\n";
		$query=$db->query("SELECT * FROM pw_smiles WHERE type='$id' ORDER BY vieworder");
		while ($smile=db_cv($db->fetch_array($query))) {
			$face.="\t'$smile[id]'=>'$path/$smile[path]',\r\n";
			$faces.="'$smile[id]',";
			
			$jsface.="face[$smile[id]]='$path/$smile[path]';\n";
			$jsfaces.="$smile[id],";
		}
		$faces.="),\r\n";
		$faces.="\t),\r\n";
		
		$jsfaces.="];\n";
	}

	$momtiondb.=");\r\n";
	$faces.=");\r\n";

	$face.=");";
	if($return){
		return $momtiondb.$faces.$face;
	}else{
		writeover(D_P."data/bbscache/face.js",$jsdefault.$jsfacedb."\n".$jsface."\n".$jsfaces);
		writeover(D_P."data/bbscache/postcache.php","<?php\r\n".$momtiondb.$faces.$face."\r\n?>");
		cache_read();
	}
}
/**
* 更新禁用词语缓冲
*/
function updatecache_w(){
	global $db;
	$replace = "\$replace=array(\r\n";
	$wordsfb = "\$wordsfb=array(\r\n";
	$adddb=array('/','^','$','(',')','.','[',']','|','*','?','+','{');
	$query=$db->query("SELECT * FROM pw_wordfb");
	while(@extract(db_cv($db->fetch_array($query)))){
		if($word){
			foreach($adddb as $key=>$value){
				$word = str_replace($value,"\\$value",$word);
			}
			if($type==0){
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
function updatecache_cr($return=0){
	global $db;
	$creditdb="\$_CREDITDB=array(\r\n\t\t";
	$query=$db->query("SELECT * FROM  pw_credits");
	while($write=db_cv($db->fetch_array($query))){
		if($write){
			$creditdb.="'".$write['cid']."'=>array('$write[name]','$write[unit]','$write[description]'),\r\n\t\t";
		}
	}
	$creditdb.=");";
	if($return){
		return $creditdb;
	}else{
		writeover(D_P."data/bbscache/creditdb.php","<?php\r\n".$creditdb."\r\n?>");
		cache_read();
	}
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
				$bkdb.="'$key'=>array('$value[0]','$value[1]','$value[2]','$value[3]','$value[4]'),\r\n\t\t";
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
		} elseif($db_name=='df_FID'){
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
function updatecache_inv(){
	global $db;
	$query = $db->query("SELECT * FROM pw_hack WHERE hk_name LIKE 'inv_%'");
	$invdb = "<?php\r\n";
	while(@extract(db_cv($db->fetch_array($query)))){
		$hk_name = key_cv($hk_name);
		$invdb .="\$$hk_name='$hk_value';\r\n";
	}
	$invdb .="\n?>";
	writeover(D_P.'data/bbscache/inv_config.php', $invdb);
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
function updatecache_md($return=0){
	global $db;
	$medaldb='';
	$query = $db->query("SELECT * FROM pw_hack WHERE hk_name LIKE 'md\_%'");
	while(@extract(db_cv($db->fetch_array($query)))){
		$hk_name = key_cv($hk_name);
		$medaldb.="\$$hk_name='$hk_value';\r\n";
	}
	if($return){
		return $medaldb;
	}else{
		writeover(D_P.'data/bbscache/md_config.php',"<?php\r\n".$medaldb."\r\n?>");
		cache_read();
	}
}
function updatecache_mddb($return=0){
	global $db;
	$medaldb="\$_MEDALDB=array(\r\n";
	$query = $db->query("SELECT * FROM pw_medalinfo ORDER BY id");
	while($rt=db_cv($db->fetch_array($query))){
		$medaldb.="\t'$rt[id]'=>array(\r\n\t";
		foreach($rt as $key=>$value){
			$medaldb.="\t'$key'=>'$value',\r\n\t";
		}
		$medaldb.="),\r\n";
	}
	$medaldb.=");";
	if($return){
		return $medaldb;
	}else{
		writeover(D_P.'data/bbscache/medaldb.php',"<?php\r\n".$medaldb."\r\n?>");
		cache_read();
	}
}
function updatecache_plan(){
	global $db;
	$plandb="\$plandb=array(\r\n";
	$plantime=array();
	$query = $db->query("SELECT id,month,week,day,hour,nexttime,filename FROM pw_plan WHERE ifopen='1' ORDER BY id");
	while($rt=db_cv($db->fetch_array($query))){
		$plantime[]=$rt['nexttime'];
		$plandb.="\t'$rt[id]'=>array(\r\n\t";
		foreach($rt as $key=>$value){
			$plandb.="\t'$key'=>'$value',\r\n\t";
		}
		$plandb.="),\r\n";
	}
	$plandb.=");";
	writeover(D_P.'data/bbscache/plandb.php',"<?php\r\n".$plandb."\r\n?>");
	rsort($plantime);
	$plantime=array_pop($plantime);
	$db->update("UPDATE pw_bbsinfo SET plantime='$plantime' WHERE id='1'");
}
function updatemedal_list(){
	global $db;
	$query   = $db->query("SELECT uid,medals FROM pw_members WHERE medals!=''");
	$medaldb = '<?die;?>0';
	while($rt=db_cv($db->fetch_array($query))){
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
function updatecache_ftp(){
	global $db;
	$ftpdb	= '';
	$query	= $db->query("SELECT * FROM pw_config WHERE db_name LIKE 'ftp\_%'");
	while(@extract(db_cv($db->fetch_array($query)))){
		$db_name = key_cv($db_name);
		$ftpdb	.= "\$$db_name='$db_value';\r\n";
	}
	writeover(D_P.'data/bbscache/ftp_config.php',"<?php\r\n".$ftpdb."?>");
}
function updatecache_field($return=0){
	global $db;
	$customfield=array();
	$query	= $db->query("SELECT * FROM pw_customfield WHERE state='1' ORDER BY vieworder");
	while($rt=db_cv($db->fetch_array($query))){
		$customfield[]=$rt;
	}
	$cachedb = "\$customfield=".pw_var_export($customfield).";\r\n";
	if($return){
		return $cachedb;
	}else{
		writeover(D_P.'data/bbscache/customfield.php',"<?php\r\n".$cachedb."?>");
		cache_read();
	}
}
function updatecache_advert(){
	global $db;
	$advertdb	= '';
	$query	= $db->query("SELECT * FROM pw_modules WHERE type=6 AND state=1 ORDER BY vieworder");
	while($rt=db_cv($db->fetch_array($query))){
		$conf = unserialize(str_replace(array("\\\\","\'"),array("\\","'"),$rt['config']));
		if($conf['style'] == 'code'){
			$code = $conf['htmlcode'];
		}elseif($conf['style'] == 'txt'){
			$style='';
			if($conf['color']){
				$style .= "color:$conf[color];";
			}
			if($conf['size']){
				$style .= "font-size:$conf[size];";
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
		}elseif($conf['style'] == 'window'){
			$conf['height']= (int) $conf['height'];
			$conf['width'] = (int) $conf['width'];
			$conf['close'] = (int) $conf['close'];
			$code = array($conf['title'],$conf['content'],$conf['height'],$conf['width'],$conf['close']);
		}
		$ad['starttime'] = PwStrtoTime($conf['starttime']);
		$ad['endtime']	 = PwStrtoTime($conf['endtime']);
		$ad['fid']		 = $conf['fid'];
		$ad['descrip']	 = $conf['descrip'];
		$ad['link']	     = $conf['link'];
		$ad['code']		 = $code;
		$advertdb[$rt['varname']][] = $ad;
	}
	$cachedb = "\$advertdb=".pw_var_export($advertdb).";\r\n";
	writeover(D_P.'data/bbscache/advert_data.php',"<?php\r\n".$cachedb."?>");
}
function updatecache_ad(){
	global $db,$db_cp;
	if(!isset($db_cp)) return;
	$maildb	= '';
	$query	= $db->query("SELECT * FROM pw_cmsmodule WHERE type=5 AND state=1 ORDER BY vieworder");
	while($rt=db_cv($db->fetch_array($query))){
		$conf = unserialize($rt['config']);
		if($conf['style'] == 'code'){
			$code = $conf['htmlcode'];
		}elseif($conf['style'] == 'txt'){
			$style='';
			if($conf['color']){
				$style .= "color:$conf[color];";
			}
			if($conf['size']){
				$style .= "font-size:$conf[size];";
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
		$ad['starttime'] = PwStrtoTime($conf['starttime']);
		$ad['endtime']	 = PwStrtoTime($conf['endtime']);
		$ad['fid']		 = $conf['fid'];
		$ad['descrip']	 = $conf['descrip'];
		$ad['link']	     = $conf['link'];
		$ad['code']		 = $code;
		$advertdb[$rt['varname']][] = $ad;
	}
	$cachedb = "\$advertdb=".pw_var_export($advertdb).";\r\n";
	writeover(D_P.'data/bbscache/advert_config.php',"<?php\r\n".$cachedb."?>");
}
function cache_read(){
	global $db;
	if(!defined('CT')){
		define('CT',1);
		$fdb	= updatecache_f(1)."\r\n\r\n";
		$mddb	= updatecache_md(1)."\r\n\r\n";
		$ldb	= updatecache_l(1)."\r\n\r\n";
		$crdb	= updatecache_cr(1)."\r\n\r\n";
		$grdb	= updatecache_gr(1)."\r\n\r\n";
		$fielddb= updatecache_field(1)."\r\n\r\n";
		$mddbdb	= updatecache_mddb(1)."\r\n\r\n";
		$pdb	= updatecache_p(1)."\r\n\r\n";
		list($indexdb,$threaddb)=updatecache_i(1);

		writeover(D_P.'data/bbscache/cache_index.php',"<?php\r\n{$ldb}{$indexdb}\r\n?>");
		writeover(D_P.'data/bbscache/cache_thread.php',"<?php\r\n{$fdb}{$threaddb}\r\n?>");	writeover(D_P.'data/bbscache/cache_read.php',"<?php\r\n{$fdb}{$mddb}{$ldb}{$crdb}{$grdb}{$fielddb}{$mddbdb}?>");
		writeover(D_P.'data/bbscache/cache_post.php',"<?php\r\n{$fdb}{$ldb}{$pdb}?>");
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
function creatcolor($color){
	substr($color,0,1)=='#' && $color = substr($color,1);
	$len = strlen($color);
	if($len==6 || $len==3){
		$step = $len / 3;
		for($i=0;$i<3;$i++){
			$c[$i] = substr($color,$i*$step,$step);
			$step == 1 && $c[$i] .= $c[$i];
			$c[$i] = hexdec($c[$i]) - 6;
			$c[$i] < 0 && $c[$i] = 0;
			$c[$i] = $c[$i] < 16 ? '0'.dechex($c[$i]) : dechex($c[$i]);
		}
		return '#'.$c[0].$c[1].$c[2];
	} else{
		return '#'.$color;
	}
}
?>