<?php
!function_exists('readover') && exit('Forbidden');

$ifupload=0;
foreach($_FILES as $key=>$val){
	$i=(int)substr($key,14);
	if($i>$db_attachnum || $i<1){
		unset($_FILES[$key]);
	}
	$ud_name = is_array($val) ? $val['tmp_name'] : $$key;
	if(!$ud_name || $ud_name == 'none'){
		unset($_FILES[$key]);
	} else { 
		$ifupload=1;
	}
}
$attachs=array();
if($ifupload){
	/*
	* 附件上传功能开关 
	*/
	if(!$db_allowupload){
		showmsg('upload_close');
	}
	/**
	*版块权限判断
	*/
	if($foruminfo['allowupload'] && !allowcheck($foruminfo['allowupload'],$groupid,$winddb['groups'])){
		Showmsg('upload_forum_right');
	}
	/**
	*用户组权限判断
	*/
	if(!$foruminfo['allowupload'] && $gp_allowupload==0){
		Showmsg('upload_group_right');
	}
	$gp_uploadmoney && $winddb['money']<$gp_uploadmoney && Showmsg('upload_money_limit');

	$_G['uploadmaxsize'] && $db_uploadmaxsize=$_G['uploadmaxsize'];
	
	if($db_ifftp){
		require_once(R_P.'require/ftp.php');
	}
	foreach($_FILES as $key=>$value){
		$i=(int)substr($key,14);
		if($i>$db_attachnum || $i<1)continue;
		$ifreplace = ($action=='modify' && $replacedb && array_key_exists($i,$replacedb)) ? 1 : 0;
		if(is_array($value)){
			$atc_attachment=$value['tmp_name'];
			$atc_attachment_name=$value['name'];
			$atc_attachment_size=$value['size'];
		}else{
			$atc_attachment=$$key;
			$atc_attachment_name=${$key.'_name'};
			$atc_attachment_size=${$key.'_size'};
		}
		$needrvrc = $ifreplace ? $replacedb[$i]['needrvrc'] : ${'atc_downrvrc'.$i};
		$descrip  = $ifreplace ? $replacedb[$i]['desc'] : Char_cv(${'atc_desc'.$i});
		!is_numeric($needrvrc) && $needrvrc=0;

		if(!if_uploaded_file($atc_attachment)){
			continue;
		}
		if($atc_attachment_size>$db_uploadmaxsize){
			Showmsg('upload_size_error');
		}
		$_G['uploadtype'] && $db_uploadfiletype=$_G['uploadtype'];
		$available_type = explode(' ',trim($db_uploadfiletype));
		$attach_ext = substr(strrchr($atc_attachment_name,'.'),1);
		$attach_ext = strtolower($attach_ext);
		if(empty($attach_ext) || !@in_array($attach_ext,$available_type)){
			Showmsg('upload_type_error');
		}
		if(@in_array($attach_ext,array('php','php3','asp','aspx','jsp','cgi','exe','pl'))) $attach_ext.='_scp';
		if($ifreplace==0){
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
			$fileuplodeurl="{$fid}_{$winduid}_{$randvar}.{$attach_ext}";

			if($db_attachdir){
				switch($db_attachdir) {
					case 1: $savedir = 'Fid_'.$fid; break;
					case 2: $savedir = 'Type_'.$attach_ext; break;
					case 3: $savedir = 'Mon_'.date('ym'); break;
					case 4: $savedir = 'Day_'.date('ymd'); break;
					default:$savedir = 'Fid_'.$fid; break;
				}
				if(!is_dir("$attachdir/$savedir") && $db_ifftp=='0') {
					@mkdir("$attachdir/$savedir");
					@chmod("$attachdir/$savedir", 0777);
					@fclose(@fopen("$attachdir/$savedir".'/index.html', 'w'));
					@chmod("$attachdir/$savedir".'/index.html', 0777);
				}
				$fileuplodeurl= $savedir.'/'.$fileuplodeurl;
			}
		}else{
			$fileuplodeurl=$replacedb[$i]['attachurl'];
		}
		$source = $db_ifftp ? $db_ftpweb."/".$fileuplodeurl : $attachdir.'/'.$fileuplodeurl;
		//版块id_文件名_时间.类型
		if($db_ifftp){
			$ftpsize=$ftp->upload($atc_attachment,$fileuplodeurl);
		}elseif(!postupload($atc_attachment,$source)){
			Showmsg('upload_error');
		}
		if (eregi("\.(gif|jpg|png|bmp|swf)$",$atc_attachment_name) && function_exists('getimagesize')){
			if(!$img_size=getimagesize($source)){
				$db_ifftp ? $ftp->delete($fileuplodeurl) : P_unlink($source);
				Showmsg('upload_content_error');
			}
			if(!$db_ifftp && $attach_ext!='swf' && $db_watermark && $img_size[0]>$db_waterwidth && $img_size[1]>$db_waterheight){
				if (function_exists('imagecreatefromgif') && function_exists('imagealphablending') && ($attach_ext!='gif' || function_exists('imagegif') && ($db_ifgif==2 || $db_ifgif==1 && in_array(PHP_VERSION,array('4.4.3','4.4.4','5.1.5')))) && ($db_waterimg && function_exists('imagecopymerge') || !$db_waterimg && function_exists('imagettfbbox'))){
					require_once(R_P.'require/watermark.php');
					ImgWaterMark($source,$db_waterpos,$db_waterimg,$db_watertext,$db_waterfont,$db_watercolor,$db_waterpct,$db_jpgquality);
				}
			}
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
			$safecheckdb = $db_ifftp ? (function_exists('file_get_contents') ? file_get_contents($source) : '') : readover($source);
			if (strpos($safecheckdb,"onload")!==false && strpos($safecheckdb,"submit")!==false && strpos($safecheckdb,"post")!==false && strpos($safecheckdb,"form")!==false){
				$db_ifftp ? $ftp->delete($fileuplodeurl) : P_unlink($source);
				Showmsg('upload_content_error');
			} else{
				$ifupload=2;
				$type='txt';
			}
		} else{
			$ifupload=3;
			$type='zip';
		}
		$size = $db_ifftp ? ceil($ftpsize/1024) : ceil(filesize("$attachdir/$fileuplodeurl")/1024);
		$atc_attachment_name=addslashes($atc_attachment_name);
		if($ifreplace==0){
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
		}else{
			$aid=$replacedb[$i]['aid'];
			$db->update("UPDATE pw_attachs SET name='$atc_attachment_name',type='$type',size='$size',attachurl='$fileuplodeurl',needrvrc='$needrvrc',uploadtime='$timestamp',descrip='$descrip' WHERE aid='$aid'");
			$oldattach[$aid]['name']=$atc_attachment_name;
			$oldattach[$aid]['type']=$type;
			$oldattach[$aid]['size']=$size;
		}
	}
}
if($ftp){
	$ftp->close();unset($ftp);
}
$aids = 0;
foreach($attachs as $key => $value){
	$aids .= ','.$key;
}
$attachs = $attachs ?  addslashes(serialize($attachs)) : '';
?>