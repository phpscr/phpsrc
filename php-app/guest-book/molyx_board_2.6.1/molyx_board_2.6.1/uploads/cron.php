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
define('THIS_SCRIPT', 'cron');
require_once('./global.php');

class cron {

    function show()
    {
    	@set_time_limit(1200);
		require_once( ROOT_PATH.'includes/functions_cron.php' );
		$functions = new functions_cron();
		$functions->docron();
    	@header("Content-Type: image/gif");
		print base64_decode("R0lGODlhAQABAID/AMDAwAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==");
 	}
}

$output = new cron();
$output->show();

?>