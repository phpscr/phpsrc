<?php
require_once('global.php');
require_once(R_P.'require/checkpass.php');

if(!$passport_ifopen || $passport_type != 'client'){
	exit("Passport closed(PHPWind)");
}

if(md5($action.$userdb.$forward.$passport_key) != $verify){
	exit('Illegal request(PHPWind)');
}
$_db_hash=$db_hash;

$db_hash=$passport_key;
parse_str(StrCode($userdb,'DECODE'),$userdb);

if($action=='login'){
	foreach($userdb as $key=>$val){
		$userdb[$key] = addslashes($val);
	}
	if(!$userdb['time'] || !$userdb['username'] || !$userdb['password']){
		exit("Lack of parameters(PHPWind)");
	}
	if($timestamp-$userdb['time']>3600){
		exit('Passport request expired(PHPWind)');
	}

	$member_field = array('username','password','email');
	$memberdata_field = array('rvrc','money','credit','currency');

	$sql='';
	foreach($member_field as $key=>$val){
		$sql .= ','.$val;
	}
	$rt=$db->get_one("SELECT uid $sql FROM pw_members WHERE username='$userdb[username]'");
	if($rt){
		$sql=$sql2='';
		foreach($userdb as $key=>$val){
			if(in_array($key,$member_field) && $rt[$key] != $val){
				$sql  .= $sql ? ",$key='$val'" : "$key='$val'";
			}elseif(in_array($key,$memberdata_field) && strpos(",$passport_credit,",",$key,")!==false){
				$sql2 .= $sql2 ? ",$key='$val'" : "$key='$val'";
			}
		}
		$sql  && $db->update("UPDATE pw_members SET $sql WHERE uid='$rt[uid]'");
		$sql2 && $db->update("UPDATE pw_memberdata SET $sql2 WHERE uid='$rt[uid]'");

		$winduid = $rt['uid'];
	}else{
		$sql1=$sql2=$sql3=$sql4='';
		foreach($userdb as $key=>$val){
			if(in_array($key,$member_field)){
				$sql1 .= $sql1 ? ','.$key  : $key;
				$sql2 .= $sql2 ? ",'$val'" : "'$val'";
			}elseif(in_array($key,$memberdata_field) && strpos(",$passport_credit,",",$key,")!==false){
				$sql3 .= ",$key";
				$sql4 .= ",'$val'";
			}
		}
		$db->update("REPLACE INTO pw_members($sql1,groupid,memberid,gender,regdate,signchange) VALUES($sql2,'-1','8','0','$timestamp','1')");
		$winduid = $db->insert_id();
		$db->update("REPLACE INTO pw_memberdata (uid,postnum,lastvisit,thisvisit,onlineip $sql3) VALUES ('$winduid','0','$timestamp','$timestamp','$onlineip' $sql4)");

		$db->update("UPDATE pw_bbsinfo SET newmember='$userdb[username]',totalmember=totalmember+1 WHERE id='1'");
	}

	$db_hash=$_db_hash;
	$windpwd = PwdCode($userdb['password']);
	Cookie("winduser",StrCode($winduid."\t".$windpwd),$userdb['cktime']);
	Cookie('lastvisit','',0);
	Loginipwrite();

	if($userdb['url']){
		$clienturl = explode(',',$userdb['url']);
		$jumpurl='';
		while(!$jumpurl){
			$jumpurl=array_shift($clienturl);
		}
		$userdb['url'] = implode(',',$clienturl);
	}

	if($jumpurl){
		$userdb_encode='';
		foreach($userdb as $key=>$val){
			$userdb_encode .= $userdb_encode ? "&$key=$val" : "$key=$val";
		}
		$db_hash=$passport_key;
		$userdb_encode=str_replace('=','',StrCode($userdb_encode));

		$verify = md5("login$userdb_encode$forward$passport_key");
		ObHeader("$jumpurl/passport_client.php?action=login&userdb=".rawurlencode($userdb_encode)."&forward=".rawurlencode($forward)."&verify=".rawurlencode($verify));
	}else{
		ObHeader($forward ? $forward : $passport_serverurl);
	}
}elseif($action=='quit'){
	$db_hash=$_db_hash;
	Loginout();

	if($userdb['url']){
		$clienturl = explode(',',$userdb['url']);
		$jumpurl='';
		while(!$jumpurl){
			$jumpurl=array_shift($clienturl);
		}
		$userdb['url'] = implode(',',$clienturl);
	}

	if($jumpurl){
		$userdb_encode='';
		foreach($userdb as $key=>$val){
			$userdb_encode .= $userdb_encode ? "&$key=$val" : "$key=$val";
		}
		$db_hash=$passport_key;
		$userdb_encode=str_replace('=','',StrCode($userdb_encode));

		$verify = md5("quit$userdb_encode$forward$passport_key");
		ObHeader("$jumpurl/passport_client.php?action=quit&userdb=".rawurlencode($userdb_encode)."&forward=".rawurlencode($forward)."&verify=".rawurlencode($verify));
	}else{
		ObHeader($forward ? $forward : $passport_serverurl);
	}
}
?>