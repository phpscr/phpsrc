<?php
!function_exists('adminmsg') && exit('Forbidden');
$basename="$amind_file?adminjob=hack&hackset=advert";

if($job=='add' || $job=='edit'){
	$adtype=array();
	$query = $db->query("SELECT varname,title FROM pw_modules WHERE type=6 AND title!='' GROUP BY varname");
	while($rt = $db->fetch_array($query)){
		if(!in_array($rt['varname'],array('header','footer','text','article'))){
			$adtype[]=$rt;
		}
	}
}
if(!$job){
	$query = $db->query("SELECT id,varname,state,vieworder,title,config FROM pw_modules WHERE type=6 ORDER BY varname,vieworder");
	while($rt = $db->fetch_array($query)){
		$config = unserialize($rt['config']);
		$rt['fid']		 = explode(',',$config['fid']);
		$rt['adtitle']	 = $config['title'];
		$rt['starttime'] = $config['starttime'];
		$rt['endtime']	 = $config['endtime'];
		$moduledb[]=$rt;
	}
	include_once(PrintEot('advert'));exit;
}elseif($job=='add'){
	if(!$step){
		$style='code';
		$selids_01 = 'checked';
		$config['starttime'] = get_date($timestamp,'Y-m-d');
		$config['endtime']	 = get_date($timestamp+31536000,'Y-m-d');
		include_once(PrintEot('advert'));exit;
	}elseif($step=='2'){
		!$varname && adminmsg('module_adderror');
		if($module['style'] == 'code' && !$module['htmlcode']){
			adminmsg('advert_code_error');
		}elseif($module['style'] == 'txt' && (!$module['title'] || !$module['link'])){
			adminmsg('advert_txt_error');
		}elseif($module['style'] == 'img' && (!$module['url'] || !$module['link'])){
			adminmsg('advert_img_error');
		}elseif($module['style'] == 'flash' && !$module['link']){
			adminmsg('advert_flash_error');
		}
		$fids = '';
		foreach($selids as $key=>$val){
			if(is_numeric($val)){
				$fids .= $fids ? ','.$val : $val;
			}
		}
		$module['fid'] = $fids;
		foreach($module as $key=>$value){
			$module[$key] = stripslashes($value);
		}
		$config = addslashes(serialize($module));
		$db->update("INSERT INTO pw_modules(type,varname,state,vieworder,title,config) VALUES('6','$varname','1','$vieworder','$title','$config')");
		updatecache_advert();
		$basename="$amind_file?adminjob=hack&hackset=advert";
		adminmsg('operate_success');
	}
}elseif($job=='edit'){
	if(!$step){
		$rt = $db->get_one("SELECT * FROM pw_modules WHERE type=6 AND id='$id'");
		if(!$rt){
			adminmsg('module_id_error');
		}
		$config = unserialize($rt['config']);
		HtmlConvert($rt);
		HtmlConvert($config);

		$style=$config['style'];
		${'style_'.$config['style']} = 'selected';
		${'method_'.$config['method']}='checked';
		${'order_'.$config['order']} = "selected";

		$fids = explode(',',$config['fid']);
		foreach($fids as $k=>$v){
			if($v > 0){
				${'selids_'.$v} = 'checked';
			}else{
				${'selids_0'.abs($v)} = 'checked';
			}
		}
		include_once(PrintEot('advert'));exit;
	}elseif($step=='2'){
		$basename="$amind_file?adminjob=hack&hackset=advert&job=edit&id=$id";
		!$varname && adminmsg('module_adderror');
		if($module['style'] == 'code' && !$module['htmlcode']){
			adminmsg('advert_code_error');
		}elseif($module['style'] == 'txt' && (!$module['title'] || !$module['link'])){
			adminmsg('advert_txt_error');
		}elseif($module['style'] == 'img' && (!$module['url'] || !$module['link'])){
			adminmsg('advert_img_error');
		}elseif($module['style'] == 'flash' && !$module['link']){
			adminmsg('advert_flash_error');
		}
		if(is_array($selids)){
			$fids = '';
			foreach($selids as $key=>$val){
				if(is_numeric($val)){
					$fids .= $fids ? ','.$val : $val;
				}
			}
			$module['fid'] = $fids;
		}else{
			$module['fid'] = '';
		}
		$module['descrip'] = str_replace("\n",'<br />',$module['descrip']);
		foreach($module as $key=>$value){
			$module[$key] = stripslashes($value);
		}
		$config = addslashes(serialize($module));
		$db->update("UPDATE pw_modules SET varname='$varname',vieworder='$vieworder',title='$title',config='$config' WHERE type='6' AND id='$id'");
		updatecache_advert();
		$basename="$amind_file?adminjob=hack&hackset=advert";
		adminmsg('operate_success');
	}
}elseif($job=='del'){
	if($selid = checkselid($selid)){
		$db->update("DELETE FROM pw_modules WHERE type='6' AND id IN($selid)");
	}
	$db->update("UPDATE pw_modules SET state=0 WHERE type='6'");
	if($applyid = checkselid($applyid)){
		$db->update("UPDATE pw_modules SET state=1 WHERE type='6' AND id IN($applyid)");
	}
	updatecache_advert();
	adminmsg('operate_success');
}
?>