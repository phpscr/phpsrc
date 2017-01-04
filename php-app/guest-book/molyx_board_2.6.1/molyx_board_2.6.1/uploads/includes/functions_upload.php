<?php
#**************************************************************************#
#   MolyX2
#   ------------------------------------------------------
#   copyright (c) 2004-2006 HOGE Software.
#   official forum : http://molyx.com
#   license : MolyX License, http://molyx.com/license
#   MolyX2 is free software. You can redistribute this file and/or modify
#   it under the terms of MolyX License. If you do not accept the Terms
#   and Conditions stated in MolyX License, please do not redistribute
#   this file.Please visit http://molyx.com/license periodically to review
#   the Terms and Conditions, or contact HOGE Software.
#**************************************************************************#
if(!defined('IN_MXB')) exit('Access denied.Sorry, you can not access this file directly.');
@set_time_limit(0);
class functions_upload
{
	var $upload_form = 'attachment';
	var $filename = '';
	var $filepath = './';
	var $maxfilesize = 0;
	var $force_extension = 'attach';
	var $allow_extension = array();
	var $image_extension = array('gif', 'jpeg', 'jpg', 'png');
	var $file_extension = '';
	var $real_file_extension = array();
	var $error_no = 0;
	var $is_image = array();
	var $original_file_name = array();
	var $parsed_file_name = array();
	var $uploadfile = array();

	function upload_process()
	{
		global $bbuserinfo, $_INPUT;
		$attach_num = intval($_INPUT['attach_num']);
		$this->filepath = preg_replace("#/$#", '', $this->filepath);
		if ($bbuserinfo['attachnum'] && $attach_num > $bbuserinfo['attachnum']) {
			$attach_num = $bbuserinfo['attachnum'];
		}
		for ($i = 0; $i < $attach_num; $i++) {
			$fileinfo['name'] = $_FILES[$this->upload_form.$i]['name'];
			$fileinfo['size'] = $_FILES[$this->upload_form.$i]['size'];
			$fileinfo['type'] = $_FILES[$this->upload_form.$i]['type'];
			$fileinfo['tmp_name'] = $_FILES[$this->upload_form.$i]['tmp_name'];
			$fileinfo['filename'] = TIMENOW.'_'.$i;
			$fileinfo['num'] = $i;
			$this->upload_file($fileinfo);
		}
	}

	function upload_file($fileinfo)
	{
		global $bboptions, $forums;
		$filename = $fileinfo['name'];
		$filesize = $fileinfo['size'];
		$filetype = $fileinfo['type'];
		$filetype = preg_replace( "/^(.+?);.*$/", "\\1", $filetype );
		if ($filename == '' OR !$filename OR $filename == 'none' OR !is_uploaded_file($fileinfo['tmp_name'])) {
			return;
		}
		if (!$filesize) {
			$this->error_no = 1;
			return;
		}
		if ( ! is_array( $this->allow_extension ) OR ! count( $this->allow_extension ) ) {
				$this->error_no = 2;
			return;
		}
		$this->file_extension = strtolower(str_replace('.', '', substr($filename, strrpos($filename, '.'))));
		if (!$this->file_extension) {
			$this->error_no = 2;
			return;
		}
		if ( ! in_array( $this->file_extension, $this->allow_extension ) ) {
			$this->error_no = 2;
			return;
		}
		if ( $this->maxfilesize AND $filesize > $this->maxfilesize ) {
			$this->error_no = 3;
			return;
		}
		$filename = $forums->func->htmlspecialchars_uni( $filename );
		if (in_array( $this->file_extension, $this->image_extension)) {
			$this->is_image[$fileinfo['num']] = 1;
		}
		if ($fileinfo['filename']) {
			$parsed_file_name = $fileinfo['filename'];
		} else {
			$parsed_file_name = str_replace('.'.$this->file_extension, '', $filename);
		}
		if ( preg_match("/\.(cgi|pl|js|asp|aspx|php|html|phtml|htm|jsp)/", $filename)) {
			$filetype = 'text/plain';
			$this->file_extension = 'txt';
		}
		$this->real_file_extension[$fileinfo['num']] = $this->file_extension;
		if (!$this->is_image) {
			$this->file_extension = str_replace('.', '', $this->force_extension);
		}
		$parsed_file_name .= '.'.$this->file_extension;
		$uploadfile = $this->filepath.'/'.$parsed_file_name;
		if (!@move_uploaded_file( $fileinfo['tmp_name'], $uploadfile)) {
			if (!@copy( $fileinfo['tmp_name'], $uploadfile)) {
				$this->error_no = 4;
				return;
			}
		}
		@chmod($uploadfile, 0777);
		if ($bboptions['watermark'] && in_array($this->file_extension, array('jpeg', 'jpg', 'png'))) {
			$this->create_watermark($uploadfile, $this->file_extension);
		}
		$this->original_file_name[$fileinfo['num']] = $filename;
		$this->parsed_file_name[$fileinfo['num']] = $parsed_file_name;
		$this->uploadfile[$fileinfo['num']] = $uploadfile;
	}

