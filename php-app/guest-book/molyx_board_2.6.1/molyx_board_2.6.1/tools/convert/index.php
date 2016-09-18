<?php
#**************************************************************************#
#   MolyX Convert Tools
#   ---------------------------
#   copyright ?2004 - 2006 MolyX Studios.  All Rights Reserved. http://www.molyx.com
#   MolyX Board is licensed software. This file may not be redistributed in whole or in
#   part. By installing and/or using this software you hereby agree to all of the terms,
#   conditions, and restrictions set by MolyX. If you do not accept these Terms and
#   Conditions, please do not use this software.
#   MolyX Studios may revise these Terms and Conditions at any time without notice.
#   Please visit www.molyx.com periodically to review the Terms and Conditions,
#   or contact the author for clarification.
#**************************************************************************#
error_reporting(E_ALL & ~E_NOTICE);
@set_time_limit(0);
define('ROOT_PATH', './../');
define ( 'IN_ACP', 1 );
define('IN_MXB', TRUE);
class convert {

	var $percycle = 100;
	var $extra = array();
	var $from_chars = 'GBK';
	var $to_chars = 'UTF-8';

	function show()
	{
		global $forums, $DB, $bbuserinfo;

		require_once( ROOT_PATH."includes/functions.php" );
		$forums->func = new functions();
		$this->input = $forums->func->init_variable();

		require_once(ROOT_PATH.'includes/adminfunctions_importers.php');
		$this->lib = new adminfunctions_importers();
		$this->convert = array(
									array('adminlog',  array('note'), 'adminlogid'),
									array('announcement',  array('title', 'pagetext'), 'id'),
									array('attachment',  array('filename'), 'attachmentid'),
									array('badword',  array('badbefore', 'badafter'), 'id'),
									array('banfilter',  array('content'), 'id'),
									array('bbcode',  array('description', 'bbcodeexample'), 'bbcodeid'),
									//array('blog',  array('title'), 'bid'),
									//array('blogcontents',  array('content'), 'bid'),
									array('cache',  array('data'), 'title'),
									array('cron',  array('title', 'description'), 'cronid'),
									array('cronlog',  array('title', 'description'), 'cronlogid'),
									array('faq',  array('title', 'description', 'text'), 'id'),
									array('forum',  array('name', 'description', 'lastthread', 'threadprefix', 'specialtopic'), 'id'),
									array('icon',  array('image', 'icontext'), 'id'),
									array('league',  array('sitename', 'siteinfo', 'siteimage'), 'leagueid'),
									array('moderator',  array('username', 'usergroupname'), 'moderatorid'),
									array('moderatorlog',  array('title', 'action'), 'moderatorlogid'),
									array('pm',  array('title'), 'pmid'),
									array('pmtext',  array('message'), 'pmtextid'),
									array('pmuserlist',  array('contactname', 'description'), 'id'),
									array('poll',  array('options', 'question'), 'pollid'),
									array('post',  array('pagetext', 'username'), 'pid'),
									array('search',  array('query'), 'searchid'),
									array('setting',  array('title', 'description', 'value', 'defaultvalue', 'dropextra'), 'settingid'),
									array('settinggroup',  array('title', 'description'), 'groupid'),
									array('smile',  array('smiletext', 'image'), 'id'),
									array('strikes',  array('username'), 'striketime'),
									array('style',  array('title'), 'styleid'),
									array('template',  array('template'), 'tid'),
									array('thread',  array('title', 'description', 'postusername', 'lastposter', 'logtext'), 'tid'),
									array('user',  array('name', 'customtitle', 'signature', 'location'), 'id'),
									//array('userblog',  array('blogtitle', 'blogdescription', 'blogoptions', 'category', 'gallerycate', 'bloglink', 'newblogs', 'newcomments', 'newgallerys'), 'id'),
									array('usergroup',  array('grouptitle', 'groupranks'), 'usergroupid'),
									array('usertitle',  array('title'), 'id'),
								);
		if (function_exists('mb_convert_encoding')) {
			$this->can_convert = TRUE;
			$this->convert_func = "mb_convert_encoding";
		} elseif (function_exists('iconv')) {
			$this->can_convert = TRUE;
			$this->convert_func = "iconv";
		} else {
			require_once( ROOT_PATH."includes/functions_encoding.php" );
			$this->convert = new Encoding();
		}

		require_once( ROOT_PATH."convert/password.php" );

		$this->check_password = $check_password;

		if (!$this->check_password) {
			$this->error = "请编辑当前目录内的 password.php 文件设定登录密码，系统才可以继续使用。";
			$this->input['do'] = "";
		} else	if ($this->input['password'] != $this->check_password) {
			$this->error = isset($this->input['password']) ? "密码无效，请重新输入确认" : "请填写验证密码";
			$this->input['do'] = "";
		}
		if ($this->input['do'] != 'generate_config') {
			$this->p_header();
		}
		$bbuserinfo['usergroupid'] = 4;
		switch($this->input['do']) {
			case 'convert':
				$this->do_convert();
				break;
			case 'set_db':
				$this->set_db();
				break;
			case 'connect_db':
				$this->connect_db();
				break;
			case 'already_convert':
				$this->already_convert();
				break;
			case 'generate_config':
				$this->generate_config();
				break;
			case 'start':
				$this->start();
				break;
			case 'restore':
				$this->restore();
				break;
			case 'dorestore':
				$this->do_restore();
				break;
			case 'howto':
				$this->howto();
				break;
			default:
				$this->input_password();
				break;
		}
		if ($this->input['do'] != 'generate_config') {
			$this->p_footer();
		}
	}

