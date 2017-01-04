<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=hackcenter";
if(!$action){
	include PrintEot('hackcenter');exit;
} elseif($action=='edit'){
	if(!$step){
		$hackname=$db_hackdb[$id][0];
		$hacksign=$db_hackdb[$id][1];
		$filename1=$db_hackdb[$id][2];
		$filename2=$db_hackdb[$id][3];
		$hackfile=$db_hackdb[$id][5];
		$db_hackdb[$id][4] ? $H_open='checked' : $H_close='checked';
		include PrintEot('hackcenter');exit;
	} else{
		if(!$hackname || !$hacksign){
			adminmsg('hackcenter_empty');
		}
		$oldsign=$db_hackdb[$id][1];
		$oldfile1=$db_hackdb[$id][2];
		$oldfile2=$db_hackdb[$id][3];
		foreach($db_hackdb as $key=>$value){
			if($key!=$id){
				if($hacksign==$value[1]){
					adminmsg('hackcenter_sign_exists');
				}
			}
		}
		$db_hackdb[$id]=array($hackname,$hacksign,$filename1,$filename2,$ifopen,$hackfile);
		$hackarray='';
		foreach($db_hackdb as $key=>$value){
			foreach($value as $k => $val){
				$value[$k] = str_replace(array("\\","'"),array("",""),$val);
			}
			$hackarray.="'$value[1]'=>array('$value[0]','$value[1]','$value[2]','$value[3]','$value[4]','$value[5]'),\r\n\t\t";
		}
		write_config($hackarray);
		updatecache_h();
		adminmsg('operate_success');
	}
} elseif($action=='add'){
	if(!$step){
		$H_open='checked';
		include PrintEot('hackcenter');exit;
	} else{
		if(!$hackname || !$hacksign){
			adminmsg('hackcenter_empty');
		}
		foreach($db_hackdb as $key=>$value){	
			if($hacksign==$value[1]){
				adminmsg('hackcenter_sign_exists');
			}
		}
		$db_hackdb[]=array($hackname,$hacksign,$filename1,$filename2,$ifopen,$hackfile);
		$hackarray='';
		foreach($db_hackdb as $key=>$value){
			foreach($value as $k => $val){
				$value[$k] = str_replace(array("\\","'"),array("",""),$val);
			}
			$hackarray.="'$value[1]'=>array('$value[0]','$value[1]','$value[2]','$value[3]','$value[4]','$value[5]'),\r\n\t\t";
		}
		write_config($hackarray);
		updatecache_h();adminmsg('operate_success');
	}
} elseif($action=='del'){
	//if(!$db_hackdb[$id]){
	//	adminmsg('hackcenter_del');
	//}
	$delhackfile=$db_hackdb[$id][5];
	$delfile_a=explode(",",$delhackfile);
	foreach($delfile_a as $value){
		P_unlink($value);
	}
	unset($db_hackdb[$id]);
	$hackarray='';
	foreach($db_hackdb as $key=>$value){
		$hackarray.="'$value[1]'=>array('$value[0]','$value[1]','$value[2]','$value[3]','$value[4]','$value[5]'),\r\n\t\t";
	}
	write_config($hackarray);
	updatecache_h();adminmsg('operate_success');
}
function write_config($hackarray){
	include(D_P.'data/sql_config.php');

	if($db_hostweb!=0){
		$db_hostweb=1;
	}
	if(empty($pconnect)){
		$pconnect=0;
	}
	$writetofile=
"<?php
/**
* 以下变量需根据您的服务器说明档修改
*/
\$dbhost = '$dbhost';			// 数据库服务器
\$dbuser = '$dbuser';			// 数据库用户名
\$dbpw = '$dbpw';				// 数据库密码
\$dbname = '$dbname';			// 数据库名
\$database = 'mysql';			// 数据库类型
\$PW = '$PW';					// 表区分符
\$pconnect = '$pconnect';			// 是否持久连接

/*
MYSQL编码设置
如果您的论坛出现乱码现象，需要设置此项来修复
请不要随意更改此项，否则将可能导致论坛出现乱码现象
*/
\$charset='$charset';

/**
* 论坛创始人,拥有论坛所有权限
*/
\$manager='$manager';			// 管理员用户名
\$manager_pwd='$manager_pwd';	// 管理员密码
\$adminemail = '$adminemail';	// 论坛系统 Email

/**
* 镜像站点设置
*/
\$db_hostweb='$db_hostweb';		// 是否为主站点

/*
* 附件url地址，以http:// 开头的绝对地址  为空使用默认
*/
\$attach_url='$attach_url';



/**
* 插件配置
*/
\$db_hackdb=array(

		$hackarray
		);
".'?>';

	writeover(D_P.'data/sql_config.php',$writetofile);
}
?>