<?php
!function_exists('adminmsg') && exit('Forbidden');

include(D_P.'data/bbscache/forum_cache.php');
$basename="$admin_file?adminjob=attachstats";
if(empty($_POST['action'])){
	$rs=$db->get_one("SELECT COUNT(*) AS count FROM pw_attachs");
	$A_count=$rs['count'];
	$rs=$db->get_one("SELECT SUM(size) AS size FROM pw_attachs");
	$A_size=$rs['size'] ? $rs['size'] : 0;
	$average=$A_count ? number_format($A_size/$A_count,1) : 0;
	$rs=$db->get_one("SELECT SUM(hits) AS hits FROM pw_attachs");
	$A_hits=$rs['hits'] ? $rs['hits'] : 0;
	
	$M_hits=$M_size=array();
	$query=$db->query("SELECT * FROM pw_attachs ORDER BY hits DESC LIMIT 20");
	while($attach=$db->fetch_array($query)){
		$attach['url']=$attach['attachurl'];
		$attach['forum']=$forum[$attach['fid']]['name'];
		$M_hits[]=$attach;
	}
	$query=$db->query("SELECT * FROM pw_attachs ORDER BY size DESC LIMIT 20");
	while($attach=$db->fetch_array($query)){
		$attach['url']=$attach['attachurl'];
		$attach['forum']=$forum[$attach['fid']]['name'];
		$M_size[]=$attach;
	}

	include PrintEot('attachstats');exit;
} elseif($action=='delete'){
	$delnum=0;
	if($urlarray){
		$count=count($urlarray);
		foreach($urlarray as $key=>$value){
			if(P_unlink("$attachdir/$value")){
				$delnum++;
				$delname.="{$value}<br>";
			}
			$db->update("DELETE FROM pw_attachs WHERE aid='$key'");
		}
	}
	$F_basename && $basename=$F_basename;
	adminmsg('attachstats_del');
}
/*function getallfile($filedir){
	global $attach;
	$dir=opendir($filedir.'/');
	while($file=readdir($dir)){
		if(($file!=".") && ($file!="..") && ($file!="") && (strpos($file,'html')===false)){
			if (is_dir($filedir.'/'.$file)){
				getallfile($filedir.'/'.$file);
			}else{
				
				
				if(substr(strrchr($filedir,'/'),1)){
					$file = substr(strrchr($filedir,'/'),1).'/'.$file;
				}
				$attach[]=$file;
				//$count++;
				//if($count>1000)return;
			}
		}
	}closedir($dir);
}*/
?>