	function p_header()
	{
		@header("Content-Type:text/html; charset=UTF-8");
		echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n";
		echo "<html xmlns=\"http://www.w3.org/1999/xhtml\">
		<head><title>MolyX 通用文件编码转换、数据导入恢复工具</title>
		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
		<meta HTTP-EQUIV=\"Pragma\"  CONTENT=\"no-cache\">
		<meta HTTP-EQUIV=\"Cache-Control\" CONTENT=\"no-cache\">
		<meta HTTP-EQUIV=\"Expires\" CONTENT=\"Mon, 06 May 1996 04:57:00 GMT\">
		<style>
			.header {
				font-family:Courier, Tahoma, Serif, Simsun;
				width:85%;
				border:1px dashed #156BC3;
				padding:10px;
				margin:0px auto 8px auto;
				color:#245D98;
				background-color:#FFCCCC;
				font-weight: bold;
				text-align: center;
				font-size: 14px;
			}
			.footer {
				font-family:Arial, Helvetica, sans-serif;
				width:85%;
				padding:10px;
				margin:0px auto 8px auto;
				color:#245D98;
				font-weight: bold;
				text-align: center;
				font-size: 10px;
			}
			.center {
				font-family:Tahoma, Serif, Simsun;
				width:85%;
				border:1px dashed #156BC3;
				padding:10px;
				margin:0px auto 8px auto;
				color:#245D98;
				background-color:#FFFFFF;
				font-size: 12px;
				line-height: 150%;
			}
			.highlight {
				font-weight: bold;
				color: #FF0000;
			}
			.button {
				vertical-align:middle;
				padding:5px 10px 5px 10px;
				background-color:#C8FFFF;
				border:1px solid #156BC3;
				margin:0px;
				font-size: 14px;
				font-weight: bold;
				color:#245D98;
			}
			table {
				background-color: #666666;
			}
		</style>
		</head>
		<body>
		<div class='header' id=''>
			MolyX 通用文件编码转换、数据导入恢复工具 v1.2
		</div>
		<div class='center' id=''>
		";
	}

	function p_footer()
	{
		echo "
		</div>
			<div class='footer' id=''>
			Power By <a href='http://www.molyx.com' target='_blank'>MolyX</a>
			</div>
			</body>
			</html>
		";
		exit;
	}

	function build_form($form = array(), $extra="")
	{
		$show_value = "<form action='index.php' method='get'".$extra.">\n";
		if ($_GET['password']) {
			$show_value .= "<input type='hidden' name='password' value='".$_GET['password']."' />\n";
		}
		if (is_array($form)) {
			foreach ($form AS $k) {
				$show_value .= "<input type='hidden' name='".$k[0]."' value='".$k[1]."' />\n";
			}
		}
		echo $show_value;
	}

	function build_form_end($form = array())
	{
		echo "</form>\n";
	}

	function build_form_button($text = "", $extra="")
	{
		echo "<p align='center'><input type='submit' value='$text' class='button' /> {$extra}</p>\n";
	}

	function build_nav($text = "")
	{
		echo "<p align='left'>当前操作：$text</p>\n";
	}

	function input_password()
	{
		$this->build_form(
									array(
										array("do", 'start'),
									)
								);
		echo "
			请输入密码后进行下一步转换操作，该密码可以在本文件开头部分的 <b>var $check_password = '<font class='highlight'>xxxxxx</font>'; </b>处看到：<br />
		";
		if($this->error) {
			echo "
				<br /><font class='highlight'>".$this->error."</font><br />
			";
		}
		echo "
				<p>
				<input name='password' value='' type='text' size='20' />
				</p>
		";
		$this->build_form_button("验证密码");
		$this->build_form_end();
	}

	function start()
	{
		$this->build_form(
									array(
										array("do", 'set_db'),
									)
								);
		echo "
			欢迎使用 MolyX Convert Tools!<br />
			在稍后的过程中，您可以通过本工具将涉及到 MolyX 的数据库数据编码由 {$this->from_chars} 转换为 {$this->to_chars} 编码格式.<br /><br />
			在执行下一步转换前，请您<font class='highlight'>务必确认</font>以下设定后才能继续下一步操作：<br />
			<ul>
				<li>当前数据库为 {$this->from_chars} 编码格式，尚未升级为 {$this->to_chars}</li>
				<li>论坛数据库已完整备份</li>
				<li>论坛已处于关闭状态</li>
				<li>论坛表已经过优化。（可以在后台或PhpMyadmin里面进行操作）</li>
				<li>转换文件的转换表格式完整<br />注：<br />如果有新添加论坛表也需要转换的话，请编辑这个文件，在 22 行左右的 $this->convert 变量，按照:<br /><b>array('数据表',  array('字段1', '字段2', '字段3'...), '关键Key键'),</b><br />的方式添加额外转换数据表。<b>请勿填写数据表前缀，否则将出现查询错误！</b></li>
			</ul>
			如果一切确认无误，点击下一步开始进行数据库设定。
		";
		$this->build_form_button("点击这里进行数据库设置");
		$this->build_form_end();
	}

