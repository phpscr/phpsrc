<?php
define('SCR','index');
$wind_in = 'hm';
require_once('global.php');
include_once(D_P.'data/bbscache/level.php');

if($groupid!='guest'){
	$lastlodate=get_date($winddb['lastvisit']);
	$level=$ltitle[$groupid];
}
unset($lneed,$lpic);

include_once(D_P.'data/bbscache/index_cache.php');
if($notice_A){
	$NT_A=array_shift($notice_A);
	$NT_A['startdate']=get_date($NT_A['startdate'],'m-j G:i');
	$notice="<a href='notice.php?fid=$NT_A[fid]#$NT_A[aid]'>$NT_A[subject]($NT_A[startdate])</a>";
}else{
	$notice='';
}

$forumdb=$catedb=array();
unset($tposts);

if($db_forumdir){
	require_once(R_P.'require/dirname.php');
}elseif(is_numeric($cateid)){
	$cateinfo=$db->get_one("SELECT style FROM pw_forums WHERE fid='$cateid'");
	if(!empty($cateinfo['style']) && file_exists(D_P."data/style/$cateinfo[style].php")){
		$skin=$cateinfo['style'];
	}
	$threadcate=" AND (f.fid='$cateid' OR f.fup='$cateid')";
} else{
	$threadcate="";
}
require_once(R_P.'require/header.php');

$c_htm=0;
$cmsadd=$db_showcms ? '' : "AND f.cms!='1'";
$topics=$article=$tposts=0;

$query = $db->query("SELECT f.fid,f.fup,f.type,f.logo,f.name, f.descrip,f.forumadmin,f.across,f.allowhtm,f.password,f.allowvisit,f.f_type,f.cms,fd.tpost,fd.topic,fd.article,fd.subtopic,fd.top1,fd.lastpost FROM pw_forums f LEFT JOIN pw_forumdata fd USING(fid) WHERE f.ifsub='0' $cmsadd $threadcate ORDER BY f.vieworder");
while($forums = $db->fetch_array($query)){
	if($forums['type']==='category'){
		if(strpos($_COOKIE['deploy'],"\t".$forums['fid']."\t")===false){
			$forums['deploy_img']='fold';
		}else{
			$forums['deploy_img']='open';
			$forums['tbody_style']='display:none;';
		}
		if($forums['forumadmin']){
			$forumadmin=explode(",",$forums['forumadmin']);
			foreach($forumadmin as $key => $value){
				if($value){
					if ($key==10) {$forums['admin'].='...'; break;}
					$forums['admin'].="<a href=profile.php?action=show&username=".rawurlencode($value)." class=cfont>$value</a> ";
				}
			}
		}
		$forums['name']=preg_replace("/\<(.+?)\>/is","",$forums['name']);
		$catedb[]=$forums;
	} elseif($forums['type']==='forum'){
		$forums['topics']=$forums['topic']+$forums['subtopic'];
		if($db_topped){
			$forums['topics']+=$forums['top1'];
			$forums['article']+=$forums['top1'];
		}
		$article+=$forums['article'];
		$topics+=$forums['topics'];
		$tposts+=$forums['tpost'];
		if(empty($forums['password']) && (empty($forums['allowvisit']) || strpos($forums['allowvisit'],','.$groupid.',')!==false)){
			list($f_a,$forums['au'],$f_c,$forums['ft'])=explode("\t",$forums['lastpost']);
			$forums['pic'] = $winddb['lastvisit']<$f_c && ($f_c+172800>$timestamp) ? 'new' : 'old';
			$forums['newtitle']=get_date($f_c);
			$forums['t']=substrs($f_a,21);
		} else{
			if($forums['f_type']==='hidden' && $groupid != 3){
				continue;
			}
			$forums['pic']="lock";
		}
		$forums['allowhtm']==1 && $c_htm=1;
		if($db_indexfmlogo==2){
			$forums['llogo']=$forums['logo']!='' ? "<img align=left src=$forums[logo] border=0>":'';
		} elseif($db_indexfmlogo==1){
			$forumlogofile="$stylepath/forumlogo/$forums[fid].gif";
			file_exists("$imgdir/$forumlogofile") && $forums['llogo']="<img align=left src='$imgpath/$forumlogofile' border=0>";
		}
		$adminarray=explode("\t",$forums['forumadmin']);
		if($adminarray[0]){
			$forumadmin=explode(",",$adminarray[0]);
			foreach($forumadmin as $key => $value){
				if($value){
					if(!$db_adminshow){
						$forums['admin'].="<a href='profile.php?action=show&username=".rawurlencode($value)."'>$value</a> ";
					} else{
						$forums['admin'].="<option value='$value'>$value</option>";
					}
				}
			}
		}
		$forumdb[$forums['fup']][]=$forums;
	}
}
$db->free_result($query);
unset($forums);

