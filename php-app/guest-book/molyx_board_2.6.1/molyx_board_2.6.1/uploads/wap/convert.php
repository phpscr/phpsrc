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
class convert {

	function convert_text( $message='' )
	{		
		$message = preg_replace( '#<img[^>]+smilietext=(\'|"|\\\")(.*)(\\1).*>#siU', " \\2 ", $message );
		$message = preg_replace( "/^(\r|\n)+?(.*)$/", "\\2", $message );
		$message = preg_replace( "#<!--quote-(.+?)<!--quote3-->#", "" , $message );
		$message = preg_replace( "#<!--Flash (.+?)-->.+?<!--End Flash-->#e", "(FLASH MOVIE)" , $message );
		$message = preg_replace( "#<!--attachid::(\d+)-->(.+?)<!--attachid-->#", "(Attachment:\\1)" , $message );
		$message = preg_replace( "#<!--editpost-->(.+?)<!--editpost1-->#", "" , $message );
		$message = preg_replace( "#<img src=[\"'](\S+?)['\"].+?".">screen.+?".">#", "(IMAGE)"   , $message );
		$message = preg_replace( "#<img src=[\"'](\S+?)['\"].+?".">#", "(IMAGE)"   , $message );
		$message = preg_replace( "#<a href=[\"'](http|https|ftp|news)://(\S+?)['\"].+?".">(.+?)</a>#", "\\1://\\2"     , $message );
		$message = preg_replace( "#<a href=[\"']mailto:(.+?)['\"]>(.+?)</a>#", "(EMAIL: \\2)"   , $message );
		$message = str_replace( "&amp;" , "&", $message );
		$message = str_replace( "&quot;", "\"", $message );
		$message = str_replace( "&#092;", "\\", $message );
		$message = str_replace( "&#160;", "\r\n", $message );
		$message = str_replace( "&#036;", "\$", $message );
		$message = str_replace( "&#33;" , "!", $message );
		$message = str_replace( "&#39;" , "'", $message );
		$message = str_replace( "&lt;"  , "<", $message );
		$message = str_replace( "&gt;"  , ">", $message );
		$message = str_replace( "&#124;", '|', $message );
		$message = str_replace( "&#58;" , ":", $message );
		$message = str_replace( "&#91;" , "[", $message );
		$message = str_replace( "&#93;" , "]", $message );
		$message = str_replace( "&#064;", '@', $message );
		$message = str_replace( "&#60;", '<', $message );
		$message = str_replace( "&#62;", '>', $message );
		$message = str_replace( "&nbsp;", ' ', $message );
		$message = str_replace( "&" , "&amp;", $message );
		$message = strip_tags( $message, "<a>" );
		return $message;
	}

	function fetch_trimmed_title($text, $limit=200, $post_set=0)
	{
		$val = $this->csubstr($text, $post_set, $limit/2);
		return $val[1] ? $val[0]."..." : $val[0];
	}

	function csubstr($text, $post_set=0, $limit=100)
	{
		if (function_exists('mb_substr')) {
			if (mb_strlen($text, 'UTF-8') > ($post_set+$limit)) {
				$this->post_set += $limit;
				$this->more = TRUE;
			} else {
				$this->post_set = 0;
				$this->more = FALSE;
			}
			$text = mb_substr($text, $post_set, $limit, 'UTF-8');
		} elseif (function_exists('iconv_substr')) {
			if (iconv_strlen($text, 'UTF-8') > ($post_set+$limit)) {
				$this->post_set += $limit;
				$this->more = TRUE;
			} else {
				$this->post_set = 0;
				$this->more = FALSE;
			}
			$text = iconv_substr($text, $post_set, $limit, 'UTF-8');
		} else {
			preg_match_all("/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/", $text, $ar);   
            if(func_num_args() >= 3) {
                if (count($ar[0])>($post_set + $limit ) ) {
					$this->post_set += $limit;
					$this->more = TRUE;
                    $text = join("",array_slice($ar[0],$post_set,$limit));
                } else {
					$this->post_set = 0;
					$this->more = FALSE;
					$text = join("",array_slice($ar[0],$post_set,$limit));
				}
            } else {
				$this->post_set = 0;
				$this->more = FALSE;
                $text =  join("",array_slice($ar[0],$post_set)); 
            }
		}
		
		return array($text, $this->more);
	}
}