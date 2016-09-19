<?php
!function_exists('adminmsg') && exit('Forbidden');

if(!$action){
	if(!$step){
		include_once(D_P.'data/bbscache/md_config.php');
		ifcheck($md_ifopen,'ifopen');
		ifcheck($md_ifmsg,'ifmsg');
		require_once PrintHack('admin');
	}elseif($step=='2'){
		if(is_array($groups)){
			$config['md_groups']=','.implode(',',$groups).',';
		}else{
			$config['md_groups']='';
		}
		foreach($config as $key=>$value){
			$rt=$db->get_one("SELECT hk_name FROM pw_hack WHERE hk_name='$key'");
			if($rt){
				$db->update("UPDATE pw_hack SET hk_value='$value' WHERE hk_name='$key'");
			}else{
				$db->update("INSERT INTO pw_hack(hk_name,hk_value) VALUES ('$key','$value')");
			}
		}
		updatecache_md();
		adminmsg('operate_success');
	}
}elseif($action=='edit'){
	if(!$step){
		$query = $db->query("SELECT * FROM pw_medalinfo");
		while ($rt = $db->fetch_array($query)){
			$medaldb[]=$rt;
		}
		require_once PrintHack('admin');
	}elseif($step=='2'){
		foreach($medal as $key=>$value){
			$value['name']	= Char_cv($value['name']);
			$value['intro']	= Char_cv($value['intro']);
			$value['picurl']= Char_cv($value['picurl']);
			$db->update("UPDATE pw_medalinfo SET name='$value[name]',intro='$value[intro]',picurl='$value[picurl]' WHERE id='$key'");
		}
		$basename="$admin_file?adminjob=hack&hackset=medal&action=edit";
		updatecache_mddb();
		adminmsg('operate_success');
	}
}elseif($action=='add'){	
	if(!$step){
		require_once PrintHack('admin');
	}elseif($step=='2'){
		$newname   = Char_cv($newname);
		$newintro  = Char_cv($newintro);
		$newpicurl = Char_cv($newpicurl);
		$db->update("INSERT INTO pw_medalinfo(name,intro,picurl) VALUES('$newname','$newintro','$newpicurl')");
		$basename="$admin_file?adminjob=hack&hackset=medal&action=edit";
		updatecache_mddb();
		adminmsg('operate_success');
	}
}elseif($action=='del'){
	$db->update("DELETE FROM pw_medalinfo WHERE id='$id'");
	$basename="$admin_file?adminjob=hack&hackset=medal&action=edit";
	updatecache_mddb();
	adminmsg('operate_success');
}
?>