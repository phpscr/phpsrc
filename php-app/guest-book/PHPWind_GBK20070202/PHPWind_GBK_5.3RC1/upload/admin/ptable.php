<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=ptable";

if(!$action){
	if(!$_POST['step']){
		$postdb=array();
		$query = $db->query("SHOW TABLE STATUS LIKE 'pw_posts%'");
		while($rs=$db->fetch_array($query)){
			$rs['Data_length'] = round(($rs['Data_length']+$rs['Index_length'])/1048576,2);
			$rs['key'] = substr(str_replace($GLOBALS['PW'],'pw_',$rs['Name']),8);
			$rs['sel'] = $rs['key']==$db_ptable ? 'checked' : '';
			$pw_posts  = GetPtable($rs['key']);
			@extract($db->get_one("SELECT MIN(tid) AS tmin,MAX(tid) AS tmax FROM $pw_posts"));
			$rs['tmin'] = $tmin;
			$rs['tmax'] = $tmax;
			$postdb[]=$rs;
		}
		require_once PrintEot('ptable');
	}else{
		$db->pw_update(
			"SELECT db_name FROM pw_config WHERE db_name='db_ptable'",
			"UPDATE pw_config SET db_value='$ktable' WHERE db_name='db_ptable'",
			"INSERT INTO pw_config(db_name,db_value) VALUES ('db_ptable','$ktable')"
		);
		$plist = '';
		$query = $db->query("SHOW TABLE STATUS LIKE 'pw_posts%'");
		while($rs=$db->fetch_array($query)){
			$j = str_replace($PW.'posts','',$rs['Name']);
			$j && $plist .= $j.',';
		}		
		$plist = substr($plist,0,-1);
		$db->pw_update(
			"SELECT db_name FROM pw_config WHERE db_name='db_plist'",
			"UPDATE pw_config SET db_value='$plist' WHERE db_name='db_plist'",
			"INSERT INTO pw_config(db_name,db_value) VALUES ('db_plist','$plist')"
		);
		updatecache_c();

		adminmsg('operate_success');
	}
}elseif($action=='create'){
	if(!$_POST['step']){
		require_once PrintEot('ptable');
	}else{
		!is_numeric($num) && adminmsg('only_numeric');
		$i=0;
		$plist='';
		$query = $db->query("SHOW TABLE STATUS LIKE 'pw_posts%'");
		while($rs=$db->fetch_array($query)){
			$i++;
			if($rs['Name']==$PW.'posts'.$num){
				adminmsg('table_exists');
			}
			$j=str_replace($PW.'posts','',$rs['Name']);
			$j && $plist .= $j.',';
		}
		if($i==1){
			extract($db->get_one("SELECT MAX(pid) AS pid FROM pw_posts"));
			$db->update("INSERT INTO pw_pidtmp (pid) VALUES ('$pid')");
		}
		$table='pw_posts'.$num;
		$CreatTable = $db->get_one("SHOW CREATE TABLE pw_posts");
		$sql=str_replace($CreatTable['Table'],$table,$CreatTable['Create Table']);
		$db->update($sql);

		$plist .= $num;
		$db->pw_update(
			"SELECT db_name FROM pw_config WHERE db_name='db_plist'",
			"UPDATE pw_config SET db_value='$plist' WHERE db_name='db_plist'",
			"INSERT INTO pw_config(db_name,db_value) VALUES ('db_plist','$plist')"
		);
		updatecache_c();
		
		adminmsg('operate_success');
	}
}elseif($action=='movedata'){
	if(!$step){
		$table_sel='';
		$query = $db->query("SHOW TABLE STATUS LIKE 'pw_posts%'");
		while($rs=$db->fetch_array($query)){
			$key = substr(str_replace($GLOBALS['PW'],'pw_',$rs['Name']),8);
			$table_sel .= "<option value=\"$key\">$rs[Name]</option>";
		}
		require_once PrintEot('ptable');
	} else{
		$step>1 && PostCheck($verify);
		set_time_limit(0);
		$db_bbsifopen=='1' && adminmsg('bbs_open');

		$tfrom = (int) $tfrom;
		$tto   = (int) $tto;
		if($tfrom==$tto){
			adminmsg('table_same');
		}
		!$lines && $lines=200;
		!$tstart && $tstart=0;

		$ftable = $tfrom ? 'pw_posts'.$tfrom : 'pw_posts';
		$ttable = $tto   ? 'pw_posts'.$tto   : 'pw_posts';
		if(!$tend){
			@extract($db->get_one("SELECT MAX(tid) AS tend FROM $ftable"));
		}
		$end = $tstart + $lines;
		$end > $tend && $end = $tend;
		$db->update("INSERT INTO $ttable SELECT * FROM $ftable WHERE tid>'$tstart' AND tid<='$end'");
		$db->update("DELETE FROM $ftable WHERE tid>'$tstart' AND tid<='$end'");
		$db->update("UPDATE pw_threads SET ptable='$tto' WHERE tid>'$tstart' AND tid<='$end' AND ptable='$tfrom'");

		if($end<$tend){
			$step++;
			$j_url="$basename&action=$action&step=$step&tstart=$end&tend=$tend&tfrom=$tfrom&tto=$tto&lines=$lines";
			adminmsg('table_change',EncodeUrl($j_url),2);
		}else{
			adminmsg('operate_success');
		}		
	}
}
?>