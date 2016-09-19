<?php
!function_exists('adminmsg') && exit('Forbidden');

$basename="$admin_file?adminjob=attachrenew";
if(empty($_POST['action'])){
	$rs=$db->get_one("SELECT COUNT(*) AS count FROM pw_attachs");
	$A_count=$rs['count'];
	//require R_P.'require/forum.php';
	$threaddb=array();
	//getallfile($attachdir.'/');
	if($A_count!=0){
		$P_N=1000;
		$A_C=ceil($A_count/$P_N);
		$L_N=$P_N-$A_C*$P_N+$A_count;
		$S_end=$L_N;
		for($i=1;$i<=$A_C;$i++){
			$num=($i-1)*$P_N;
			$E_num=$i==$A_C ? $num+$L_N : $num+$P_N;
			$S_num="$num To $E_num";
			$A_option.="<option value=$i>$S_num</option>";
		}
		!$S_start && $S_start=1;
		$S_start==$A_C ? $S_end=$L_N : $S_end=$P_N;
		$start_limit=($S_start-1)*$P_N;
		$orderway!='size' && $orderway='aid';
		$query=$db->query("SELECT aid,fid,name,size,attachurl FROM pw_attachs ORDER BY $orderway LIMIT $start_limit,$S_end");
		
		while($attach=$db->fetch_array($query)){
			if(!file_exists("$attachdir/$attach[attachurl]")){
				$thread['aid']=$attach['aid'];
				$thread['size']=$attach['size'];
				$thread['url']=$attach['attachurl'];
				$thread['name']=$attach['name'];
				$thread['where']="thread.php?fid=$fid";
				$threaddb[]=$thread;
			}
		}
	}
	include PrintEot('attachrenew');exit;
} else{
	$count=0;
	$query=$db->query("SELECT t.fid,t.tid,t.authorid,aid FROM pw_tmsgs tm LEFT JOIN pw_threads t ON t.tid=tm.tid  WHERE aid<>''");
	while($aids=$db->fetch_array($query)){
		$attachs= unserialize(stripslashes($aids['aid']));
		if(is_array($attachs)){
			$update=0;
			foreach($attachs as $key=>$attach){
				if($attach['attachurl'] && file_exists($attachdir.'/'.$attach['attachurl'])){
					$check=$db->get_one("SELECT aid FROM pw_attachs WHERE aid='$attach[aid]'");
					if(!$check){
						$uploadtime=filemtime($attachdir.'/'.$attach['attachurl']);
						$count++;
						$attach['name']=addslashes($attach['name']);
						$db->update("INSERT INTO pw_attachs(aid,fid,uid,tid,pid,name,type,size,attachurl,hits,needrvrc,uploadtime,descrip) VALUES('$attach[aid]','$aids[fid]','$aids[authorid]','$aids[tid]','0','$attach[name]','$attach[type]','$attach[size]','$attach[attachurl]','$attach[hits]','$attach[needrvrc]','$uploadtime','$attach[desc]')");
					}
				} else{
					$count++;
					$check=$db->get_one("SELECT aid FROM pw_attachs WHERE aid='$attach[aid]'");
					if($check){
						$db->update("DELETE FROM pw_attachs WHERE aid='$attach[aid]'");
					}
					$update=1;
					unset($attachs[$key]);
				}
			}
			if($update){
				$attachs=$attachs ?  addslashes(serialize($attachs)):'';
				$db->update("UPDATE pw_tmsgs SET aid='$attachs' WHERE tid='$aids[tid]'");
			}
		} else{
			$count++;
			$db->update("UPDATE pw_tmsgs SET aid='' WHERE tid='$aids[tid]'");
		}
	}
	
	$query=$db->query("SELECT fid,tid,authorid,aid FROM pw_posts WHERE aid<>''");
	while($aids=$db->fetch_array($query)){
		$attachs= unserialize(stripslashes($aids['aid']));
		if(is_array($attachs)){
			$update=0;
			foreach($attachs as $key=>$attach){
				if($attach['attachurl'] && file_exists($attachdir.'/'.$attach['attachurl'])){
					$check=$db->get_one("SELECT aid FROM pw_attachs WHERE aid='$attach[aid]'");
					if(!$check){
						$uploadtime=filemtime($attachdir.'/'.$attach['attachurl']);
						$count++;
						$attach['name']=addslashes($attach['name']);
						$db->update("INSERT INTO pw_attachs(aid,fid,uid,tid,pid,name,type,size,attachurl,hits,needrvrc,uploadtime,descrip) VALUES('$attach[aid]','$aids[fid]','$aids[authorid]','$aids[tid]','0','$attach[name]','$attach[type]','$attach[size]','$attach[attachurl]','$attach[hits]','$attach[needrvrc]','$uploadtime','$attach[desc]')");
					}
				} else{
					$count++;
					$check=$db->get_one("SELECT aid FROM pw_attachs WHERE aid='$attach[aid]'");
					if($check){
						$db->update("DELETE FROM pw_attachs WHERE aid='$attach[aid]'");
					}
					$update=1;
					unset($attachs[$key]);

				}
			}
			if($update){
				$attachs=$attachs ?  addslashes(serialize($attachs)):'';
				$db->update("UPDATE pw_posts SET aid='$attachs' WHERE tid='$aids[tid]'");
			}
		} else{
			$count++;
			$db->update("UPDATE pw_posts SET aid='' WHERE tid='$aids[tid]'");
		}
	}
	adminmsg('attach_renew');
}
?>