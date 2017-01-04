<?php
!function_exists('readover') && exit('Forbidden');

function wind_forumcheck($forum){
	global $windid,$groupid,$tid,$fid,$skin,$winddb,$manager;

	if($forum['f_type']=='former' && $groupid=='guest' && $_COOKIE){
		Showmsg('forum_former');
	}
	if(!empty($forum['style']) && file_exists(R_P."data/style/$forum[style].php")){
		$skin=$forum['style'];
	}
	$pwdcheck=GetCookie('pwdcheck');
	if($forum['password']!=''&&($groupid=='guest' || $pwdcheck[$fid]!=$forum['password'] && $windid!=$manager)){
		require_once(R_P.'require/forumpw.php');
	}
	if($forum['allowvisit'] && !allowcheck($forum['allowvisit'],$groupid,$winddb['groups'],$fid,$winddb['visit'])){
		Showmsg('forum_jiami');
	}

	if(!$forum['cms'] && $forum['f_type']=='hidden' && !$forum['allowvisit']){
		Showmsg('forum_hidden');
	}
}
function forum_creditcheck(){
	global $db,$winddb,$userrvrc,$forumset,$groupid;

	$forumset['rvrcneed']   /= 10;
	$forumset['moneyneed']   = (int) $forumset['moneyneed'];
	$forumset['creditneed']  = (int) $forumset['creditneed'];
	$forumset['postnumneed'] = (int) $forumset['postnumneed'];
	$check = 1;
	if ($forumset['rvrcneed'] && $userrvrc < $forumset['rvrcneed']){
		$check = 0;
	} elseif ($forumset['moneyneed'] && $winddb['money'] < $forumset['moneyneed']){
		$check = 0;
	} elseif ($forumset['creditneed'] && $winddb['credit'] < $forumset['creditneed']){
		$check = 0;
	} elseif ($forumset['postnumneed'] && $winddb['postnum'] < $forumset['postnumneed']){
		$check = 0;
	}
	if(!$check){
		if($groupid == 'guest'){
			Showmsg('forum_guestlimit');
		}else{
			Showmsg('forum_creditlimit');
		}
	}
}
function get_creditset($creditset,$db_creditset){
    $creditset    = unserialize($creditset);
	$db_creditset = unserialize(stripslashes($db_creditset));
	if ($creditset){
		foreach($creditset as $key => $value){
			$value['Digest']===''   && $creditset[$key]['Digest']   = $db_creditset[$key]['Digest'];
			$value['Post']===''     && $creditset[$key]['Post']     = $db_creditset[$key]['Post'];
			$value['Reply']===''    && $creditset[$key]['Reply']    = $db_creditset[$key]['Reply'];
			$value['Undigest']==='' && $creditset[$key]['Undigest'] = $db_creditset[$key]['Undigest'];
			$value['Delete']===''   && $creditset[$key]['Delete']   = $db_creditset[$key]['Delete'];
			$value['Deleterp']==='' && $creditset[$key]['Deleterp'] = $db_creditset[$key]['Deleterp'];
		}
	} else{
		$creditset = $db_creditset;
	}
	return $creditset;
}
function customcredit($uid,$creditset,$option){
	global $db;
	@include (D_P.'data/bbscache/creditdb.php');
	foreach($_CREDITDB as $key => $value){
		if($creditset[$key][$option]){
			if($option == 'Digest' || $option == 'Post' || $option == 'Reply'){
				$addpoint = $creditset[$key][$option];
			} else{
				$addpoint = -$creditset[$key][$option];
			}
			$db->pw_update(
				"SELECT uid FROM pw_membercredit WHERE uid='$uid' AND cid='$key'",
				"UPDATE pw_membercredit SET value=value+'$addpoint' WHERE uid='$uid' AND cid='$key'",
				"INSERT INTO pw_membercredit SET uid='$uid',cid='$key',value='$addpoint'"
			);
		}
	}
}
function forumindex($fup){
	global $forum,$fid,$cateid,$fpage;
	$secondurl="thread.php?fid=$fid".($fpage>1 ? "&page=$fpage" : '');
	
	if($forum[$fup]['type']=='category'){
		$cateid=$forum[$fup]['fid'];
		$guidename=array(
			$cateid=>array($forum[$fid]['name'],$secondurl)
		);
	} elseif($forum[$fup]['type']=='forum'){
		$cateid=$forum[$fup]['fup'];
		$guidename=array(
			$cateid=>array($forum[$fup]['name'],"thread.php?fid=".$forum[$fup]['fid']),
			$fup=>array($forum[$fid]['name'],$secondurl)
		);
	} elseif($forum[$fup]['type']=='sub'){
		$fup1=$forum[$fup]['fup'];
		$cateid=$forum[$fup1]['fup'];
		$guidename=array(
			$cateid=>array($forum[$fup1]['name'],"thread.php?fid=".$forum[$fup1]['fid']),
			$fup1=>array($forum[$fup]['name'],"thread.php?fid=".$forum[$fup]['fid']),
			$fup=>array($forum[$fid]['name'],$secondurl)
		);
	}
	return $guidename;
}
function headguide($guidename){
	global $db_menu,$db_bbsname,$db_dfn,$cateid,$fid,$imgpath,$stylepath,$db_menu;
	
	if($db_menu>1){
		$headguide = "<img src=\"$imgpath/$stylepath/index/home_menu.gif\" align=\"absmiddle\" id=\"td_cate\" onclick=\"click_open('menu_cate','td_cate');\" onMouseOver=\"mouseover_open('menu_cate','td_cate');\" style=\"cursor:pointer;\" /> <b>";
	} else{
		$headguide = "<img src=\"$imgpath/$stylepath/index/home.gif\" align=\"absmiddle\" /> <b>";
	}

	$headguide .= "<a href=\"$db_dfn\">$db_bbsname</a>";
	
	if(!is_array($guidename)){
		return array($headguide." &raquo; ".$guidename,'');
	}
	foreach($guidename as $key=>$value){
		if($value[1]){
			$headguide .= " &raquo; <a href=\"$value[1]\">$value[0]</a>";
		} else{
			$headguide .= " &raquo; $value[0]";
		}
	}
	$headguide .= "</b>";
	return array($headguide,forumlist($fid));
}
function numofpage($count,$page,$numofpage,$url,$max=0){
	global $tablecolor;
	$total=$numofpage;
	$max && $numofpage > $max && $numofpage=$max;
	if($numofpage <= 1 || !is_numeric($page)){
		return '';
	}else{
		$pages="<div class=\"pages\"><a href=\"{$url}page=1\" style=\"font-weight:bold\">&laquo;</a>";
		$flag=0;
		for($i=$page-3;$i<=$page-1;$i++){
			if($i<1) continue;
			$pages.="<a href=\"{$url}page=$i\">$i</a>";
		}
		$pages.="<b> $page </b>";
		if($page<$numofpage){
			for($i=$page+1;$i<=$numofpage;$i++){
				$pages.="<a href=\"{$url}page=$i\">$i</a>";
				$flag++;
				if($flag==4) break;
			}
		}
		$pages.="<input type=\"text\" size=\"3\" onkeydown=\"javascript: if(event.keyCode==13){ location='{$url}page='+this.value;return false;}\"><a href=\"{$url}page=$numofpage\" style=\"font-weight:bold\">&raquo;</a> Pages: ( $page/$total total )</div>";
		return $pages;
	}
}
function getstyles($skin){
	$styles='';
	$fp=opendir(R_P."data/style/");
	while($skinfile=readdir($fp)){
		if(eregi("\.php$",$skinfile)) {
			$skinfile=str_replace(".php","",$skinfile);
			if($skin && $skinfile==$skin){
				$styles .= "<option value=\"$skinfile\" selected>$skinfile</option>";
			}else{
				$styles .= "<option value=\"$skinfile\">$skinfile</option>";
			}
		}
	}
	closedir($fp);
	return $styles;
}
function geteditor($type='editor'){
	global $stylepath;
	if($type=='c_editor'){
		$js_path=file_exists(R_P."data/{$stylepath}_c_editor.js") ? "data/{$stylepath}_c_editor.js" : "data/wind_c_editor.js";
	}else{
		$js_path=file_exists(R_P."data/{$stylepath}_editor.js") ? "data/{$stylepath}_editor.js" : "data/wind_editor.js";
	}
	return $js_path;
}
function forumlist($fid){
	global $forum,$db_menu;

	if($db_menu<2) return '';

	$chtml = "<div id=\"menu_cate\" class=\"menu\" style=\"display:none;\"><div style=\"padding:5px;background:#e2eff5;height:420px;width:165px;overflow-Y:auto;\"><ul class=\"ul1\">";

	foreach($forum as $key=>$v){
		if($v['cms'] || $v['f_type']=='hidden')continue;
		$fup = $v['fup'];
		if($v['type']=='category'){
			$chtml .= '<li> <a href="index.php?cateid='.$v['fid'].'">>> '.$v['name'].'</a></li>';
		} elseif($forum[$fup]['type']=='category'){
			$chtml .= '<li> &nbsp;<a href="thread.php?fid='.$v['fid'].'">|- '.$v['name'].'</a></li>';
		} elseif($forum[$fup]['type']=='forum'){
			$chtml .= '<li> &nbsp; &nbsp;<a href="thread.php?fid='.$v['fid'].'">|- '.$v['name'].'</a></li>';
		} else{
			$chtml .= '<li> &nbsp;&nbsp; &nbsp; &nbsp;<a href="thread.php?fid='.$v['fid'].'">|- '.$v['name'].'</a></li>';
		}
	}
	$chtml .= '</ul></div></div>';
	return $chtml;
}
?>