/**
* Share union
*/
if($db_indexmqshare){
	$sharelink="<marquee scrolldelay=100 scrollamount=4 onmouseout='if (document.all!=null){this.start()}' onmouseover='if (document.all!=null){this.stop()}' behavior=alternate>$sharelink</marquee>";
}
if(strpos($_COOKIE['deploy'],"\t".info."\t")===false){
	$cate_img='fold';
}else{
	$cate_img='open';
	$cate_info='display:none;';
}
if(strpos($_COOKIE['deploy'],"\t".notice."\t")===false){
	$notice_img='fold';
}else{
	$notice_img='open';
	$cate_notice='display:none;';
}

/**
* oline users
*/

Update_ol();
@include_once(D_P.'data/bbscache/olcache.php');
$usertotal=$guestinbbs+$userinbbs;

if($windid==$manager || $usertotal<2000){
	$online1 = GetCookie('online1');
	if($online){
		$online1=$online;
		Cookie('online1',$online);
	}
	if($db_indexonline && $online1!='no'){
		$doonlinefu=1;
	} elseif($online1=='yes'){
		$doonlinefu=1;
	}
}
$showgroup = explode(",",$db_showgroup);

@extract($db->get_one("SELECT * FROM pw_bbsinfo WHERE id=1"));
$rawnewuser=rawurlencode($newmember);
if($tdtcontrol!=$tdtime){
	if($db_hostweb == 1 && !$cateid && $groupid != 'guest'){
		$db->update("UPDATE pw_bbsinfo SET yposts='$tposts', tdtcontrol='$tdtime' WHERE id=1");
		$db->update("UPDATE pw_forumdata SET tpost=0 WHERE tpost<>'0'");
	}
	if(file_exists(D_P.'data/bbscache/ip_cache.php')){
		P_unlink(D_P.'data/bbscache/ip_cache.php');
	}
}
/**
* update posts hits
*/
if($c_htm || $db_hithour){
	$db_hithour==0 && $db_hithour=4;
	$hit_wtime=$hit_control*$db_hithour;
	if($hit_wtime>24)$hit_wtime=0;
	$hitsize=@filesize(D_P."data/bbscache/hits.txt");
	if(($timestamp-$hit_tdtime)>$hit_wtime*3600 || $hitsize>1024){
		require_once(R_P.'require/hitupdate.php');
	}
}
if($higholnum<$usertotal){
	$db->update("UPDATE pw_bbsinfo SET higholnum='$usertotal',higholtime='$timestamp' WHERE id=1");
	$higholnum=$usertotal;
}
if($hposts<$tposts){
	$db->update("UPDATE pw_bbsinfo SET hposts='$tposts' WHERE id=1");
	$hposts=$tposts;
}
$mostinbbstime=get_date($higholtime);
if($db_onlinelmt!=0 && $usertotal>=$db_onlinelmt && !$ol_offset){
	Cookie('ol_offset','',0);
	Showmsg('most_online');
}

list($db_moneyname,$db_moneyunit,$db_rvrcname,$db_rvrcunit,$db_creditname,$db_creditunit)=explode("\t",$db_credits);

require_once PrintEot('index');footer();
?>