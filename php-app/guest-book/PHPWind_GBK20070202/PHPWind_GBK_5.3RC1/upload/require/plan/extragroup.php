<?php
!function_exists('db_cv') && exit('Forbidden');

$updatecache_fd=0;
$query=$db->query("SELECT eg.uid,eg.gid,eg.togid,m.groupid,m.groups,m.username FROM pw_extragroups eg LEFT JOIN pw_members m USING(uid) WHERE $timestamp-eg.startdate>eg.days*86400 LIMIT 100");
while($rt=$db->fetch_array($query)){
	if($rt['gid']==$rt['groupid']){
		$newgid=($rt['togid'] && strpos($rt['groups'],",$rt[togid],")!==false) ? $rt['togid'] : '-1';
		$newgroups=str_replace(','.$newgid.',',',',$rt['groups']);
	}else{
		$newgid=$rt['groupid'];
		$newgroups=str_replace(','.$rt['gid'].',',',',$rt['groups']);
	}
	if($rt['gid']=='5'){
		$query1=$db->query("SELECT fid,forumadmin FROM pw_forums WHERE forumadmin!=''");
		while($fm=$db->fetch_array($query1)){
			if($fm['forumadmin'] && strpos($fm['forumadmin'],",$rt[username],")!==false){
				$newadmin = str_replace(",$rt[username],",',',$fm['forumadmin']);
				$newadmin == ',' && $newadmin = '';
				$db->update("UPDATE pw_forums SET forumadmin='$newadmin' WHERE fid='$fm[fid]'");
			}
		}
		$updatecache_fd=1;
	}
	$newgroups==',' && $newgroups='';
	$db->update("UPDATE pw_members SET groupid='$newgid',groups='$newgroups' WHERE uid='$rt[uid]'");
	$db->update("DELETE FROM pw_extragroups WHERE uid='$rt[uid]' AND gid='$rt[gid]'");
}
if($updatecache_fd){
	$havechild=array();
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
?>