	function verify_attachment_path($userid)
	{
		global $bboptions;
		$subpath = SAFE_MODE ? "" : implode('/', preg_split('//', intval($userid),  -1, PREG_SPLIT_NO_EMPTY));
		$this->filepath = $bboptions['uploadfolder'] . '/' . $subpath;
		if ($this->make_attach_dir($this->filepath)) {
			return $this->filepath;
		} else {
			return false;
		}
	}

	function make_attach_dir($path, $mode = 0777)
	{
		if (is_dir($path)) {
			if (!(is_writable($path))) {
				@chmod($path, $mode);
			}
			return true;
		} else {
			$oldmask = @umask(0);
			$partialpath = dirname($path);
			if (!$this->make_attach_dir($partialpath, $mode)) {
				return false;
			} else {
				return mkdir($path, $mode);
			}
		}
	}

	function create_thumbnail( $data )
	{
		global $DB, $forums, $bboptions;
		$return = array();
		require_once( ROOT_PATH.'includes/functions_image.php' );
		$image = new functions_image();
		$image->filepath = $this->filepath;
		$image->filename = $data['location'];
		$image->thumbswidth  = $bboptions['thumbswidth'];
		$image->thumbsheight = $bboptions['thumbsheight'];
		if ( $bboptions['viewattachedthumbs'] ) {
			$return = $image->generate_thumbnail();
		}
		return $return;
	}

	function create_watermark($uploadfile, $extension)
	{
		global $bboptions;
		$uploadsize = @GetImageSize( $uploadfile );
		if(!$uploadsize[0] OR !$uploadsize[1]) return;
		if($extension=='jpeg' OR $extension=='jpg') {
			if (function_exists('imagecreatefromjpeg')) {
				$tmp = @imagecreatefromjpeg($uploadfile);
			} else {
				return;
			}
		} else	if($extension=='png') {
			if (function_exists('imagecreatefrompng')) {
				$tmp = @imagecreatefrompng($uploadfile);
			} else {
				return;
			}
		}
		if($imgmark = @imagecreatefrompng(ROOT_PATH."images/watermark.png")) {
			$marksize = @GetImageSize( ROOT_PATH."images/watermark.png" );
			if ($uploadsize[0] < ($marksize[0]*2) OR $uploadsize[1] < ($marksize[1]*2)) {
				return '';
			}
			switch ($bboptions['markposition']) {
				case '1':
					$pos_x = 0;
					$pos_y = 0;
					break;
				case '2':
					$pos_x = 0;
					$pos_y = $uploadsize[1] -  $marksize[1];
					break;
				case '3':
					$pos_x = $uploadsize[0] -  $marksize[0];
					$pos_y = 0;
					break;
				case '4':
					$pos_x = $uploadsize[0] -  $marksize[0];
					$pos_y = $uploadsize[1] -  $marksize[1];
					break;
				case '5':
					$pos_x = ($uploadsize[0] / 2) -  ($marksize[0] / 2);
					$pos_y = ($uploadsize[1] / 2) -  ($marksize[1] / 2);
					break;
				default:
					$pos_x = $uploadsize[0] -  $marksize[0];
					$pos_y = $uploadsize[1] -  $marksize[1];
					break;
			}
			imagecopy($tmp, $imgmark, $pos_x, $pos_y, 0, 0, $marksize[0], $marksize[1]);
		} else {
			$white  = ImageColorAllocate($tmp, 0, 0, 0);
			$black  = ImageColorAllocate($tmp, 255, 255, 255);
			imagestring($tmp, 3, 2, 3, "From {$bboptions['bburl']}", $white);
			imagestring($tmp, 3, 1, 2, "From {$bboptions['bburl']}", $black);
		}
		if ( function_exists( 'imagejpeg' ) AND ($extension=='jpeg' OR $extension=='jpg') ) {
			@imagejpeg( $tmp, $uploadfile );
			@imagedestroy( $tmp );
		} else if ( function_exists( 'imagepng' ) AND $extension=='png' ) {
			@imagepng( $tmp, $uploadfile );
			@imagedestroy( $tmp );
		} else {
			return '';
		}
	}
}

?>