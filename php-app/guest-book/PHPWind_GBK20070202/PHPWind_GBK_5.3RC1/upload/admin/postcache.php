<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$admin_file?adminjob=postcache";

if(empty($action)){
	$motiondb=$facedb=array();
	$query=$db->query("SELECT * FROM pw_actions");
	while($postcache=$db->fetch_array($query)){
		$motiondb[]=$postcache;
	}
	$query=$db->query("SELECT * FROM pw_smiles WHERE type=0 ORDER BY vieworder");
	while($postcache=$db->fetch_array($query)){
		$facedb[]=$postcache;
	}
	$shownum = count($facedb);
	@extract($db->get_one("SELECT db_value AS fc_shownum FROM pw_config WHERE db_name='fc_shownum'"));

	include PrintEot('postcache');exit;
} elseif($_POST['action']=='addact'){
	if(empty($images) || empty($names) || empty($descrips)){
		adminmsg('postcache_emmpty');
	}
	foreach($names as $key=>$value){
		if($value && $images[$key] && $descrips[$key]){
			$value=Char_cv($value);
			$value1=Char_cv($images[$key]);
			$value2=Char_cv($descrips[$key]);
			$db->update("INSERT INTO pw_actions(images,name,descrip) VALUES('$value1','$value','$value2') ");
		}
	}
	updatecache_p();
	adminmsg('operate_success');
} elseif($_POST['action']=='addface'){
	if(empty($path) || !is_dir("$imgdir/post/smile/$path")){
		adminmsg('smile_path_error');
	}
	empty($name) && adminmsg('smile_name_error');
	$rs=$db->get_one("SELECT COUNT(*) AS sum FROM pw_smiles WHERE path='$path'");
	$rs['sum']>=1 && adminmsg('smile_rename');
	
	$vieworder=(int)$vieworder;
	$db->update("INSERT INTO pw_smiles(path,name,vieworder) VALUES('$path','$name','$vieworder')");
	updatecache_p();
	adminmsg('operate_success');
} elseif($_POST['action']=='delete'){
	$delids='';
	if(is_array($delid)){
		foreach($delid as $value){
			is_numeric($value) && $delids.=$value.',';
		}
	}
	$delids=substr($delids,0,-1);
	(!$delids || $table!='pw_actions' && $table!='pw_smiles') && adminmsg('operate_error');
	$db->update("DELETE FROM $table WHERE id IN($delids)");
	updatecache_p();
	adminmsg('operate_success');
} elseif($_POST['action']=='update'){
	updatecache_p();
	adminmsg('operate_success');
} elseif($_POST['action']=='editsmiles'){
	foreach($vieworder as $key=>$value){
		$value=(int)$value;
		$smilesname=Char_cv($name[$key]);
		$db->update("UPDATE pw_smiles SET name='$smilesname',vieworder='$value' WHERE id='$key'");
	}
	$db->pw_update(
		"SELECT db_value FROM pw_config WHERE db_name='fc_shownum'",
		"UPDATE pw_config SET db_value='$shownum' WHERE db_name='fc_shownum'",
		"INSERT INTO pw_config (db_name,db_value) VALUES ('fc_shownum','$shownum')"
	);
	updatecache_p();
	adminmsg('operate_success');
} elseif($action=='delete'){
	$db->update("DELETE FROM pw_smiles WHERE id='$id'");
	$db->update("DELETE FROM pw_smiles WHERE type='$id'");
	updatecache_p();
	adminmsg('operate_success');
} elseif($action=='smilemanage'){
	@extract($db->get_one("SELECT * FROM pw_smiles WHERE id='$id'"));
	
	$rs=$db->query("SELECT * FROM pw_smiles WHERE type='$id' ORDER BY vieworder");
	$smiles_new = $smiles_old = $smiles = array();
	$picext = array("gif","bmp","jpeg","jpg","png");
	while($smiledb=$db->fetch_array($rs)) {
		$smiledb['src']="$imgpath/post/smile/$path/{$smiledb[path]}";
		$smiles_old[]=$smiledb['path'];
		$smiles[]=$smiledb;
	}
	$smilepath="$imgdir/post/smile/$path";

	$fp=opendir($smilepath);
	$i=0;
	while($smilefile = readdir($fp)){
		if(in_array(strtolower(end(explode(".",$smilefile))),$picext)){
			if(!in_array($smilefile,$smiles_old)){
				$i++;
				$smiles_new[$i]['path']=$smilefile;
				$smiles_new[$i]['src']="$imgpath/post/smile/$path/$smilefile";
			}
		}
	}
	include PrintEot('postcache');exit;
} elseif($_POST['action']=='addsmile'){
	foreach($add as $value){
		$db->update("INSERT INTO pw_smiles SET path='$value',type='$id'");
	}
	updatecache_p();
	adminmsg('operate_success',"$basename&action=smilemanage&id=$id");
} elseif($action=='delsmile'){
	$db->update("DELETE FROM pw_smiles WHERE id='$smileid'");
	updatecache_p();
	adminmsg('operate_success',"$basename&action=smilemanage&id=$typeid");
} elseif($_POST['action']=='smilevieworder'){
	foreach($vieworder as $key=>$value){
		$value=(int)$value;
		$db->update("UPDATE pw_smiles SET vieworder='$value' WHERE id='$key'");
	}
	updatecache_p();
	adminmsg('operate_success',"$basename&action=smilemanage&id=$id");
}
?>