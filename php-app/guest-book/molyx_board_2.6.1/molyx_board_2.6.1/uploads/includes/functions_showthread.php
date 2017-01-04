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
class functions_showthread {
	function parse_attachment( $postids, $type='postid' )
	{
		global $DB, $forums, $bbuserinfo, $bboptions, $config;
		$final_attachment = $attachments_inpost = $return = array();
		if ( count( $postids ) ) {
			$sql_inpost = $bbuserinfo['candownload'] ? ' AND inpost=0' : '';
			$attach = $DB->query("SELECT * FROM ".TABLE_PREFIX."attachment WHERE $type IN (".implode(",", $postids).")$sql_inpost");
			while ( $a = $DB->fetch_array( $attach ) ) {
				if ($a['inpost'] == 0) {
					$final_attachment[$a[$type]][$a['attachmentid']] = $a;
				} else {
					$attachments_inpost[$a[$type]][$a['attachmentid']] = $a;
				}
			}
			$forums->func->check_cache('attachmenttype');
			foreach ( $final_attachment AS $pid => $data ) {
				$temp = '';
				$attachment = array();
				foreach($data AS $aid => $row) {
					$link_name = urlencode($row['filename']);
					if ( $bboptions['viewattachedimages'] AND $row['image'] AND $bbuserinfo['candownload'] ) {
						if ( $row['thumblocation'] AND $row['thumblocation'] AND $bboptions['viewattachedthumbs']) {
							if ($config['remoteattach']) {
								$subpath = implode('/', preg_split('//', intval($row['userid']),  -1, PREG_SPLIT_NO_EMPTY));
								$subpath = $config['remoteattach'] ."/". $subpath;
								$row['location'] = str_replace( "\\", "/", $row['location']);
								$row['location'] = str_replace( "/", "", substr( $row['location'], strrpos( $row['location'], '/' ) ) );
								$showfile = $subpath."/".$row['location'];
								$row['thumblocation'] = str_replace( "\\", "/", $row['thumblocation']);
								$row['thumblocation'] = str_replace( "/", "", substr( $row['thumblocation'], strrpos( $row['thumblocation'], '/' ) ) );
								$showthumb = $subpath."/".$row['thumblocation'];
							} else {								
								$showfile = "attachment.php?{$forums->sessionurl}id={$row['attachmentid']}&amp;u={$row['userid']}&amp;extension={$row['extension']}&amp;attach={$row['location']}&amp;filename={$link_name}&amp;attachpath={$row['attachpath']}";
								$showthumb = "attachment.php?{$forums->sessionurl}do=showthumb&amp;u={$row['userid']}&amp;extension={$row['extension']}&amp;attach={$row['thumblocation']}&amp;attachpath={$row['attachpath']}";
							}
							$temp['thumb'] .= "<a href='{$showfile}' title='{$row['filename']} -  ".$forums->lang['_filesize'].$forums->func->fetch_number_format( $row['filesize'], true ).", ".$forums->lang['_clicknums'].": {$row['counter']}' target='_blank'><img src='{$showthumb}' width='{$row['thumbwidth']}' height='{$row['thumbheight']}' class='attach' alt='{$row['filename']} - ".$forums->lang['_filesize'].$forums->func->fetch_number_format( $row['filesize'], true ).", ".$forums->lang['_clicknums'].": {$row['counter']} (".$forums->lang['_largeviews'].")' /></a> ";
						} else {
							if ($config['remoteattach']) {
								$subpath = implode('/', preg_split('//', intval($row['userid']),  -1, PREG_SPLIT_NO_EMPTY));
								$subpath = $config['remoteattach'] ."/". $subpath;
								$row['location'] = str_replace( "\\", "/", $row['location']);
								$row['location'] = str_replace( "/", "", substr( $row['location'], strrpos( $row['location'], '/' ) ) );
								$showfile = $subpath."/".$row['location'];
							} else {
								$showfile = "attachment.php?{$forums->sessionurl}id={$row['attachmentid']}&amp;u={$row['userid']}&amp;extension={$row['extension']}&amp;attach={$row['location']}&amp;filename={$link_name}&amp;attachpath={$row['attachpath']}";
							}
							$temp['image'] .= "<img src='{$showfile}' alt='".$forums->lang['_uploadimages']."' onload='javascript:if(this.width>screen.width-500)this.style.width=screen.width-500;' onclick='javascript:window.open(this.src);' style='CURSOR: pointer' /> ";
						}
					} else {
						$temp['attach'] .= "<br /><img src='images/".$bbuserinfo['imgurl']."/{$forums->cache['attachmenttype'][ $row['extension'] ]['attachimg']}' border='0' alt='".$forums->lang['_uploadattachs']."' />&nbsp;<a href='attachment.php?{$forums->sessionurl}id={$row['attachmentid']}&amp;u={$row['userid']}&amp;extension={$row['extension']}&amp;attach={$row['location']}&amp;filename={$link_name}&amp;attachpath={$row['attachpath']}' title='' class='edit' target='_blank'>{$row['filename']}</a><span class='edit'>( ".$forums->lang['_filesize'].": ".$forums->func->fetch_number_format($row['filesize'], 1)." ".$forums->lang['_clicknums'].": {$row['counter']} )</span><br />";
					}
				}
				$attachment[$pid] = "<br /><br /><span class='edit'>";
				if ( $temp['thumb'] ) {
					$attachment[$pid] .= "<strong>".$forums->lang['_uploadthumbs'].":</strong></span><br />".$temp['thumb']."<br />";
				}
				if ( $temp['image'] ) {
					$attachment[$pid] .= "<strong>".$forums->lang['_uploadimages'].":</strong></span><br />".$temp['image'];
				}
				if ( $temp['attach'] ) {
					$attachment[$pid] .= "<strong>".$forums->lang['_uploadattachs'].":</strong></span><br />".$temp['attach'];
				}
				$aids[$pid] .= $attachment[$pid];
			}
			$return['attachments'] = $aids;
			if ($attachments_inpost) {
				$attachment = array();
				foreach ( $attachments_inpost as $pid => $data ) {
					foreach($data AS $aid => $row) {
						$link_name = urlencode($row['filename']);
						$attachment[$pid][$aid] = "<br /><img src='images/".$bbuserinfo['imgurl']."/{$forums->cache['attachmenttype'][ $row['extension'] ]['attachimg']}' border='0' alt='".$forums->lang['_uploadattachs']."' />&nbsp;<a href='attachment.php?{$forums->sessionurl}id={$row['attachmentid']}&amp;u={$row['userid']}&amp;extension={$row['extension']}&amp;attach={$row['location']}&amp;filename={$link_name}&amp;attachpath={$row['attachpath']}' title='' class='edit' target='_blank'>{$row['filename']}</a><span class='edit'>( ".$forums->lang['_filesize'].": ".$forums->func->fetch_number_format($row['filesize'], 1)." ".$forums->lang['_clicknums'].": {$row['counter']} )</span><br />";
					}
				}
				$return['attachments_inpost'] = $attachment;
			}
		}
		return $return;
	}

