<?php
!function_exists('adminmsg') && exit('Forbidden');
@set_time_limit(0);
$db_perpage=50;
$basename="$admin_file?adminjob=attachment";

include(D_P.'data/bbscache/forum_cache.php');
include_once(R_P.'require/forum.php');

if ($admin_gid == 5){
	list($allowfid,$forumcache) = GetAllowForum($admin_name);
	$sql = "fid IN($allowfid)";
} else {
	include D_P.'data/bbscache/forumcache.php';
	list($hidefid,$hideforum) = GetHiddenForum();
	if($admin_gid == 3){
		$forumcache .= $hideforum;
		$sql = '1';
	} else{
		$sql = "fid NOT IN($hidefid)";
	}
}

if (empty($action)){
	$postdate2=get_date($timestamp+24*3600,'Y-m-d');
	include PrintEot('attachment');exit;
}elseif ($action=='search'){
	if (is_numeric($fid)){
		$sql .= " AND fid='$fid'";
	}
	$username = trim($username);
	if($username){
		$rt  = $db->get_one("SELECT uid FROM pw_members WHERE username='$username'");
		$uid = $rt['uid'];
	}
	if(is_numeric($uid)){
		$sql .= " AND uid='$uid'";
	}
	$filename = trim($filename);
	if ($filename!=''){
		$filename=str_replace('*','%',$filename);
		$sql.=" AND (name LIKE '%$filename%')";
	}
	if ($hits){
		if ($ifmore){
			$sql.=" AND (hits<'$hits')";
		} else{
			$sql.=" AND (hits>'$hits')";
		}
	}
	if ($filesize){
		if ($ifless){
			$sql.=" AND (size<'$filesize')";
		} else{
			$sql.=" AND (size>'$filesize')";
		}
	}
	if ($postdate1){
		$uploadtime=PwStrtoTime($postdate1);
		is_numeric($uploadtime) && $sql.=" AND uploadtime > '$uploadtime'";
	}
	if ($postdate2){
		$uploadtime=PwStrtoTime($postdate2);
		is_numeric($uploadtime) && $sql.=" AND uploadtime < '$uploadtime'";
	}
	
	if ($orderway){
		$order="ORDER BY '$orderway'";
		 $asc=='DESC' && $order.=' '.$asc;
	}else{
		$order='';
	}
	$pernum=is_numeric($pernum) ? $pernum : 20;
	$page < 1 && $page=1;
	$limit="LIMIT ".($page-1)*$pernum.",$pernum";

	$rt=$db->get_one("SELECT COUNT(*) AS count FROM pw_attachs WHERE $sql");
	$sum=$rt['count'];
	$numofpage=ceil($sum/$pernum);
	$pages=numofpage($sum,$page,$numofpage,"$basename&action=search&fid=$fid&uid=$uid&filename=".rawurlencode($filename)."&hits=$hits&ifmore=$ifmore&filesize=$filesize&ifless=$ifless&orderway=$orderway&asc=$asc&postdate1=$postdate1&postdate2=$postdate2&pernum=$pernum&");

	$attachdb=$thread=array();
	$query=$db->query("SELECT * FROM pw_attachs WHERE $sql $order $limit");
	while(@extract($db->fetch_array($query))){
		if($_POST['direct']){
			if(file_exists("$attachdir/$attachurl")){
				P_unlink("$attachdir/$attachurl");
			}
		}else{
			$thread['url']=$attachurl;
			$thread['name']=$name;
			$thread['aid']=$aid;
			$thread['tid']=$tid;
			$thread['where']="thread.php?fid=$fid";
			$thread['forum']=$forum[$fid]['name'];
			$thread['filezie']=$size;
			$thread['uploadtime']=get_date($uploadtime);
			$attachdb[]=$thread;
		}
	}
	if($_POST['direct']){
		$db->update("DELETE FROM pw_attachs WHERE $sql LIMIT $pernum");
		adminmsg('operate_success');
	}else{
		include PrintEot('attachment');exit;
	}
} elseif ($action=='schdir'){

	if (!$filename && !$filesize && !$postdate1 && !$postdate2){
		adminmsg('noenough_condition');
	}
	$cache_file=D_P."data/bbscache/att_".substr(md5($admin_name),10,10).".txt";
	if (!$start){
		$start=0;
		if (file_exists($cache_file)){
			P_unlink($cache_file);
		}
	}
	$num = 0;
	!$pernum && $pernum = 1000;
	$dir1=opendir($attachdir);
	while(false !== ($file1 = readdir($dir1))){
		if ($file1!='' && $file1!='.' && $file1!='..' && !eregi("\.html$",$file1)){
			if (is_dir("$attachdir/$file1")){
				$dir2=opendir("$attachdir/$file1");
				while(false !==($file2=readdir($dir2))){
					if (is_file("$attachdir/$file1/$file2") && $file2!='' && $file2!='.' && $file2!='..' && !eregi("\.html$",$file2)){
						$num++;
						if ($num > $start){
							attachcheck("$file1/$file2");
							if ($num-$start>=$pernum){
								if($direct){
									adminmsg('attach_delfile');
								}else{
									adminmsg('attach_step',"$basename&action=$action&filename=$filename&filesize=$filesize&ifless=$ifless&postdate1=$postdate1&postdate2=$postdate2&start=$num&pernum=$pernum&direct=$direct",0);
								}
							}
						}
					}
				}
			}elseif (is_file("$attachdir/$file1")){
				$num++;
				if ($num > $start){
					attachcheck("$file1");
					if ($num-$start>=$pernum){
						if($direct){
							adminmsg('attach_delfile');
						}else{
							adminmsg('attach_step',"$basename&action=$action&filename=$filename&filesize=$filesize&ifless=$ifless&postdate1=$postdate1&postdate2=$postdate2&start=$num&pernum=$pernum&direct=$direct",0);
						}
					}
				}
			}
		}
	}
	adminmsg('attach_success',"$basename&action=list",0);
}elseif ($action=='list'){
	$cache_file=D_P."data/bbscache/att_".substr(md5($admin_name),10,10).".txt";

	if (!is_numeric($page) || $page<1){
		$page=1;
	}
	$start=($page-1)*$db_perpage*50;
	$readsize=$db_perpage*50;

	$sum=floor(@filesize($cache_file)/50);
	$numofpage=ceil($sum/$db_perpage);
	$pages=numofpage($sum,$page,$numofpage,"$basename&action=list&");

	if ($fp=@fopen($cache_file,"rb")){
		flock($fp,LOCK_SH);
		fseek($fp,$start);
		$readdb=fread($fp,$readsize);
		fclose($fp);
	}
	$readdb=explode("\n",$readdb);
	foreach($readdb as $key => $value){
		$value=trim($value);
		if ($value){
			$attach['name']=$value;
			if (file_exists("$attachdir/$value")){
				$attach['size']=round(filesize("$attachdir/$value")/1024,1);
				$attach['time']=get_date(fileatime("$attachdir/$value"));
				$attach['exists']=1;
			} else{
				$attach['size']='-';
				$attach['time']='-';
				$attach['exists']=0;
			}
			$attachdb[]=$attach;
		}
	}
	include PrintEot('attachment');exit;
}elseif ($_POST['action']=='delfile'){
	if ($delfile){
		foreach($delfile as $key => $value){
			if (file_exists("$attachdir/$value")){
				P_unlink("$attachdir/$value");
			}
		}
	}
	$basename="$admin_file?adminjob=attachment&action=list";
	adminmsg('attach_delfile');
}elseif ($_POST['action']=='delete'){
	if ($aidarray){
		$count   = count($aidarray);
		$attachs = '';
		foreach($aidarray as $value){
			is_numeric($value) && $attachs.=$value.',';
		}
		$attachs = substr($attachs,0,-1);
		$query=$db->query("SELECT attachurl FROM pw_attachs WHERE $sql AND aid IN($attachs)");
		while($rs=$db->fetch_array($query)){
			if (P_unlink("$attachdir/$rs[attachurl]")){
				$delnum ++;
				$delname .= "$rs[attachurl]<br>";
			}
		}
		$db->update("DELETE FROM pw_attachs WHERE $sql AND aid IN($attachs)");
	}
	adminmsg('attachstats_del');
}elseif ($action=='delattach'){
	$start && PostCheck($verify);
	if (!$start){		
		$start=0;
		$deltotal=0;
	}
	$num	= 0;
	$delnum	= 0;
	!$pernum && $pernum = 1000;
	$dir1=opendir($attachdir);
	while(false !== ($file1 = readdir($dir1))){
		if ($file1!='' && $file1!='.' && $file1!='..' && !eregi("\.html$",$file1)){
			if (is_dir("$attachdir/$file1")){
				if($file1=='upload')continue;
				$dir2=opendir("$attachdir/$file1");
				while(false !==($file2=readdir($dir2))){
					if (is_file("$attachdir/$file1/$file2") && $file2!='' && $file2!='.' && $file2!='..' && !eregi("\.html$",$file2)){
						$num++;
						if ($num > $start){
							$rt = $db->get_one("SELECT aid FROM pw_attachs WHERE attachurl='$file1/$file2'");
							if(!$rt){
								$delnum++;
								$deltotal++;
								P_unlink("$attachdir/$file1/$file2");
							}
							if ($num-$start >= $pernum){
								$start = $num-$delnum;

								$j_url="$basename&action=$action&start=$start&pernum=$pernum&deltotal=$deltotal";
								adminmsg('delattach_step',EncodeUrl($j_url),0);
							}
						}
					}
				}
			}elseif (is_file("$attachdir/$file1")){
				$num++;
				if ($num > $start){
					$rt = $db->get_one("SELECT aid FROM pw_attachs WHERE attachurl='$file1'");
					if(!$rt){
						$delnum++;
						$deltotal++;
						P_unlink("$attachdir/$file1");
					}
					if ($num-$start>=$pernum){
						$start = $num-$delnum;

						$j_url="$basename&action=$action&start=$start&pernum=$pernum&deltotal=$deltotal";
						adminmsg('delattach_step',EncodeUrl($j_url),0);
					}
				}
			}
		}
	}
	adminmsg('operate_success');
}

function attachcheck($file){
	global $cache_file,$attachdir,$admin_pwd,$filename,$filesize,$ifless,$postdate1,$postdate2,$direct,$attachdir;

	if ($filename && strpos($file,$filename)===false){
		return;
	}
	if ($filesize){
		if ($ifless && filesize("$attachdir/$file") >= $filesize * 1024){
			return;
		} elseif (!$ifless && filesize("$attachdir/$file") <= $filesize * 1024){
			return;
		}
	}
	if ($postdate1){
		$visittime=PwStrtoTime($postdate1);
		if (is_numeric($visittime) && fileatime("$attachdir/$file") < $visittime){
			return;
		}
	}
	if ($postdate2){
		$visittime=PwStrtoTime($postdate2);
		if (is_numeric($visittime) && fileatime("$attachdir/$file") > $visittime){
			return;
		}
	}
	if($_POST['direct']){
		P_unlink("$attachdir/$file");
	}else{
		strlen($file)>49 && $file=substr($file,0,49);
		writeover($cache_file,str_pad($file,49)."\n","ab");
	}
}
?>