	function db_table()
	{
		$servername = $_GET['servername'] ? trim($_GET['servername']) : 'localhost';
		$serverport = $_GET['serverport'] ? trim($_GET['serverport']) : '3306';
		$dbname = trim($_GET['dbname']);
		$dbprefix = $_GET['dbprefix'] ? trim($_GET['dbprefix']) : 'mxb_';
		$dbusername = trim($_GET['dbusername']);
		$dbpassword = trim($_GET['dbpassword']);
		echo "
			<table width='60%' border='0' cellpadding='3' cellspacing='1' align='center'>
			  <tr>
				<td bgcolor='#eeeeee'>数据库地址</td>
				<td width='60%' bgcolor='#eeeeee'><input name='servername' value='$servername' type='text' size='20' /></td>
			  </tr>
			  <tr>
				<td bgcolor='#ffffff'>数据库端口(默认3306)</td>
				<td bgcolor='#ffffff'><input name='serverport' value='$serverport' type='text' size='20' /></td>
			  </tr>
			  <tr>
				<td bgcolor='#eeeeee'>数据库名称</td>
				<td bgcolor='#eeeeee'><input name='dbname' value='$dbname' type='text' size='20' /></td>
			  </tr>
			  <tr>
				<td bgcolor='#ffffff'>数据表前缀</td>
				<td bgcolor='#ffffff'><input name='dbprefix' value='$dbprefix' type='text' size='20' /></td>
			  </tr>
			  <tr>
				<td bgcolor='#eeeeee'>数据库用户名</td>
				<td bgcolor='#eeeeee'><input name='dbusername' value='$dbusername' type='text' size='20' /></td>
			  </tr>
			  <tr>
				<td bgcolor='#ffffff'>数据库连接密码</td>
				<td  bgcolor='#ffffff'><input name='dbpassword' value='$dbpassword' type='password' size='20' /></td>
			  </tr>
			</table>
			";
	}

	function set_db()
	{
		$this->build_form(
									array(
										array("do", 'connect_db'),
									)
								);
		$this->build_nav("设定数据库参数");
		echo "
			请在下面的表单内设定 MySQL 数据库的相关参数。<br /><br />
			";
		$this->db_table();
		$this->build_form_button("点击这里尝试连接数据库");
		$this->build_form_end();
	}

	function connect_db()
	{
		if ($_GET['serverport'] != '3306') {
			$serverport = ":".intval($_GET['serverport']);
		}
		@require ( ROOT_PATH.'convert/db_mysql.php' );
		define( 'TABLE_PREFIX', $_GET['dbprefix'] );
		$DB = new db;
		$DB->server = $_GET['servername'].$serverport;
		$DB->database = $_GET['dbname'];
		$DB->user = $_GET['dbusername'];
		$DB->password = $_GET['dbpassword'];
		$DB->pconnect = 0;
		$DB->use_shutdown = 1;
		$DB->return_die = 1;
		$DB->connect();
		$DB->return_die = 0;

		if ($DB->failed) {
			$this->build_form(
										array(
											array("do", 'connect_db'),
										)
									);
			$this->build_nav("连接失败，请重新设定数据库参数");
			echo "
				<font class='highlight'>无法连接数据库！</font>请重新在下面的表单内设定 MySQL 数据库的相关参数。<br /><br />
				";
			$this->db_table();
			$this->build_form_button("点击这里重新尝试连接数据库");
			$this->build_form_end();
		} else {

			$version = $DB->query_first("SELECT VERSION() AS version");

			$vars = $DB->query("SHOW VARIABLES");
			while ($row = $DB->fetch_array($vars)) {
				$var[$row['Variable_name']] = $row['Value'];
			}

			if ($version['version'] >= '4.1' AND !in_array($dbcharset, array('gbk', 'gb2312', 'utf8')))
			{
				if ($var['character_set_client'] != "utf8" OR $var['character_set_database'] != "utf8") {
					$dbcharset = 'utf8';
				} else {
					$dbcharset = '';
				}
			}
			$_GET['dbcharset'] = $dbcharset;
			$this->already_convert();
		}
	}

