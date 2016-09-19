<?php
!function_exists('readover') && exit('Forbidden');

function write_config($setting=array()){
	global $tplpath;

	include D_P.'data/sql_config.php';

	$db_hostweb!=0 && $db_hostweb=1;
	empty($pconnect) && $pconnect=0;
	
	if($setting && is_array($setting)){
		$setting['user'] && $manager = $setting['user'];
		$setting['pwd'] && $manager_pwd = $setting['pwd'];
		$setting['hack'] && $db_hackdb = $setting['hack'];
		$setting['pic'] && $picpath = $setting['pic'];
		$setting['att'] && $attachname = $setting['att'];
	}

	$attachdb='';
	foreach($attach_url as $key=>$value){
		$attachdb .= $attachdb ? ",'".$value."'" : "'$value'";
	}

	$hackarray='';
	foreach($db_hackdb as $key=>$value){
		foreach($value as $k => $val){
			$value[$k] = str_replace(array("\\","'"),array("",""),$val);
		}
		$hackarray.="'$value[1]'=>array('$value[0]','$value[1]','$value[2]'),\r\n\t\t";
	}
	if(file_exists(R_P."template/admin_$tplpath")){
		include R_P."template/admin_$tplpath/cp_lang_all.php";
	}else{
		include R_P."template/admin/cp_lang_all.php";
	}

	$writetofile=
"<?php
/**
* $lang[info]
*/
\$dbhost = '$dbhost';			// $lang[dbhost]
\$dbuser = '$dbuser';			// $lang[dbuser]
\$dbpw = '$dbpw';				// $lang[dbpw]
\$dbname = '$dbname';			// $lang[dbname]
\$database = 'mysql';			// $lang[database]
\$PW = '$PW';					// $lang[PW]
\$pconnect = '$pconnect';		// $lang[pconnect]

/*
$lang[charset]
*/
\$charset='$charset';

/**
* $lang[ma_info]
*/
\$manager='$manager';			//$lang[manager_name]
\$manager_pwd='$manager_pwd';	//$lang[manager_pwd]

/**
* $lang[hostweb]
*/
\$db_hostweb='$db_hostweb';		//$lang[ifhostweb]

/*
* $lang[attach_url]
*/
\$attach_url=array($attachdb);

/*
* $lang[pic_att]
*/
\$picpath='$picpath';
\$attachname='$attachname';


/**
* $lang[hackdb]
*/
\$db_hackdb=array(

		$hackarray
		);
".'?>';

	writeover(D_P.'data/sql_config.php',$writetofile);
}
?>