<?php
!function_exists('readover') && exit('Forbidden');
require_once(R_P.'require/bbscode.php');
include_once(D_P."data/bbscache/forumcache.php");
list($db_moneyname,$db_moneyunit,$db_rvrcname,$db_rvrcunit,$db_creditname,$db_creditunit)=explode("\t",$db_credits);
if($tid){
	$readtpl='readtpl';
	include_once(D_P."data/style/$skin.php");
	if(!file_exists(R_P."template/$tplpath/$readtpl.htm")){
		include_once(D_P."data/style/$db_defaultstyle.php");
		if(!file_exists(R_P."template/$tplpath/$readtpl.htm")){
			$tplpath='wind';
		}
	}
	include_once(D_P.'data/bbscache/md_config.php');
	if($md_ifopen){
		include_once(D_P.'data/bbscache/medaldb.php');
	}
	ob_end_clean();
	$i_table = $yeyestyle == 'no' ? "bgcolor=$tablecolor" : 'class=i_table';
	$S_sql=',tm.*,p.voteopts,p.pollid,m.uid,m.username,m.oicq, m.groupid,m.memberid,m.icon AS micon ,m.hack,m.honor,m.signature,m.showsign,m.payemail,m.regdate,m.signchange,m.medals,md.onlinetime,md.postnum,md.digests,md.rvrc,md.money,md.credit,md.currency,md.starttime,md.thisvisit';
	$J_sql='LEFT JOIN pw_tmsgs tm ON t.tid=tm.tid LEFT JOIN pw_members m ON m.uid=t.authorid LEFT JOIN pw_memberdata md ON md.uid=t.authorid LEFT JOIN pw_polls p ON p.pollid=t.pollid';
	$usehtm=1;
	$read = $db->get_one("SELECT t.* $S_sql FROM pw_threads t $J_sql WHERE t.tid='$tid'");
	if(!$read){
		$usehtm=0;
	}
	if($foruminfo['allowvisit']){
		$usehtm=0;
	}elseif($foruminfo['password']){
		$usehtm=0;
	}elseif($foruminfo['allowsell'] && strpos($read['content'],"[sell") !== false && strpos($read['content'],"[/sell]") !== false){
		$usehtm=0;
	} elseif($foruminfo['allowhide'] && strpos($read['content'],"[post]") !== false && strpos($read['content'],"[/post]") !== false){
		$usehtm=0;
	} elseif($foruminfo['allowencode'] && strpos($read['content'],"[hide") !== false && strpos($read['content'],"[/hide]") !== false){
		$usehtm=0;
	} elseif(!$ifcheck){
		$usehtm=0;
	}
	$date=date('ym',$read['postdate']);
	if(!$usehtm && file_exists(R_P."$htmdir/$fid/$date/$tid.html")){
		P_unlink(R_P."$htmdir/$fid/$date/$tid.html");
	}
	$page=floor($article/$db_readperpage)+1;
	$count= $read['replies']+1;

	if($usehtm && ($page==1 || $read['replies']<=$db_readperpage || $read['replies']%$db_readperpage==0 || !file_exists(R_P."$htmdir/$fid/$date/$tid.html"))){

		if(D_P != R_P && $db_http != 'N'){
			$path=$db_bbsurl;
		}else{
			$path='../../..';
		}
		$db_http=='N' && $imgpath=$path.'/'.$imgpath;

		$f_url="$path/read.php?tid=$tid&";

		$pollid=$read['pollid'];
		if($read['voteopts']){
			$tpc_date=get_date($read['postdate']);
			htmvote($read['voteopts']);
		}
		$read['pid'] = 'tpc';
		$readdb      = array();
		$readdb[]    = htmread($read,0);
		$authorids   = $read['authorid'];
		$subject     = $read['subject'];
		$tpctitle    = '- '.$subject;
		$favortitle=str_replace("&#39","‘",$subject);
		if($read['replies']>0){
			$start_limit = 0;
			$readnum=$db_readperpage-1;
			$query = $db->query("SELECT p.*,m.uid,m.username,m.oicq, m.groupid,m.memberid,m.icon AS micon,m.hack,m.honor,m.signature,m.showsign,m.payemail,m.regdate,m.signchange,m.medals,md.onlinetime,md.postnum,md.digests,md.rvrc,md.money,md.credit,md.currency,md.starttime,md.thisvisit FROM pw_posts p LEFT JOIN pw_members m ON m.uid=p.authorid LEFT JOIN pw_memberdata md ON md.uid=p.authorid WHERE p.tid='$tid' ORDER BY postdate LIMIT $start_limit, $readnum");
			$start_limit++;
			while($read=$db->fetch_array($query)){
				if($foruminfo['allowsell'] && strpos($read['content'],"[sell") !== false && strpos($read['content'],"[/sell]") !== false){ 
					$usehtm=0;
					break;
				} elseif($foruminfo['allowhide'] && strpos($read['content'],"[post]") !== false && strpos($read['content'],"[/post]") !== false){
					$usehtm=0;break;
				} elseif($foruminfo['allowencode'] && strpos($read['content'],"[hide") !== false && strpos($read['content'],"[/hide]") !== false){
					$usehtm=0;break;
				}
				$readdb[]=htmread($read,$start_limit);
				$authorids .=','.$read['authorid'];
				$start_limit++;
			}
			$db->free_result($query);unset($sign);
		}
		if($usehtm){
			if($db_showcolony){
				$colonydb=array();
				$query = $db->query("SELECT c.uid,cy.id,cy.cname FROM pw_cmembers c LEFT JOIN pw_colonys cy ON cy.id=c.colonyid WHERE c.uid IN($authorids) ORDER BY id DESC");
				while ($rt = $db->fetch_array($query)){
					if(!$colonydb[$rt['uid']]){
						$colonydb[$rt['uid']] = $rt;
					}
				}
			}
			if($db_showcustom){
				$customdb=array();
				@include(D_P.'data/bbscache/creditdb.php');
				$cids = $add = '';
				foreach($_CREDITDB as $key=>$value){
					if(strpos($db_showcustom,",$key,")!==false){
						$cids .= $add.$key;
						!$add && $add = ',';
					}
				}
				if($cids){
					$query = $db->query("SELECT uid,cid,value FROM pw_membercredit WHERE uid IN($authorids) AND cid IN($cids)");
					while ($rt = $db->fetch_array($query)){
						$customdb[$rt['uid']][$rt['cid']] = $rt['value'];
					}
				}
			}

			if ($count%$db_readperpage==0){ //$count $db_readperpage read.php?fid=$fid&tid=$tid&
				$numofpage=$count/$db_readperpage;
			} else{
				$numofpage=floor($count/$db_readperpage)+1;
			}
			$pages=numofpage($count,1,$numofpage,$f_url);//文章数,页码,共几页,路径
			$db_obstart==1 ? ob_start('ob_gzhandler') : ob_start();
			$db_bbsname_a=addslashes($db_bbsname);//模版内用到
			require_once(PrintEot($readtpl));
			$content=ob_get_contents();
			ob_end_clean();
			if(!is_dir(R_P.$htmdir.'/'.$fid)){
				@mkdir(R_P.$htmdir.'/'.$fid);
				@chmod(R_P.$htmdir.'/'.$fid,0777);
				writeover(R_P."$htmdir/$fid/index.html",'');
				@chmod(R_P."$htmdir/$fid/index.html",0777);
			}
			if(!is_dir(R_P.$htmdir.'/'.$fid.'/'.$date)){
				@mkdir(R_P.$htmdir.'/'.$fid.'/'.$date);
				@chmod(R_P.$htmdir.'/'.$fid.'/'.$date,0777);
				writeover(R_P."$htmdir/$fid/$date/index.html",'');
				@chmod(R_P."$htmdir/$fid/$date/index.html",0777);
			}
			writeover(R_P."$htmdir/$fid/$date/$tid.html",$content,"rb+",0);
			@chmod(R_P."$htmdir/$fid/$date/$tid.html",0777);
		} elseif(file_exists(R_P."$htmdir/$fid/$date/$tid.html")){
			P_unlink(R_P."$htmdir/$fid/$date/$tid.html");
		}
		$j_p="$R_url/$htmdir/$fid/$date/$tid.html";
	}
}
function htmread($read,$start_limit){
	global $tpc_author,$path,$count,$timestamp,$db_onlinetime,$attachdir,$attachpath,$gp_allowloadrvrc,$tablecolor,$readcolorone,$readcolortwo,$lpic,$ltitle,$imgpath,$db_ipfrom,$db_showonline,$stylepath,$db_windpost,$db_windpic,$fid,$tid,$attachper,$attachments,$aids,$db_signwindcode,$md_ifopen,$_MEDALDB;
	include_once(D_P.'data/bbscache/level.php');
	$read['lou']=$start_limit;
	$start_limit==$count-1 && $read['jupend']='<a name=lastatc></a>';

	if ($start_limit%2==0){
		$read['colour']=$readcolorone;
	} else{
		$read['colour']=$readcolortwo;
	}
	$read['ifsign']<2 && $read['content']=str_replace("\n","<br>",$read['content']);
	$read['groupid']=='-1' && $read['groupid']=$read['memberid'];
	if($read['groupid']!=''){
		!$lpic[$read['groupid']] && $read['groupid']=8;
		$read['lpic']=$lpic[$read['groupid']];
		$read['level']=$ltitle[$read['groupid']];
		$read['regdate']=get_date($read['regdate'],"Y-m-d");
		$read['aurvrc']=floor($read['rvrc']/10);
		$read['author']=$read['username'];
		$read['ontime']=(int)($read['onlinetime']/3600);
		$tpc_author=$read['author'];
		$read['face']=showfacedesign($read['micon']);
		if($db_ipfrom==1) $read['ipfrom']=' From:'.$read['ipfrom'];

		if($md_ifopen && $read['medals']){
			$medals='';
			$md_a=explode(',',$read['medals']);
			foreach($md_a as $key=>$value){
				if($value){
					$medals.="<img src=\"$imgpath/medal/{$_MEDALDB[$value][picurl]}\" alt=\"{$_MEDALDB[$value][name]}\"> ";
				}
			}
			$read['medals']=$medals;
		}else{
			$read['medals']='';
		}

		global $sign;
		if(!$sign[$read['author']]){
			if ($read['ifsign']==1 || $read['ifsign']==3){
				global $db_signmoney,$db_signgroup,$tdtime;
				if(strpos($db_signgroup,",$read[groupid],") !== false && $db_signmoney && (!$read['showsign'] ||  (!$read['starttime'] || $read['currency'] < (($tdtime-$read['starttime'])/86400)*$db_signmoney))){
					$read['signature'] = '';
				} else{
					if ($db_signwindcode && $read['signchange']==2){
						$read['signature']=convert($read['signature'],$db_windpic,2);
					}
					$read['signature']=str_replace("\n","<br>",$read['signature']);
				}
			}else{
				$read['signature']='';
			}
		}else{
			$read['signature']=$sign[$read['author']];
		}
		$sign[$read['author']]=$read['signature'];
	}else{
		$read['face']="<br>";$read['lpic']='8';
		$read['level']=$read['digests']=$read['postnum']=$read['money']=$read['regdate']=$read['lastlogin']=$read['aurvrc']='*';
	}
	$read['postdate']=get_date($read['postdate']);
	if($read['ifmark']){
		$markdb=explode("\t",$read['ifmark']);
		$markinfo="<tr>";
		foreach($markdb as $key=>$value){
			$markinfo.='<td>'.$value.'</td>';
			$key++;
			if($key%3==0)$markinfo.='</tr><tr>';
		}
		$markinfo.='</tr>';
		$read['mark']=$markinfo;
	} else{
		$read['mark']='';
	}
	
	if($read['icon']){
		$read['icon']="<img src='$imgpath/post/emotion/$read[icon].gif' align=left border=0>";
	} else{
		$read['icon']='';
	}
	/**
	* 动态判断发贴是否需要转换
	*/
	if($read['ifconvert']==2){
		$read['content']=preg_replace("/\[sell=(.+?)\]/is","",$read['content']);
		$read['content']=preg_replace("/\[hide=(.+?)\]/is","",$read['content']);
		$read['content']=str_replace("[/hide]","",$read['content']);
		$read['content']=str_replace("[/sell]","",$read['content']);
		$read['content']=str_replace("[post]","",$read['content']);
		$read['content']=str_replace("[/post]","",$read['content']);
		$read['content']=convert($read['content'],$db_windpost);
	}
	$GLOBALS['foruminfo']['copyctrl'] && $read['content'] = preg_replace("/<br>/eis","copyctrl('$read[colour]')",$read['content']);
	/**
	* 附件信息
	*/
	$attachper=1;
	$attachments=array();
	if($read['aid']!=''){
		$attachs= unserialize(stripslashes($read['aid']));
		if(is_array($attachs)){
			foreach($attachs as $at){
				if(file_exists($attachdir.'/'.$at['attachurl'])){
					$a_url="$path/$attachpath/$at[attachurl]";
				} elseif($attach_url){
					$a_url="$attach_url/$at[attachurl]";
				} else{
					continue;
				}
				if($at['type']=='img' && $at['needrvrc']==0){
					$dfurl='<br>'.cvpic($a_url,1,$db_windpost['picwidth'],$db_windpost['picheight']);
					$read['pic'][$at['aid']]=array($at['aid'],$dfurl,$at['desc']);
					$attachments[$at['aid']]="<b>$at[desc]</b>$dfurl";
				} else{
					$read['downattach'][$at['aid']]=array($at['aid'],$at['name'],$at['size'],$at['hits'],$at['needrvrc'],$at['type'],$at['desc']);
					$attachments[$at['aid']]="<b>$at[desc]</b><br><a href='$path/job.php?action=download&pid=$read[pid]&tid=$tid&aid=$at[aid]' target='_blank'><font color=red>$at[name]</font></a>";
				}
			}
			$aids=array();
			$read['content']=attachment($read['content']);
			foreach($aids as $key => $value){
				if($read['pic'][$value]){
					unset($read['pic'][$value]);
				}
				if($read['downattach'][$value]){
					unset($read['downattach'][$value]);
				}
			}
		}
	}
	$read['alterinfo'] && $read['content'].="<br><br><br><font color=gray>[ $read[alterinfo] ]</font>";
	return $read;
}
function htmvote($voteopts)
{
	global $path,$multi,$votetype,$pollid,$votedb,$votesum,$viewvoter,$fid,$tid,$windid,$admin_check;
	$votearray = unserialize(stripslashes($voteopts));
	if(!is_array($votearray)) return;
	if(!is_array($votearray['options'])) return;
	$votetype = $votearray['multiple'][0] ? 'checkbox' : 'radio';
	$votesum=0;
	$vt_name=$vt_num=$voteid=$voter=$allvoter=$votedb=array();
	foreach($votearray['options'] as $option){
		foreach($option[2] as $key =>$value){
			$allvoter[]=$value;
		}
		$vt_name[]=$option[0];
		$vt_num[]=$option[1];
		$votesum+=$option[1];
	}
	foreach($vt_name as $key=>$value){
		$vote['width']=floor(500*$vt_num[$key]/($votesum+1));
		$vote['name']=$value;
		$vote['num']=$vt_num[$key];
		$votedb[]=$vote;
	}
	$votesum=count(array_unique($allvoter));
	$multi=$votearray['multiple'][0] ? $votearray['multiple'][1] : 0;
}
?>