	function already_convert()
	{
		$this->build_form(
									array(
										array("do", 'convert'),
										array("servername", $_GET['servername']),
										array("serverport", $_GET['serverport']),
										array("dbname", $_GET['dbname']),
										array("dbusername", $_GET['dbusername']),
										array("dbpassword", $_GET['dbpassword']),
										array("dbprefix", $_GET['dbprefix']),
										array("dbcharset", $_GET['dbcharset']),
									), " onsubmit='return validate()'"
								);
		$this->build_nav("数据库设定完毕");
		echo "
		<script type=\"text/javascript\">
		<!--
		 function validate()
		{
			 okconfirm = confirm(\"确实要使用转码方式更新数据库么？\\n这种方式可能会对数据造成破环！并且可能无法完美转换！\");
			 if (okconfirm == true) {
				return true;
			 } else {
				return false;
			 }
		}
		 //-->
		</script>

			数据库已完成全部设定，点击下一步将开始进行数据字符集更新，如果已经完整确认当前参数的话，点击下一步将开始更新数据库字符集<br /><br />
			当前数据库设定状态：<br />
			数据库服务器地址：".$_GET['servername']."<br />
			数据库服务器端口：".$_GET['serverport']."<br />
			数据库名称：".$_GET['dbname']."<br />
			数据库用户名：".$_GET['dbusername']."<br />
			数据库前缀：".$_GET['dbprefix']."<br />
			字符集连接方式：<font class='highlight'>".$_GET['dbcharset']."</font><br />

			<p align='center'><font class='highlight'>推荐方式:</font><br />
				<font class='highlight' style='font-size:14px;'>关于如何将数据库备份存为 UTF-8 格式，<a href='index.php?do=howto&amp;password=".$_GET['password']."' target='_blank'>请点击这里</a></font><br />
				<input type='button' onclick='window.location=\"index.php?do=restore&servername=".$_GET['servername']."&dbprefix=".$_GET['dbprefix']."&dbname=".$_GET['dbname']."&dbusername=".$_GET['dbusername']."&dbpassword=".$_GET['dbpassword']."&dbcharset=".$_GET['dbcharset']."&serverport=".$_GET['serverport']."&password=".$_GET['password']."\"' value='点击这里通过数据库备份文件恢复数据' class='button' /></p><br /><br />
			<p align='center'>不推荐方式:</p>
		";
		if ($this->can_convert) {
			$this->build_form_button("点击这里开始转换数据库字符设定");
		} else {
			echo "由于你的服务器尚未安装 mbstring 或 iconv 函数库，请使用上面的方式转换系统。";
		}


		$this->build_form_end();
	}

	function restore()
	{
		$files = $this->get_folder_contents();
		$size = $this->gen_size($files['filesize'], 3, 1);
		$this->build_form(
							array(
								array("do", 'dorestore'),
								array("servername", $_GET['servername']),
								array("serverport", $_GET['serverport']),
								array("dbname", $_GET['dbname']),
								array("dbusername", $_GET['dbusername']),
								array("dbpassword", $_GET['dbpassword']),
								array("dbprefix", $_GET['dbprefix']),
								array("dbcharset", $_GET['dbcharset']),
							)
						);
		$this->build_nav("请确认要恢复的数据库");

		if ($files['nofiles']) {
			echo "
			convert/db 目录内不存在备份数据，系统无法执行恢复！<br />
			<font class='highlight'>请将要转换的数据库文件存放在 convert/db 目录内！</font>
			";
		} elseif ($files['errors']) {
			echo "
			convert/db目录内备份数据资料错误，系统无法执行恢复！<br />
			<font class='highlight'>如果所要转换的文件并非为 MolyX 系统备份数据，请通过下面菜单手动升级。</font><br /><br />

			convert/db目录内文件信息<br />
			数据大小：".$size[0].$size[1]."<br />
			文件数量：".$files['info'][3]."</b><br /><br />
			<font class='highlight'>数据库恢复位置：".$_GET['dbname']."</font><br /><br />

				<input type='hidden' name='manual' value='1' />
				<table width='60%' border='0' cellpadding='3' cellspacing='1' align='center'>
					  <tr>
						<td bgcolor='#eeeeee'><b>是否使用批处理方式导入数据？</b><br />仅支持数字方式，如1-30</td>
						<td width='60%' bgcolor='#eeeeee'>从 <input name='from' value='' type='text' size='5' /> 到 <input name='to' value='' type='text' size='5' /></td>
					  </tr>
					  <tr>
						<td bgcolor='#ffffff'><b>请在右侧文本框内填写要恢复的数据文件名称</b><br /><br />
					请在每行按要恢复的顺序填写一个完整的文件名，如果在上面填写了批处理格式，请用(*)的方式替换要批处理的字符（仅填一行）。<br />如：backup_(*).sql</td>
						<td bgcolor='#ffffff'><textarea name='convert_sql' rows=8 cols=30></textarea></td>
					  </tr>
					</table>
			";
			$this->build_form_button("导入自定义的数据库");
			$this->build_form_end();
		} else {
			echo "
			点击下一步将开始进行数据库恢复。<br />
			<font class='highlight'>在恢复前请确认数据库备份文件已全部保存为 UTF-8 格式。</font>关于如何将数据库备份存为 UTF-8 格式，<a href='index.php?do=howto&amp;password=".$_GET['password']."' target='_blank'>请点击这里</a><br /><br />
			以下是要恢复数据库的数据库信息说明：<br />
			<font class='highlight'>请确认 db 文件夹内不存在与要恢复数据无关的文件</font><br /><br />
			<b>备份日期：".$files['info'][0]."<br />
			备份方式：".$files['info'][1]."<br />
			数据大小：".$size[0].$size[1]."<br />
			文件数量：".$files['info'][3]."</b><br /><br />
			<font class='highlight'>数据库恢复位置：".$_GET['dbname']."</font><br />
			";
			$this->build_form_button("确认开始恢复数据库");
			$this->build_form_end();
		}
	}

	function do_restore()
	{
		if ($_GET['serverport'] != '3306') {
			$serverport = ":".intval($_GET['serverport']);
		}
		@require ( ROOT_PATH.'convert/db_mysql.php' );
		define( 'TABLE_PREFIX', $_GET['dbprefix'] );
		$DB = new db;
		$DB->server = $_GET['servername'].$serverport;
		$DB->database = $_GET['dbname'];
		$DB->user = $_GET['dbusername'];
		$DB->password = $_GET['dbpassword'];
		$DB->dbcharset = $_GET['dbcharset'];
		$DB->pconnect = 0;
		$DB->use_shutdown = 1;
		$DB->return_die = 1;
		$DB->connect();
		$DB->return_die = 0;

		if ($_GET['manual']) {
			$files = rawurldecode($_REQUEST['convert_sql']);
			if (!$files) {
				echo "
						请设定手动转换的数据库文件名列表
					";
				$this->p_footer();
			}
			if ((isset($_GET['from'])  AND !isset($_GET['to'])) OR (!isset($_GET['from']) AND isset($_GET['to']))) {
				echo "
						批处理设置有误，请返回重新设定。批处理格式仅支持数字方式，如1-30
					";
				$this->p_footer();
			}
			if ($files AND !$_GET['to']) {
				$allfiles = explode("\n", $files);
				$allfiles_count = count($allfiles);
			} else {
				$_GET['from'] = intval($_GET['from']);
				$_GET['to'] = intval($_GET['to']);
				if ($_GET['to'] <= $_GET['from']) {
					echo "
							批量转换的起始、结束符设定不正确。
						";
					$this->p_footer();
				}
				for($i=$_GET['from'];$i<=$_GET['to'];$i++) {
					$allfiles[] = str_replace("(*)", $i, $files);
				}
				$allfiles_count = count($allfiles);
			}
			$file_count = intval($_GET['file_count']) ? intval($_GET['file_count']) : 0;
			if ($allfiles_count > 0 ) {
				foreach ($allfiles AS $id => $files) {
					if ($id < $file_count) {
						continue;
					} else {
						$newcount = $file_count + 1;
						$this->lib->echo_flush(  "正在恢复文件 - ".$files."<br />" );
						if ( $fp = @fopen( ROOT_PATH.'convert/db/'.$files, 'rb' ) ) {
							$filesize = @filesize( ROOT_PATH.'convert/db/'.$files );
							@flock($fp, LOCK_UN);
							$sqldump = @fread($FH, $filesize);
							$sqlquery = $this->split_sql($sqldump);
							unset($sqldump);
							@fclose($fp);
							foreach($sqlquery as $sql) {
								if(trim($sql) != '') {
									if( preg_match('/^(CREATE TABLE).*/i', $sql) AND $DB->dbcharset ) {
										$sql = str_replace(";", "", $sql);
										$sql = $sql." ENGINE=MyISAM default charset=".$DB->dbcharset;
									}
									$DB->query_unbuffered($sql);
								}
							}
							$this->lib->echo_flush( "已恢复文件 - <b>".$files."</b><br /><br />" );

							echo "<script language='javascript'>setTimeout(function() {window.location='index.php?".$forums->js_sessionurl."do=dorestore&servername=".$_GET['servername']."&dbprefix=".$_GET['dbprefix']."&dbname=".$_GET['dbname']."&dbusername=".$_GET['dbusername']."&dbpassword=".urlencode($_GET['dbpassword'])."&dbcharset=".$_GET['dbcharset']."&serverport=".$_GET['serverport']."&password=".urlencode($_GET['password'])."&file_count=".$newcount."&from=".$_GET['from']."&to=".$_GET['to']."&convert_sql=".urlencode($_REQUEST['convert_sql'])."&manual=1';}, 2000);</script>\n";
							$this->lib->echo_flush( "<a href='index.php?do=dorestore&amp;servername=".$_GET['servername']."&amp;dbprefix=".$_GET['dbprefix']."&amp;dbname=".$_GET['dbname']."&amp;dbusername=".$_GET['dbusername']."&amp;dbpassword=".urlencode($_GET['dbpassword'])."&amp;dbcharset=".$_GET['dbcharset']."&amp;serverport=".$_GET['serverport']."&amp;password=".urlencode($_GET['password'])."&amp;file_count=".$newcount."&amp;from=".$_GET['from']."&amp;to=".$_GET['to']."&amp;convert_sql=".urlencode($_REQUEST['convert_sql'])."&amp;manual=1'>现在将跳转到下一个数据表转换页面", "正跳转至数据表转换</a>" );
							exit;
						} else {
							echo "无法找到文件 - ".$files."，是否继续导入数据库？<br />";
							$this->lib->echo_flush( "<a href='index.php?do=dorestore&amp;servername=".$_GET['servername']."&amp;dbprefix=".$_GET['dbprefix']."&amp;dbname=".$_GET['dbname']."&amp;dbusername=".$_GET['dbusername']."&amp;dbpassword=".urlencode($_GET['dbpassword'])."&amp;dbcharset=".$_GET['dbcharset']."&amp;serverport=".$_GET['serverport']."&amp;password=".urlencode($_GET['password'])."&amp;file_count=".$newcount."&amp;from=".$_GET['from']."&amp;to=".$_GET['to']."&amp;convert_sql=".urlencode($_REQUEST['convert_sql'])."&amp;manual=1'>点击这里继续下一个文件导入</a>" );
							exit;
						}
					}
				}
				$this->finish();
			} else {
				echo "
						无法确认导入的数据，请重新设定。
					";
				$this->p_footer();
			}
			//echo var_export($allfiles)."<br />".$file_count;
		} else {
			$file_count = intval($_GET['file_count']) ? intval($_GET['file_count']) : 0;
			$files = $this->get_folder_contents();
			$id = 1;
			if (count($files['files'])) {
				foreach ($files['files'] AS $id => $files) {
					if ($id < $file_count) {
						continue;
					} else {
						$newcount = $file_count + 1;
						$this->lib->echo_flush(  "正在恢复文件 - ".$files."<br />" );
						if ( $fp = @fopen( ROOT_PATH.'convert/db/'.$files, 'rb' ) ) {
							$filesize = @filesize( ROOT_PATH.'convert/db/'.$files );
							@flock( $fp, LOCK_UN );
							$sqldump = @fread($FH, $filesize);
							while (preg_match("/ default charset=(?:\w+)/is", $sqldump)) {
								$sqldump = preg_replace("/ default charset=(?:\w+)/is", "", $sqldump);
							}
							while (preg_match("/ character set (?:\w+) /is", $sqldump)) {
								$sqldump = preg_replace("/ character set (?:\w+) /is", " ", $sqldump);
							}
							while (preg_match("/ engine=[^heap](?:\w+)/is", $sqldump)) {
								$sqldump = preg_replace("/ engine=[^heap](?:\w+)/is", "", $sqldump);
							}
							$sqlquery = $this->split_sql($sqldump);
							unset($sqldump);
							@fclose($fp);
							foreach($sqlquery as $sql) {
								if(trim($sql) != '') {
									if( preg_match('/^(CREATE TABLE).*/i', $sql) AND $DB->dbcharset ) {
										$sql = str_replace(";", "", $sql);
										$add_engine = preg_match("#engine=heap#is", $sql) ? "" : " engine=MyISAM";
										$sql = $sql.$add_engine." default charset=".$DB->dbcharset;
									}
									$DB->query_unbuffered($sql);
								}
							}
							$this->lib->echo_flush( "已恢复文件 - <b>".$files."</b><br /><br />" );

							echo "<script language='javascript'>setTimeout(function() {window.location='index.php?".$forums->js_sessionurl."do=dorestore&servername=".$_GET['servername']."&dbprefix=".$_GET['dbprefix']."&dbname=".$_GET['dbname']."&dbusername=".$_GET['dbusername']."&dbpassword=".urlencode($_GET['dbpassword'])."&dbcharset=".$_GET['dbcharset']."&serverport=".$_GET['serverport']."&password=".urlencode($_GET['password'])."&file_count=".$newcount."';}, 2000);</script>\n";
							$this->lib->echo_flush( "<a href='index.php?do=dorestore&amp;servername=".$_GET['servername']."&amp;dbprefix=".$_GET['dbprefix']."&amp;dbname=".$_GET['dbname']."&amp;dbusername=".$_GET['dbusername']."&amp;dbpassword=".urlencode($_GET['dbpassword'])."&amp;dbcharset=".$_GET['dbcharset']."&amp;serverport=".$_GET['serverport']."&amp;password=".urlencode($_GET['password'])."&amp;file_count=".$newcount."'>现在将跳转到下一个数据表转换页面", "正跳转至数据表转换</a>" );
							exit;
						}
					}
				}
				$this->finish();
			}
		}
	}

	function split_sql ( $sql )
	{
		$sql = str_replace( "\r" , "\n", $sql );
		$ret = array();
		$num = 0;
		$query_arr = explode(";\n", trim($sql));
		unset($sql);
		foreach ( $query_arr as $query ) {
			$queries = explode("\n", trim($query));
			foreach($queries as $query) {
				$ret[$num] .= $query[0] == "#" ? NULL : $query;
			}
			$num++;
		}
		return($ret);
	}

	function gen_size($val, $li, $sepa )
	{
		$sep     = pow(10, $sepa);
		$li      = pow(10, $li);
		$retval  = $val;
		$unit    = 'Bytes';
		if ($val >= $li * 1000000) {
			$val = round( $val / (1073741824/$sep) ) / $sep;
			$unit  = 'GB';
		} else if ($val >= $li*1000) {
			$val = round( $val / (1048576/$sep) ) / $sep;
			$unit  = 'MB';
		} else if ($val >= $li) {
			$val = round( $val / (1024/$sep) ) / $sep;
			$unit  = 'KB';
		}
		if ($unit != 'Bytes') {
			$retval = number_format($val, $sepa, '.', ',');
		} else {
			$retval = number_format($val, 0, '.', ',');
		}
		return array($retval, $unit);
    }

	function get_folder_contents()
	{
		$files = array();
		$info = array();
		$filesize = 0;
		$dh = @opendir( ROOT_PATH.'convert/db/' );
		$file_nums = 1;
		while (false !== ($file = @readdir($dh))) {
 			if ( ($file != ".") && ($file != "..") ) {
				$allfolder[] = $file;
 			}
 		}
		if (is_array($allfolder)) {
			natcasesort($allfolder);
			foreach ($allfolder AS $file) {
				if ( $fp = @fopen( ROOT_PATH.'convert/db/'.$file, 'rb' ) ) {
					$size = filesize(ROOT_PATH.'convert/db/'.$file);
					$filesize += $size;
					$info = explode(",", base64_decode(preg_replace("/^# key:\s*(\w+).*/s", "\\1", fgets($FH, 256))));
					$files[] = $file;
					if ($info[2] != 'NULL' AND intval($info[3]) < $file_nums) {
						continue;
					}
					if ($info['4'] == 1 AND !$true) {
						$true = 1;
					}
					@fclose($fp);
				}
				$file_nums++;
			}
		} else {
			return array( 'nofiles' => 1);
		}
		@closedir( $dh );
		if ( !$true OR $info['3'] != count($allfolder) ) {
			return array( 'errors' => 1, 'info' => $info , 'filesize' => $filesize);
		}
 		return array( 'files' => $files, 'info' => $info , 'filesize' => $filesize);
 	}

	function do_convert()
	{
		if ($_GET['serverport'] != '3306') {
			$serverport = ":".intval($_GET['serverport']);
		}
		@require ( ROOT_PATH.'convert/db_mysql.php' );
		define( 'TABLE_PREFIX', $_GET['dbprefix'] );
		$DB = new db;
		$DB->server = $_GET['servername'].$serverport;
		$DB->database = $_GET['dbname'];
		$DB->user = $_GET['dbusername'];
		$DB->password = $_GET['dbpassword'];
		$DB->dbcharset = $_GET['dbcharset'];
		$DB->pconnect = 0;
		$DB->return_die = 1;
		$DB->connect();

		$pp = isset($_GET['pp']) ? intval($_GET['pp']) : 0;
		$percycle = isset($_GET['percycle']) ? intval($_GET['percycle']) : $this->percycle;
		$table = isset($_GET['table']) ? intval($_GET['table']) : 0;

		if ($table >= count($this->convert)) {
			$this->finish();
		}

		$current_table = $this->convert[$table][0];
		$current_col = $this->convert[$table][1];
		$usekey = $this->convert[$table][2];

		$this->build_nav("正在转换 {$current_table} 表数据，在执行过程中不要进行任何操作！");

		$this->lib->echo_flush("正在转换 {$current_table} 表数据...\n");

		$vars = $DB->query( "SELECT ".$usekey.", ".implode(", ", $current_col)." FROM ".TABLE_PREFIX."$current_table LIMIT $pp, $percycle" );

		if ($DB->num_rows($vars)) {
			while ($value = $DB->fetch_array($vars)) {
				$up = array();
				foreach ($current_col AS $col) {
					$value[$col] =	str_replace("<br>", "<br />", $value[$col]);
					//转码部分开始
					if ($this->convert_func == "mb_convert_encoding") {
						$text =	mb_convert_encoding($value[$col], $this->to_chars, $this->from_chars);
					} elseif ($this->convert_func == "iconv") {
						$text =	iconv($this->from_chars, $this->to_chars, $value[$col] );
					} else {
						$text = preg_replace("#(\r\n|\r|\n)#", "<br />", $text);
						$text = $this->convert->EncodeString($text, $this->from_chars, $this->to_chars);
						$text = str_replace("<br />", "\r\n", $text);
					}
					$up[] = "{$col}='".addslashes($text)."'";
				}
				$up_value = implode(", ", $up);
				$DB->query_unbuffered("UPDATE ".TABLE_PREFIX."$current_table SET {$up_value} WHERE ".$usekey." = '".$value[$usekey]."'");
				$this->lib->echo_flush("已转换完成 {$current_table} 表数据 {$usekey} - ".$value[$usekey]."<br />\r\n");
			}



			$pp+=$percycle;
			echo "<script language='javascript'>setTimeout(function() {window.location='index.php?".$forums->js_sessionurl."do=convert&servername=".$_GET['servername']."&dbprefix=".$_GET['dbprefix']."&dbname=".$_GET['dbname']."&dbusername=".$_GET['dbusername']."&dbpassword=".urlencode($_GET['dbpassword'])."&dbcharset=".$_GET['dbcharset']."&serverport=".$_GET['serverport']."&pp=".$pp."&percycle=".$percycle."&password=".urlencode($_GET['password'])."';}, 2000);</script>\n";
			$this->lib->echo_flush("<hr><b>&raquo; <a href='index.php?".$forums->sessionurl."do=convert&amp;servername=".$_GET['servername']."&amp;dbprefix=".$_GET['dbprefix']."&amp;dbname=".$_GET['dbname']."&amp;dbusername=".$_GET['dbusername']."&amp;dbpassword=".urlencode($_GET['dbpassword'])."&amp;dbcharset=".$_GET['dbcharset']."&amp;serverport=".$_GET['serverport']."&amp;pp=".$start."&amp;percycle=".$percycle."&amp;password=".urlencode($_GET['password'])."'>点击这里继续转换 {$current_table} 表的下{$start}条数据</a> &laquo;</b>\n");
		} else {
			$table += 1;
			$this->lib->echo_flush("<p>数据表已成功转换</p>");

			echo "<script language='javascript'>setTimeout(function() {window.location='index.php?".$forums->js_sessionurl."do=convert&servername=".$_GET['servername']."&dbprefix=".$_GET['dbprefix']."&dbname=".$_GET['dbname']."&dbusername=".$_GET['dbusername']."&dbpassword=".urlencode($_GET['dbpassword'])."&dbcharset=".$_GET['dbcharset']."&serverport=".$_GET['serverport']."&table=".$table."&pp=".$pp."&percycle=".$percycle."&password=".urlencode($_GET['password'])."';}, 2000); </script>\n";
			$this->lib->echo_flush( "<a href='index.php?do=convert&amp;servername=".$_GET['servername']."&amp;dbprefix=".$_GET['dbprefix']."&amp;dbname=".$_GET['dbname']."&amp;dbusername=".$_GET['dbusername']."&amp;dbpassword=".urlencode($_GET['dbpassword'])."&amp;dbcharset=".$_GET['dbcharset']."&amp;serverport=".$_GET['serverport']."&amp;table=".$table."&amp;pp=0&amp;percycle={$percycle}&amp;password=".urlencode($_GET['password'])."'>{$current_table} 表数据已成功完成转换，现在将跳转到下一个数据表转换页面", "正跳转至数据表转换</a>" );
		}
	}

	function howto()
	{
		echo "
		<p>所需软件：ConvertZ (<a href='http://www.molyx.com/tools/convertz802.zip' target='_blank'>下载地址</a>)<br /></p>

		<ol>
			<li>将 ConvertZ 下载至本地，并打开执行。 此时在系统任务栏会有 <img src='images/1.gif' /> 图标，同时在屏幕上方会有一个导航条自动隐藏。把鼠标移到屏幕上方会看到如：<br /><img src='images/2.gif' /><br />的导航条<br /><br /><br /><br /></li>
			<li>点击 <img src='images/3.gif' /> ，此时屏幕会弹出提示框：<br /><img src='images/4.gif' />
			<br /><br /><br /><br /></li>
			<li>点击 <img src='images/5.gif' /> ，选择备份 MySQL 数据所在目录，并选择要转换的文件加入到右边的对话框内。<br /><img src='images/6.gif' />
			<br /><br /><br /><br /></li>
			<li>点击 <img src='images/7.gif' /> ，指定恢复数据文件存放的位置。
			<br /><br /><br /><br /></li>
			<li>点击 <img src='images/8.gif' /> ，切换到文件转换高级选项，并将输入格式选为 GBK，输出格式选为 UTF-8。<br />
			<img src='images/9.gif' />
			<br /><br /><br /><br /></li>
			<li>点击 <img src='images/10.gif' /> ，下面你就可以通过点击 <img src='images/11.gif' /> 开始转换。转换完的数据可以在你设定的输出目录里面看到。
			<br /><br /><br /><br /></li>
			<li>最后，请把转换好的数据复制到 当前目录的 db 子目录下，通过本工具进行导入恢复</li>

		";
	}

	function finish()
	{
		echo "
			数据记录已成功完成转换！通过将下载的 config.php 文件上传到 includes 目录内，开始升级MolyX Board 至最新版本！<br />
			升级完成后，请务必重新生成缓存，并重新生成全部模版文件！<br /><br />
			<a href='index.php?do=generate_config&amp;servername=".$_GET['servername']."&amp;dbprefix=".$_GET['dbprefix']."&amp;dbname=".$_GET['dbname']."&amp;dbusername=".$_GET['dbusername']."&amp;dbpassword=".urlencode($_GET['dbpassword'])."&amp;dbcharset=".$_GET['dbcharset']."&amp;serverport=".$_GET['serverport']."&amp;password=".urlencode($_GET['password'])."' target='_blank'>下载程序自动生成的 config.php 文件</a><br />
		";
			exit;
	}

	function generate_config()
	{
		ob_clean();
		ob_start();

		@header('Content-Type: text/octetstream; charset=UTF-8');
		@header('Content-Disposition: attachment; filename="config.php"');
		@header('Pragma: no-cache');
		@header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');

		@require ( ROOT_PATH.'includes/config.php' );
		if (count($config) < 1) {
			$servername = $servername;
			$dbname = $dbname;
			$dbusername = $dbusername;
			$dbpassword = $dbpassword;
			$tableprefix = $tableprefix;
			$usepconnect = $usepconnect;
			$superadmin = $superadmin;
			$technicalemail = $technicalemail;
			$dbtype = "";
			$dbcharset = "";
			$forumurl = "";
			$remoteattach = "";
		} else {
			$servername = $config['servername'];
			$dbname = $config['dbname'];
			$dbusername = $config['dbusername'];
			$dbpassword = $config['dbpassword'];
			$tableprefix = $config['tableprefix'];
			$usepconnect = $config['usepconnect'];
			$superadmin = $config['superadmin'];
			$technicalemail = $config['technicalemail'];
			$dbtype = $config['dbtype'];
			$dbcharset = $config['dbcharset'];
			$forumurl = $config['forumurl'];
			$remoteattach = $config['remoteattach'];
		}

		if ($_GET['serverport'] != '3306') {
			$serverport = ":".intval($_GET['serverport']);
		}

		$servername = $servername ? $servername : $_GET['servername'].$serverport;
		$dbname = $dbname ? $dbname : $_GET['dbname'];
		$dbusername = $dbusername ? $dbusername : $_GET['dbusername'];
		$dbpassword = $dbpassword ? $dbpassword : $_GET['dbpassword'];
		$tableprefix = $tableprefix ? $tableprefix : $_GET['dbprefix'];
		$dbcharset = $dbcharset ? $dbcharset : $_GET['dbcharset'];

		print '<?php
/* You need to upload it into your includes/ directory! */
$config[\'servername\'] = "'.$servername.'";
$config[\'dbname\'] = "'.$dbname.'";
$config[\'dbusername\'] = "'.$dbusername.'";
$config[\'dbpassword\'] = "'.$dbpassword.'";
$config[\'tableprefix\'] = "'.$tableprefix.'";
$config[\'usepconnect\'] = "'.$usepconnect.'";
$config[\'superadmin\'] = "'.$superadmin.'";
$config[\'technicalemail\'] = "'.$technicalemail.'";
$config[\'dbtype\'] = "'.$dbtype.'";
$config[\'dbcharset\'] = "'.$dbcharset.'";
$config[\'forumurl\'] = "'.$forumurl.'";
$config[\'remoteattach\'] = "'.$remoteattach.'";
?>';
ob_end_flush();
exit;
	}


}

$output = new convert();
$output->show();

?>