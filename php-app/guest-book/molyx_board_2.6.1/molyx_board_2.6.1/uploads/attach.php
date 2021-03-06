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
define('NO_REGISTER_GLOBALS', 1);
define('THIS_SCRIPT', 'attach');
require_once('./global.php');

class attach {

    function show()
    {
        global $_INPUT;
		require ROOT_PATH."includes/functions_post.php";
        $this->lib = new functions_post(0);
        switch( $_INPUT['do'] ) {
        	case 'upload':
        		$this->upload();
        		break;
        	case 'delete':
        		$this->delete();
        		break;
        	default:
        		$this->showattach('');
        		break;
        }
	}

	function showattach($error){
		global $forums, $_INPUT;   
		if($error) {
			$errormsg = $error;
			$notajax = 1;
		} else {
			$errormsg = $this->lib->obj['errors'];
		}
		$upload = $this->lib->fetch_upload_form($_INPUT['posthash'], 'new');
		$upload['maxnum'] = intval($bbuserinfo['attachnum']);
		$upload['tmp'] = str_replace(array('\'', "\n"), array('\\\'', ''), $upload['tmp']);
		include $forums->func->load_template('attach');
		exit;		
	}

	function upload(){
		global $DB, $forums, $_INPUT, $bboptions, $bbuserinfo;
		$forums->lang = $forums->func->load_lang($forums->lang, 'post' );
		$forums->lang = $forums->func->load_lang($forums->lang, 'error' );
		$forum_id = ($_POST['rsargs']['0']) ? intval($_POST['rsargs']['0']) : intval($_INPUT['f']);
		$this->forum = $forums->forum->single_forum($forum_id);
		$_INPUT['num'] = 0;
		if ( $forums->func->fetch_permissions($this->forum['canupload'], 'canupload') == TRUE ) {
        	if ( $bbuserinfo['attachlimit'] != -1 ) {
        		$this->lib->canupload = 1;
        	}
			if ( $_POST['upload'] ) {
				$this->lib->obj['errors'] = "";
				$this->lib->process_upload();
				$this->showattach('');
			}
        }else{
			$this->showattach($forums->lang['cannotupload']);
			exit;
		}
	}

	function delete()
	{
		global $DB, $forums, $_INPUT, $bboptions, $bbuserinfo;
		if ($_INPUT['removeattachid']) {
			$this->lib->remove_attachment(intval($_INPUT['removeattachid']), $_INPUT['posthash']);
			$this->showattach('');
		}
	}
}

$output = new attach();
$output->show();

?>