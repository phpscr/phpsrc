<?php
!function_exists('readover') && exit('Forbidden');

function wind_forumcheck($forum)
{
	global $windid,$groupid,$tid,$fid,$manager,$skin;

	if($forum['f_type']=='former' && $groupid=='guest' && $_COOKIE){
		Showmsg('forum_former');
	}
	if(!empty($forum['style']) && file_exists(D_P."data/style/$forum[style].php")){
		$skin=$forum['style'];
	}
	$pwdcheck='pwdcheck'.$fid;
	if($forum['password']!='' && GetCookie($pwdcheck) != $forum['password'] && $windid!=$manager){
		require_once(R_P.'require/forumpw.php');
	}
	if($forum['allowvisit'] && @strpos($forum['allowvisit'],','.$groupid.',')===false && $windid!=$manager){
		Showmsg('forum_jiami');
	}
	if(!$forum['cms'] && $forum['f_type']=='hidden' && $windid!=$manager && !$forum['allowvisit']){
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
	$secondurl="thread.php?fid=$fid&page=$fpage";

	if($forum[$fup]['type']=='category'){
		$cateid=$forum[$fup]['fid'];
		$guidename=array(
			$forum[$fid]['name']."\t"=>$secondurl
		);
	} elseif($forum[$fup]['type']=='forum'){
		$cateid=$forum[$fup]['fup'];
		$guidename=array(
			$forum[$fup]['name']=>"thread.php?fid=".$forum[$fup]['fid'],
			$forum[$fid]['name']."\t"=>$secondurl
		);
	} elseif($forum[$fup]['type']=='sub'){
		$fup1=$forum[$fup]['fup'];
		$cateid=$forum[$fup1]['fup'];
		$guidename=array(
			$forum[$fup1]['name']=>"thread.php?fid=".$forum[$fup1]['fid'],
			$forum[$fup]['name']."\t"=>"thread.php?fid=".$forum[$fup]['fid'],
			$forum[$fid]['name']."\t\t"=>$secondurl
		);
	}
	return $guidename;
}
function numofpage($count,$page,$numofpage,$url,$max=0)
{
	global $tablecolor;
	$total=$numofpage;
	$max && $numofpage > $max && $numofpage=$max;
	if ($numofpage <= 1 || !is_numeric($page)){
		return ;
	}else{
		$pages="<a href=\"{$url}page=1\"><< </a>";
		$flag=0;
		for($i=$page-3;$i<=$page-1;$i++)
		{
			if($i<1) continue;
			$pages.=" <a href='{$url}page=$i'>&nbsp;$i&nbsp;</a>";
		}
		$pages.="&nbsp;&nbsp;<b>$page</b>&nbsp;";
		if($page<$numofpage)
		{
			for($i=$page+1;$i<=$numofpage;$i++)
			{
				$pages.=" <a href='{$url}page=$i'>&nbsp;$i&nbsp;</a>";
				$flag++;
				if($flag==4) break;
			}
		}
		$pages.=" <input type='text' size='2' style='height: 16px; border:1px solid $tablecolor' onkeydown=\"javascript: if(event.keyCode==13) location='{$url}page='+this.value;\"> <a href=\"{$url}page=$numofpage\"> >></a> &nbsp;Pages: ( $page/$total total )";
		return $pages;
	}
}
?>