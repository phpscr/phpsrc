<?php
!function_exists('readover') && exit('Forbidden');

$ifupload=0;
$attachs=array();

for($i=1;$i<=$db_attachnum+1;$i++){
	${'atc_attachment'.$i}=$_FILES['atc_attachment'.$i];
	if(is_array(${'atc_attachment'.$i})){
		$atc_attachment=${'atc_attachment'.$i}['tmp_name'];
		$atc_attachment_name=${'atc_attachment'.$i}['name'];
		$atc_attachment_size=${'atc_attachment'.$i}['size'];
	}else{
		$atc_attachment=${'atc_attachment'.$i};
		$atc_attachment_name=${'atc_attachment'.$i.'_name'};
		$atc_attachment_size=${'atc_attachment'.$i.'_size'};
	}
	$needrvrc = ${'atc_downrvrc'.$i};
	$descrip  = Char_cv(${'atc_desc'.$i});
	!is_numeric($needrvrc) && $needrvrc=0;

	if(!$atc_attachment || $atc_attachment== 'none'){
		continue;
	} elseif(function_exists('is_uploaded_file') && !is_uploaded_file($atc_attachment)){
		continue;
	} elseif(!($atc_attachment && $atc_attachment['error']!=4)){
		continue;
	}
	/*
	* 附件上传功能开关 
	*/
	if(!$db_allowupload){
		showmsg('upload_close');
	}

	/**
	*版块权限判断
	*/
	if(($atc_attachment1||$atc_attachment2||$atc_attachment3||$atc_attachment4) && $foruminfo['allowupload'] && strpos($foruminfo['allowupload'],','.$groupid.',')===false && $windid!=$manager){
		Showmsg('upload_forum_right');
	}
	/**
	*用户组权限判断
	*/
	if(($atc_attachment1||$atc_attachment2||$atc_attachment3||$atc_attachment4) && !$foruminfo['allowupload'] && $gp_allowupload==0){
		Showmsg('upload_group_right');
	}
	$_G['uploadmaxsize'] && $db_uploadmaxsize=$_G['uploadmaxsize'];
	if($atc_attachment_size>$db_uploadmaxsize){
		Showmsg('upload_size_error');
	}
	$_G['uploadtype'] && $db_uploadfiletype=$_G['uploadtype'];
	$available_type = explode(' ',trim($db_uploadfiletype));
	$attach_ext = substr(strrchr($atc_attachment_name,'.'),1);
	$attach_ext=strtolower($attach_ext);
	if(empty($attach_ext) || !@in_array($attach_ext,$available_type)){
		Showmsg('upload_type_error');
	}
	if(@in_array($attach_ext,array('php','php3','asp','aspx','jsp','cgi','exe','pl'))) $attach_ext.='_scp';
	if($winddb['uploadtime']<$tdtime){
		$winddb['uploadtime']=$tdtime;
		$winddb['uploadnum']=1;
	} else{
		if($winddb['uploadnum']>=$gp_allownum){
			Showmsg('upload_num_error');
		} else{
			$winddb['uploadtime']=$timestamp;
			$winddb['uploadnum']++;
		}
	}

	$randvar=substr(md5($timestamp+$i),10,15);
	$fileuplodeurl="{$fid}_{$winduid}_{$randvar}.$attach_ext";
	if($db_attachdir) {
		switch($db_attachdir) {
			case 1: $savedir = 'Fid_'.$fid; break;
			case 2: $savedir = 'Type_'.$attach_ext; break;
			case 3: $savedir = 'Mon_'.date('ym'); break;
			case 4: $savedir = 'Day_'.date('ymd'); break;
			default:$savedir = 'Fid_'.$fid; break;
		}
		if(!is_dir("$attachdir/$savedir")) {
			@mkdir("$attachdir/$savedir");
			@chmod("$attachdir/$savedir", 0777);
			@fclose(@fopen("$attachdir/$savedir".'/index.html', 'w'));
			@chmod("$attachdir/$savedir".'/index.html', 0777);
		}
		$fileuplodeurl= $savedir.'/'.$fileuplodeurl;
	}
	$source=$attachdir.'/'.$fileuplodeurl;//版块id_文件名_时间.类型
	if(function_exists("move_uploaded_file") && @move_uploaded_file($atc_attachment, $source)){
		@chmod($source,0777);
		$attach_saved = TRUE;
	}elseif(@copy($atc_attachment, $source)){
		@chmod($source,0777);
		$attach_saved = TRUE;
	}elseif(is_readable($atc_attachment)){
		writeover($source,readover($atc_attachment));
		if(file_exists($source)){
			$attach_saved = TRUE;
			@chmod($source,0777);
		}
	}
	if(empty($attach_saved)){
		Showmsg('upload_error');
	}

	if (eregi("\.(gif|jpg|png|bmp|swf)$",$atc_attachment_name) && function_exists('getimagesize') && !$img_size=getimagesize($source)){
		P_unlink($source);
		Showmsg('upload_content_error');
	}
	if (eregi("\.(gif|jpg|jpeg|png|bmp|swf)$",$atc_attachment_name)){
		$ifupload=1;
		if(eregi("\.swf$",$atc_attachment_name)){
			$type='zip';
		}else{
			$type='img';
		}
	} elseif(eregi("\.(zip|rar)$",$atc_attachment_name)){
		$ifupload=3;
		$type='zip';
	} elseif(eregi("\.txt$",$atc_attachment_name)){
		$safecheckdb=readover($source);
		if (strpos($safecheckdb,"onload")!==false && strpos($safecheckdb,"submit")!==false && strpos($safecheckdb,"post")!==false && strpos($safecheckdb,"form")!==false){
			P_unlink($source);
			Showmsg('upload_content_error');
		} else{
			$ifupload=2;
			$type='txt';
		}
	} else{
		$ifupload=3;
		$type='zip';
	}
	if($type=='img' && $db_watermark && $img_size[0]>$db_waterwidth && $img_size[1]>$db_waterheight){
		if(function_exists('imagecreatefromgif') && function_exists('imagealphablending') && ($attach_ext!='gif' || function_exists('imagegif')) && ($w_img && function_exists('imagecopymerge') || !$w_img && function_exists('imagettfbbox'))){
			require_once(R_P.'require/watermark.php');
			ImgWaterMark("$attachdir/$fileuplodeurl",$db_waterpos,$db_waterimg,$db_watertext,$db_waterfont,$db_watercolor,$db_waterpct);
		}
	}
	$size=ceil(filesize("$attachdir/$fileuplodeurl")/1024);
	$atc_attachment_name=addslashes($atc_attachment_name);
	$db->update("INSERT INTO pw_attachs SET fid='$fid',uid='$winduid',hits=0,name='$atc_attachment_name',type='$type',size='$size',attachurl='$fileuplodeurl',needrvrc='$needrvrc',uploadtime='$timestamp',descrip='$descrip'");
	$aid = $db->insert_id();
	$attachs[$aid] = array(
		'aid'       => $aid,
		'name'      => stripslashes($atc_attachment_name),
		'type'      => $type,
		'attachurl' => $fileuplodeurl,
		'needrvrc'  => $needrvrc,
		'size'      => $size,
		'hits'      => 0,
		'desc'		=> str_replace('\\','',$descrip)
	);
}
$aids = 0;
foreach($attachs as $key => $value){
	$aids .= ','.$key;
}
$attachs = $attachs ?  addslashes(serialize($attachs)) : '';
?>