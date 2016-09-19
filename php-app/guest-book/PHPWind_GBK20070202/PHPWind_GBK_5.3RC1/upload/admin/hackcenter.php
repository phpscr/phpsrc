<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=hackcenter";
if(!$action){
	include PrintEot('hackcenter');exit;
} elseif($action=='edit'){
	if(!$_POST['step']){
		$hackname=$db_hackdb[$id][0];
		$hacksign=$db_hackdb[$id][1];
		$db_hackdb[$id][2]=='2' ? $method_2='checked' : ($db_hackdb[$id][2]=='1' ? $method_1='checked' : $method_0='checked');
		include PrintEot('hackcenter');exit;
	} else{
		if(!$hackname || !$hacksign){
			adminmsg('hackcenter_empty');
		}
		foreach($db_hackdb as $key=>$value){
			if($key!=$id && $hacksign==$value[1]){
				adminmsg('hackcenter_sign_exists');
			}
		}
		$db_hackdb[$id]=array($hackname,$hacksign,$ifopen);

		require_once(R_P.'require/updateset.php');
		$setting = array();
		$setting['hack'] = $db_hackdb;
		write_config($setting);

		adminmsg('operate_success');
	}
} elseif($action=='add'){
	if(!$_POST['step']){
		$method_1='checked';
		include PrintEot('hackcenter');exit;
	} else{
		if(!$hackname || !$hacksign){
			adminmsg('hackcenter_empty');
		}
		if(!is_dir(R_P."hack/$hacksign")){
			adminmsg('hackcentre_upload');
		}
		foreach($db_hackdb as $key=>$value){	
			if($hacksign==$value[1]){
				adminmsg('hackcenter_sign_exists');
			}
		}
		if(file_exists(R_P."hack/$hacksign/sql.txt")){
			updatedb(R_P."hack/$hacksign/sql.txt");
			P_unlink(R_P."hack/$hacksign/sql.txt");
		}
		$db_hackdb[]=array($hackname,$hacksign,$ifopen);

		require_once(R_P.'require/updateset.php');
		$setting = array();
		$setting['hack'] = $db_hackdb;
		write_config($setting);

		adminmsg('operate_success');
	}
} elseif($action=='del'){
	PostCheck($verify);
	if(!$db_hackdb[$id]){
		adminmsg('hackcenter_del');
	}
	unset($db_hackdb[$id]);

	require_once(R_P.'require/updateset.php');
	$setting = array();
	$setting['hack'] = $db_hackdb;
	write_config($setting);

	if(P_rmdir(R_P."hack/$id")===false){
		adminmsg('hackcenter_del_fail');
	}else{
		adminmsg('operate_success');
	}
}

function P_rmdir($pathname){
	strpos($pathname,'..')!==false && exit('Forbidden');
	if(is_dir($pathname)){
		if($handle = opendir($pathname)){
			while(($file = readdir($handle))){
				if($file == "." || $file == ".."){
					continue;
				}
				if(is_dir($pathname."/".$file)){
					P_rmdir($pathname."/".$file);
				}else{
					P_unlink($pathname."/".$file);
				}
			}
			closedir($handle);
			return rmdir($pathname);
		}
		return false;
	}else{
		return 0;
	}
}
function updatedb($filename) {
	global $db,$charset;
	
	$sql=file($filename);
	$query='';
	$num=0;
	foreach($sql as $key => $value){
		$value=trim($value);
		if(!$value || $value[0]=='#') continue;
		if(eregi("\;$",$value)){
			$query.=$value;
			if(eregi("^CREATE",$query)){
				$extra = substr(strrchr($query,')'),1);
				$tabtype = substr(strchr($extra,'='),1);
				$tabtype = substr($tabtype, 0, strpos($tabtype,strpos($tabtype,' ') ? ' ' : ';'));
				$query = str_replace($extra,'',$query);
				if($db->server_info() > '4.1'){
					$extra = $charset ? "ENGINE=$tabtype DEFAULT CHARSET=$charset;" : "ENGINE=$tabtype;";
				}else{
					$extra = "TYPE=$tabtype;";
				}
				$query .= $extra;
			}elseif(eregi("^DROP",$query) || eregi("^DELETE",$query)){
				continue;
			}elseif(eregi("^INSERT",$query)){
				$query='REPLACE '.substr($query,6);
			}
			$db->query($query);
			$query='';
		} else{
			$query.=$value;
		}
	}
}
?>