	function paste_emule($text='')
	{
		global $forums;
		$stamp = substr(md5(rand (0, 15).microtime()), 0, 16);
		$text = preg_replace( array('#{emule_(\S+?)}#isU', '#{size_(.*)}#isU'), array("<input type='checkbox' name='$stamp' value='\\1' onclick=\"em_size('$stamp');\" checked='checked' />", "size_".$stamp.""), $text );
		$text .= "<div class='row3'><div style='float:right'>{$this_file_size}</div><input type=\"checkbox\" id=\"checkall_$stamp\" onclick=\"checkAll('$stamp',this.checked)\" checked=\"checked\"/>{$forums->lang['select_all']} <input type=\"button\" value=\"{$forums->lang['download_link']}\" onclick=\"download('$stamp',0,1)\" class=\"button\"> <input type=\"button\" value=\"{$forums->lang['copy_link']}\" onclick=\"copy('$stamp')\" class=\"button\"><div id=\"ed2kcopy_$stamp\" style=\"position:absolute;height:0px;width:0px;overflow:hidden;\"></div></div>\r\n";
		$text .= "<div class='row4' style='text-align:center'><a href='http://www.emule.org.cn/download/' target='_blank'>{$forums->lang['emule_down']}</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href='http://www.emule.org.cn/guide/' target='_blank'>{$forums->lang['emule_tech']}</a></div>\r\n";
		return $text;
	}

	function paste_attachment($aid='', $text)
	{
		return '<!--attachid::'.$aid.'-->'.$text.'<!--attachid-->';
	}

	function paste_code($top='', $text='')
	{
		global $forums;
		$stamp = md5(rand (0, 15).microtime());
		$top = $top." [ <a href='###' onClick=HighlightAll(c{$stamp})>".$forums->lang['copycode']."</a> ]";
		return "<div class='codetop'>".$top."</div><div class='codemain' id='c{$stamp}'>{$text}</div>";
	}
}