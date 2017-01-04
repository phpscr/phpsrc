<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=manager";

include D_P.'data/sql_config.php';
if(!$_POST['action']){
	include PrintEot('manager');exit;
} else{
	$rs=$db->get_one("SELECT uid FROM pw_members WHERE username='$username'");
	if(!$rs && $username!=$admin_name){
		$errorname=$username;
		adminmsg('user_not_exists');
	}
	if($password){
		$check_pwd!=$password && adminmsg('password_confirm');
		$S_key=array("\\",'&',' ',"'",'"','/','*',',','<','>',"\r","\t","\n",'#');
		foreach($S_key as $value){
			if (strpos($password,$value)!==false){ 
				adminmsg('illegal_password'); 
			}
		}
		$password=md5($password);
	} else{
		$password=$manager_pwd;
	}
	if($db_hostweb!=0){
		$db_hostweb=1;
	}
	if(empty($pconnect)){
		$pconnect=0;
	}
	$hackarray='';
	foreach($db_hackdb as $key=>$value){
		$hackarray.="'$value[1]'=>array('$value[0]','$value[1]','$value[2]','$value[3]','$value[4]','$value[5]'),\r\n\t\t";
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
\$database = 'mysql';			//数据库类型
\$PW = '$PW';					//表区分符
\$pconnect = $pconnect;					//是否持久连接

/*
MYSQL编码设置
如果您的论坛出现乱码现象，需要设置此项来修复
请不要随意更改此项，否则将可能导致论坛出现乱码现象
*/
\$charset='$charset';

/**
* 论坛创始人,拥有论坛所有权限
*/
\$manager='$username';			//管理员用户名
\$manager_pwd='$password';	//管理员密码

/**
* 镜像站点设置
*/
\$db_hostweb=$db_hostweb;			//是否为主站点

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
	if(!$rs && $username==$admin_name){
		$db->update("INSERT INTO pw_members SET username='$username',password='$password',groupid='3',regdate='$timestamp'");
	} else{
		$db->update("UPDATE pw_members SET password='$password',groupid='3' WHERE username='$username'");
	}
	adminmsg('operate_success');
}
?>