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
define('IN_SQL', TRUE);
require ('./global.php');
if (function_exists('set_time_limit') AND ini_get('safe_mode') == '')
{
	@set_time_limit(0);
}

class mysql {

	var $dbsql = '';
	var $droptable = 1;
	var $createtable = 1;
	var $tableid = 0;
	var $offset = 0;
	var $skip = 1;
	var $step = 1;
	var $advbackup = 1;
	var $enablegzip = 0;
	var $dbblocksize = 0;
	var $hex_type = 0;
	var $mysql_version   = "";
	var $true_version    = "";
	var $str_gzip_header = "\x1f\x8b\x08\x00\x00\x00\x00\x00";

	function show()
	{
		global $forums, $DB, $_INPUT, $bbuserinfo;
		$admin = explode(',', SUPERADMIN);
		if(!in_array($bbuserinfo['id'], $admin) && !$forums->adminperms['caneditmysql']) {
			if (!$fp = @fopen(ROOT_PATH.'data/dbbackup/unlock.key', 'r')) {
				$forums->admin->print_cp_error($forums->lang['nopermissions']);
			}
			fclose($fp);
		}
		if (!$row = $DB->query_first('SELECT VERSION() AS version')) {
			$row = $DB->query_first("SHOW VARIABLES LIKE 'version'");
		}
		$forums->admin->nav[] = array('mysql.php', $forums->lang['managemysql']);
		$this->true_version = $row['version'];
		$no_array = explode('.', preg_replace("/^(.+?)[-_]?/", "\\1", $row['version']));
		$one = (!isset($no_array) || !isset($no_array[0])) ? 3 : $no_array[0];
		$two = (!isset($no_array[1])) ? 21 : $no_array[1];
		$three = (!isset($no_array[2])) ? 0 : $no_array[2];
		$this->savedate = $_INPUT['savedate'] ? intval($_INPUT['savedate']) : date('Ymd', TIMENOW);
		$this->md5_check = $_INPUT['check'] ? trim($_INPUT['check']) : md5(TIMENOW . SESSION_HOST);
   		$this->mysql_version = (int) sprintf('%d%02d%02d', $one, $two, intval($three));
		switch($_INPUT['do']) {
			case 'dotool':
				$this->sqltool();
				break;
			case 'runtime':
				$this->view_sql('SHOW STATUS');
				break;
			case 'system':
				$this->view_sql('SHOW VARIABLES');
				break;
			case 'processes':
				$this->view_sql('SHOW PROCESSLIST');
				break;
			case 'runsql':
				$q = ($_POST['query'] == '') ? rawurldecode($_GET['query']) : $_POST['query'];
				$this->view_sql(trim($forums->func->stripslashes_uni($q)));
				break;
			case 'backup':
				$this->backup_form();
				break;
			case 'restore':
				$this->restore_form();
				break;
			case 'confirmrestore':
				$this->confirmrestore();
				break;
			case 'confirmbackup':
				$this->confirmbackup();
				break;
			case 'dobackup':
				$this->dobackup();
				break;
			case 'dorestore':
				$this->dorestore();
				break;
			case 'export_tbl':
				$this->dobackup(trim(rawurldecode($forums->func->stripslashes_uni($_GET['tbl']))));
				break;
			case 'delsql':
				$this->dodeletesql();
				break;
			default:
				$this->sqlmain();
				break;
		}
	}

	function dobackup($tbl_name = '')
	{
		global $forums, $DB, $_INPUT;
		$this->onlymolyx = intval($_INPUT['onlymolyx']);
		$this->skip = intval($_INPUT['skip']);
		$this->createtable = intval($_INPUT['createtable']);
		$this->droptable = intval($_INPUT['droptable']);
		$this->enablegzip = intval($_INPUT['enablegzip']);
		$this->advbackup = intval($_INPUT['advbackup']);
		$this->hex_type = intval($_INPUT['hex_type']);
		if ($tbl_name == '') {
			$filename = 'molyx_dbbackup';
		} else {
			$filename = $tbl_name;
		}
		$this->noshow = false;
		if ($this->advbackup) {
			return $this->doadvbackup();
		}
		$output = '';
		@header('Pragma: no-cache');
		$do_gzip = 0;
		if($this->enablegzip) {
			$phpver = phpversion();
			if($phpver >= '4.0') {
				if(extension_loaded("zlib")) {
					$do_gzip = 1;
				}
			}
		}
		if($do_gzip) {
			@ob_start();
			@ob_implicit_flush(0);
			header("Content-disposition: attachment; filename=$filename.sql.gz");
		} else {
			header("Content-disposition: attachment; filename=$filename.sql");
		}
		header('Content-type: unknown/unknown');
		$sql_header = $this->export_header();
		echo $sql_header;
		if ($tbl_name == '') {
			$tmp_tbl = $DB->get_table_names();
			foreach($tmp_tbl as $tbl) {
				if ($this->onlymolyx) {
					if (preg_match("/^".TABLE_PREFIX."/", $tbl)) {
						$this->get_table_sql($tbl);
					}
				} else {
					$this->get_table_sql($tbl);
				}
				$this->dbsql = "\r\n";
			}
		} else {
			$this->get_table_sql($tbl_name);
		}
		if($do_gzip) {
			$size = ob_get_length();
			$crc = crc32(ob_get_contents());
			$contents = gzcompress(ob_get_contents());
			ob_end_clean();
			echo $this->str_gzip_header
				.substr($contents, 0, strlen($contents) - 4)
				.$this->gzip_four_chars($crc)
				.$this->gzip_four_chars($size);
		}
		exit();
	}

	function get_table_sql($tbl)
	{
		global $forums, $DB, $_INPUT;
		if ($this->createtable) {
			if (isset($this->noshow) AND !$this->noshow) {
				$this->dbsql .= "\r\n\r\n\r\n# --------------------------------------------------------\r\n\r\n";
				$this->dbsql .= "#\r\n";
				$this->dbsql .= "#     Export table '".$tbl."';\r\n";
				$this->dbsql .= "#\r\n\r\n";
			}
			if ($this->droptable) {
				if (empty($this->offset) OR $this->offset == 0) {
					$this->dbsql .= "DROP TABLE IF EXISTS ".$tbl.";\r\n";
				}
			}
			if ($_INPUT['addticks']) {
				$tables = $DB->query("SHOW CREATE TABLE `$tbl`");
			} else {
				$tables = $DB->query("SHOW CREATE TABLE $tbl");
			}
			$ctable = $DB->fetch_array($tables);
			$ctable = $ctable['Create Table'];
			if (strstr($ctable, 'mediumtext NOT NULL') && !strstr($ctable, 'mediumtext NOT NULL default')) {
				$ctable = str_replace('mediumtext NOT NULL', 'mediumtext NOT NULL default \'\'', $ctable);
			}
			$ctable = preg_replace("/ DEFAULT CHARSET=(?:\w+)/is", '', $ctable);
			$ctable = preg_replace("/ CHARACTER SET (?:\w+) /is", ' ', $ctable);
			if (!preg_match("/ (ENGINE|TYPE)=(HEAP|MEMORY)/is", $ctable)) {
				$ctable = preg_replace("/ (ENGINE|TYPE)=[^ ]+/is", '', $ctable);
			}
			if ($this->advbackup) {
				if (empty($this->offset) OR $this->offset == 0) {
					$this->dbsql .= $ctable . ";\r\n\r\n";
				}
			} else {
				echo $this->dbsql;
				echo $ctable . ";\r\n\r\n";
			}
			$DB->free_result($tables);
		}
		if ($this->skip == 1) {
			if (in_array($tbl, array(TABLE_PREFIX.'adminsession', TABLE_PREFIX.'session', TABLE_PREFIX.'antispam', TABLE_PREFIX.'search'))) {
				return;
			}
		}
		$limit = 500;
		if($this->advbackup AND $this->dbblocksize) {
			$this->addquery = " LIMIT ".$this->offset.", $limit";
		}
		$querys = $DB->query("SELECT * FROM $tbl".$this->addquery);

		$row_num = $DB->num_rows($querys);
		if ($row_num < 1) {
			$DB->free_result($querys);
			return;
		} else if ($row_num < $limit) {
			$this->noshow = true;
		}
		$db_key = '';
		$fields = $DB->get_result_fields($querys);
		$cnt = count($fields);
		for($i = 0; $i < $cnt; $i++) {
			$db_key .= '`' . $fields[$i]->name . '`, ';
		}
		$db_key = substr($db_key, 0, -2);
		$row_lines = 0;

		while ($row = $DB->fetch_array($querys)) {
			$row_lines++;
			$db_value = '';
			for($i = 0; $i < $cnt; $i++) {
				if (!isset($row[$fields[$i]->name])) {
					$db_value .= "NULL,";
				} else if ($row[$fields[$i]->name] != '') {
					if ($this->hex_type && in_array($fields[$i]->type, array('string', 'blob'))) {
						$db_value .= '0x'.bin2hex($row[$fields[$i]->name]).',';
					} else {
						$db_value .= "'".$this->sql_add_slashes($row[$fields[$i]->name]). "',";
					}
				} else {
					$db_value .= "'',";
				}
			}
			$db_value = substr($db_value, 0, -1);
			if ($this->advbackup) {
				$this->dbsql .= "INSERT INTO $tbl ($db_key) VALUES($db_value);\r\n";
				$this->offset++;
				if ($this->dbblocksize) {
					if (strlen($this->dbsql) > ($this->dbblocksize * 1024)) {
						$this->to_write();
					}
				}
			} else {
				echo "INSERT INTO $tbl ($db_key) VALUES($db_value);\r\n";
			}
		}
		if ($this->dbblocksize) {
			$DB->free_result($querys);
			$this->check_sql($tbl);
			$this->noshow = true;
		}
	}

	function check_sql($tbl)
	{
		if (strlen($this->dbsql) > ($this->dbblocksize * 1024)) {
			$this->to_write();
		} else {
			return $this->get_table_sql($tbl);
		}
	}

	function doadvbackup()
	{
		global $DB, $forums, $_INPUT;
		$this->dbblocksize = intval($_INPUT['dbblocksize']);
		$this->tableid = $_INPUT['tableid'] ? intval($_INPUT['tableid']) : 0;
		$this->offset = $_INPUT['offset'] ? intval($_INPUT['offset']) : 0;
		$this->dbexportfolder = preg_replace('/[^a-zA-Z0-9\-_]/', '', $_INPUT['dbexportfolder']);
		if ($this->dbexportfolder == '') {
			$forums->main_msg = $forums->lang['requirefoldername'];
			$this->backup_form();
		}
		if ($this->dbblocksize < 50 AND $this->dbblocksize != 0) {
			$forums->main_msg = $forums->lang['sqlfiletoosmall'];
			$this->backup_form();
		}
		$tmp_tbl = $DB->get_table_names();
		$t = 0;
		$this->step = $_INPUT['step'] ? intval($_INPUT['step']) : 1;
		foreach ($tmp_tbl as $tbl) {
			$this->noshow = false;
			if ($this->onlymolyx) {
				if (preg_match("/^".TABLE_PREFIX."/", $tbl)) {
					$this->next_tableid++;
					if ($this->next_tableid < $this->tableid) {
						continue;
					} else {
						$this->get_table_sql($tbl);
					}
					$this->offset = 0;
				}
			} else {
				$this->next_tableid++;
				if ($this->next_tableid < $this->tableid) {
					continue;
				} else {
					$this->get_table_sql($tbl);
				}
				$this->offset = 0;
			}
		}
		$this->write_to_file($this->dbsql, 1);
		$forums->admin->redirect('mysql.php?do=backup', $forums->lang['managemysql'], $forums->lang['sqlfilesavefinished']);
	}

	function to_write()
	{
		global $forums, $_INPUT;
		$this->write_to_file($this->dbsql);
		$forums->lang['sqlfilesaved'] = sprintf($forums->lang['sqlfilesaved'], intval($_INPUT['step']) ? intval($_INPUT['step']) - 1 : 0);
		$forums->admin->redirect("mysql.php?do=dobackup&amp;step=".$this->step."&amp;advbackup=1&amp;offset=".$this->offset."&amp;dbblocksize=".$this->dbblocksize."&amp;tableid=".$this->next_tableid."&amp;savedate=".$this->savedate."&amp;onlymolyx=".$this->onlymolyx."&amp;check=".$this->md5_check."&amp;skip=".$this->skip."&amp;createtable=".$this->createtable."&amp;dbexportfolder=".$this->dbexportfolder."&amp;hex_type=".$this->hex_type.'&amp;droptable='.$this->droptable.'&amp;addticks'.intval($_INPUT['addticks']), $forums->lang['managemysql'], $forums->lang['sqlfilesaved']);
		exit;
	}

	function write_to_file($data, $finish='0')
	{
		global $forums, $bboptions, $_INPUT;
		$savefolder = $_INPUT['dbexportfolder'] ? $_INPUT['dbexportfolder'] : $this->savedate;
		$dot = SAFE_MODE ? '_' : '/';
		if (is_writeable(ROOT_PATH.'data/dbbackup')) {
			if (SAFE_MODE) {
				if (file_exists(ROOT_PATH.'data/dbbackup/'.$savefolder.'_'.$this->md5_check.'_'.$this->step.'.sql')) {
					if (!is_writeable(ROOT_PATH.'data/dbbackup/'.$savefolder.'_'.$this->md5_check.'_'.$this->step.'.sql')) {
						$forums->main_msg = 'data/dbbackup /'.$savefolder.'_'.$this->md5_check.'_'.$this->step.'.sql '.$forums->lang['filecannotwrite'];
						$this->backup_form();
					}
				}
			} else {
				if (!is_dir(ROOT_PATH.'data/dbbackup/'.$savefolder)) {
					if (!@mkdir(ROOT_PATH.'data/dbbackup/'.$savefolder, 0777)) {
						$forums->main_msg = "data/dbbackup ".$forums->lang['cannotcreate'];
						$this->backup_form();
					} else {
						@chmod(ROOT_PATH.'data/dbbackup/'.$savefolder, 0777);
					}
				} else {
					if ($this->step == 1) {
						$forums->admin->rm_dir(ROOT_PATH.'data/dbbackup/'.$savefolder, 0);
					}
					if (file_exists(ROOT_PATH.'data/dbbackup/'.$savefolder.'/'.$this->md5_check.'_'.$this->step.'.sql')) {
						if (!is_writeable(ROOT_PATH.'data/dbbackup/'.$savefolder.'/'.$this->md5_check.'_'.$this->step.'.sql')) {
							$forums->main_msg = 'data/dbbackup /'.$savefolder.'/'.$this->md5_check.'_'.$this->step.'.sql '.$forums->lang['filecannotwrite'];
							$this->backup_form();
						}
					}
				}
			}
		} else {
			$forums->main_msg = "data/dbbackup ".$forums->lang['cannotwrite'];
			$this->backup_form();
		}
		if ($this->dbblocksize) {
			$dumptype = 'Multi Volume Backup';
		} else {
			$dumptype = 'Standard Backup';
		}
		if($this->step == 1 AND $finish) {
			$vol= 'NULL';
		} else {
			$vol= 'vol_'.$this->step;
		}
		$sql_header = $this->export_header($dumptype, $vol, $this->step, $finish, strlen($this->dbsql));
		$this->dbsql = $sql_header.$this->dbsql;
		if ($fp = @fopen(ROOT_PATH.'data/dbbackup/'.$savefolder.$dot.$this->md5_check.'_'.$this->step.'.sql', 'wb')) {
			flock($fp, LOCK_EX | LOCK_NB);
			fwrite($fp, $this->dbsql, strlen($this->dbsql));
			fclose($fp);
			@chmod(ROOT_PATH.'data/dbbackup/'.$savefolder.$dot.$this->md5_check.'_'.$this->step.'.sql', 0777);
			$this->step++;
			return;
		}
	}

	function sql_add_slashes($data)
	{
        return str_replace(array('\\', '\'', "\r", "\n"), array('\\\\', '\\\'', '\r', '\n'), $data);
	}

	function confirmbackup()
	{
		global $forums, $DB, $_INPUT;
		$pagetitle  = "MySQL ".$this->true_version." ".$forums->lang['mysqlbackup'];
		$detail = $forums->lang['mysqlbackupdesc'];
		$forums->admin->nav[] = array('mysql.php?do=backup', $forums->lang['mysqlbackup']);
		$forums->admin->print_cp_header($pagetitle, $detail);
		if ($this->mysql_version < 32321) {
			$forums->admin->print_cp_error($forums->lang['mysqlversiontooold']);
		}
		if ($_INPUT['advbackup']) {
			$type = $forums->lang['sqladvancedmode'];
			$_INPUT['droptable'] = intval($_INPUT['droptable']);
			$_INPUT['createtable'] = intval($_INPUT['createtable']);
			if (is_dir(ROOT_PATH. 'data/dbbackup/'. $_INPUT['dbexportfolder'])) {
				$extra = '<br /><br /><strong>'.$forums->lang['backupfolderexist'].'</strong><br /><br />';
			}
		} else {
			$type = $forums->lang['sqlnormalmode'];
		}
		$forums->admin->print_form_header(array(1 => array('do', 'dobackup'),
			2 => array('droptable', $_INPUT['droptable']),
			3 => array('createtable', $_INPUT['createtable']),
			4 => array('addticks', $_INPUT['addticks']),
			5 => array('skip', $_INPUT['skip']),
			6 => array('enablegzip', $_INPUT['enablegzip']),
			7 => array('advbackup', $_INPUT['advbackup']),
			8 => array('dbblocksize', $_INPUT['dbblocksize']),
			9 => array('dbexportfolder', $_INPUT['dbexportfolder']),
			10 => array('onlymolyx', $_INPUT['onlymolyx']),
			11 => array('hex_type', $_INPUT['hex_type']),)
		, 'dobackform');
		$forums->admin->print_table_start($forums->lang['mysqlbackup']." - " . $type);
		$forums->admin->print_cells_single_row($forums->lang['sqlbackupinfo'].$extra);
		$forums->admin->print_form_submit($forums->lang['startbackup']);
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_cp_footer();
	}

	function backup_form()
	{
		global $forums, $DB;
		$pagetitle  = 'MySQL '.$this->true_version.' '.$forums->lang['mysqlbackup'];
		$detail = $forums->lang['mysqlbackupdesc'];
		$forums->admin->nav[] = array('mysql.php?do=backup', $forums->lang['mysqlbackup']);
		$forums->admin->print_cp_header($pagetitle, $detail);
		if ($this->mysql_version < 32321) {
			$forums->admin->print_cp_error($forums->lang['mysqlversiontooold']);
		}
		$forums->admin->columns[] = array('&nbsp;', '60%');
		$forums->admin->columns[] = array('&nbsp;', '40%');
		$forums->admin->print_form_header(array(1 => array('do' , 'confirmbackup')), 'confirmform');
		$forums->admin->print_table_start($forums->lang['sqlnormalmodeinfo']);
		$forums->admin->print_cells_row(array(
			"<strong>".$forums->lang['addcreatepart']."</strong><div class='description'>".$forums->lang['addcreatepartdesc']." <input type='checkbox' name='addticks' value=1 /></div>",
			$forums->admin->print_yes_no_row('createtable', 1),)
		);
		$forums->admin->print_cells_row(array(
			"<strong>".$forums->lang['adddroppart']."</strong><div class='description'>".$forums->lang['adddroppartdesc']."</div>",
			$forums->admin->print_yes_no_row('droptable', 1),)
		);
		$forums->admin->print_cells_row(array(
			"<strong>".$forums->lang['skiptrashdata']."</strong><div class='description'>".$forums->lang['skiptrashdatadesc']."</div>",
			$forums->admin->print_yes_no_row('skip', 1),)
		);
		$forums->admin->print_cells_row(array(
			"<strong>".$forums->lang['backupmxdata']."</strong><div class='description'>".$forums->lang['backupmxdatadesc']."</div>",
			$forums->admin->print_yes_no_row('onlymolyx', 1),)
		);
		$forums->admin->print_cells_row(array(
			"<strong>".$forums->lang['hex_type']."</strong><div class='description'>".$forums->lang['hex_type_desc']."</div>",
			$forums->admin->print_yes_no_row('hex_type', 0),)
		);
		$forums->admin->print_cells_row(array(
			"<strong>".$forums->lang['enablegzip']."</strong><div class='description'>".$forums->lang['enablegzipdesc']."</div>",
			$forums->admin->print_yes_no_row('enablegzip', 1),)
		);
		$forums->admin->print_table_footer();
		$forums->admin->columns[] = array('&nbsp;', '60%');
		$forums->admin->columns[] = array('&nbsp;', '40%');
		$forums->admin->print_table_start($forums->lang['advancedmode']);
		$forums->lang['advancedbackupdesc'] = sprintf($forums->lang['advancedbackupdesc'], $this->savedate);
		$forums->admin->print_cells_row(array(
			"<strong>".$forums->lang['advancedbackup']."</strong><div class='description'>".$forums->lang['advancedbackupdesc']."</div>",
			$forums->admin->print_yes_no_row('advbackup', 0),)
		);
		$forums->admin->print_cells_row(array(
			"<strong>".$forums->lang['advancedbktype']."</strong><div class='description'>".$forums->lang['advancedbktypedesc']."</div>",
			$forums->admin->print_input_row('dbblocksize', 0),)
		);
		$forums->admin->print_cells_row(array(
			"<strong>".$forums->lang['advancedbkfolder']."</strong><div class='description'>".$forums->lang['advancedbkfolderdesc']."</div>",
			$forums->admin->print_input_row('dbexportfolder', $this->savedate),)
		);
		$forums->admin->print_form_submit($forums->lang['startbackup']);
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_cp_footer();
	}

	function restore_form()
	{
		global $forums, $DB, $_INPUT;
		$_INPUT['fromserver'] = $_INPUT['fromserver'] ? intval($_INPUT['fromserver']) : '';
		$pagetitle  = "MySQL ".$this->true_version." ".$forums->lang['mysqlrestore'];
		$detail = $forums->lang['mysqlrestoredesc'];
		$forums->admin->nav[] = array('mysql.php?do=restore', $forums->lang['mysqlrestore']);
		$forums->admin->print_cp_header($pagetitle, $detail);
		$forums->admin->columns[] = array('&nbsp;', '60%');
		$forums->admin->columns[] = array('&nbsp;', '40%');
		$forums->admin->print_form_header(array(1 => array('do' , 'confirmrestore')), "confirmreform", "enctype='multipart/form-data'");
		$forums->admin->print_table_start($forums->lang['importsqlfile']);
		$forums->admin->print_cells_row(array(
			"<strong>".$forums->lang['importlocalsqlfile']."</strong><div class='description'>".$forums->lang['importlocalsqlfiledesc']."</div>",
			"<input type='file' value='{$_INPUT['fromlocal']}' class='button' name='fromlocal' size='30' />",)
		);
		$forums->admin->print_cells_row(array(
			"<strong>".$forums->lang['importserversqlfile']."</strong><div class='description'>".$forums->lang['importserversqlfiledesc']."</div>",
			$forums->admin->print_yes_no_row('fromserver', $_INPUT['fromserver']),)
		);
		$forums->admin->print_cells_single_row("<input type='submit' value=' ".$forums->lang['importsqldata']." ' class='button' accesskey='s' />", "center", "pformstrip");
		$forums->admin->print_table_footer();
		$forums->admin->columns[] = array($forums->lang['sqlselected'], '');
		$forums->admin->columns[] = array($forums->lang['sqlinfolder'], '20%');
		$forums->admin->columns[] = array($forums->lang['sqlsavedate'], '20%');
		$forums->admin->columns[] = array($forums->lang['sqlbackuptype'], '20%');
		$forums->admin->columns[] = array($forums->lang['sqlfilesize'], '10%');
		$forums->admin->columns[] = array($forums->lang['sqlfilenums'], '10%');
		$forums->admin->columns[] = array($forums->lang['option'], '20%');
		$forums->admin->print_table_start($forums->lang['serversqlfilelist']);
		$dh = opendir(ROOT_PATH.'data/dbbackup');
		while (false !== ($file = readdir($dh))) {
 			if ($file != '.' && $file != '..') {
 				if (is_dir(ROOT_PATH.'data/dbbackup/'.$file)) {
					$sqldirs['dir'][] = $file;
				} else {
					if (preg_match("/\.(?:sql)$/i", $file)) {
						$sqldirs['file'][] = $file;
					} else {
						@unlink(ROOT_PATH.'data/dbbackup/'.$file);
					}
				}
 			}
 		}
 		closedir($dh);
		if(is_array($sqldirs)) {
			if(is_array($sqldirs['file'])) {
				$key = '';
				foreach($sqldirs['file'] AS $files) {
					if (!preg_match("/^".$key."_(\w){32}_(\d+).sql$/", $files)) {
						$key = preg_replace("/^(\d+)_(\w){32}_(\d+).sql$/", "\\1\\2", $files);
					}
					$groupdirs[$key][] = $files;
				}
				$sqldirs['dir']  = array_merge($groupdirs, $sqldirs['dir']);
			}
			foreach($sqldirs['dir'] as $key => $dir) {
				if (is_array($dir)) {
					$file_nums = 1;
					$files = array();
					$thisfiles = array();
					$info = array();
					$filesize = 0;
					natcasesort($dir);
					foreach ($dir as $file) {
						if ($fp = @fopen(ROOT_PATH.'data/dbbackup/'.$file, 'rb')) {
							$size = filesize(ROOT_PATH.'data/dbbackup/'.$file);
							$filesize += $size;
							$info = explode(',', base64_decode(preg_replace("/^# key:\s*(\w+).*/s", "\\1", fgets($fp, 256))));
							$thisfiles[] = $file;
							if ($info['2'] != 'NULL' AND intval($info['3']) < $file_nums) {
								@fclose($fp);
								continue;
							}
							@fclose($fp);
						}
						$file_nums++;
					}
					if ($info['3'] != count($dir)) {
						$files = array('errors' => 1);
					} else {
						$files = array('files' => $thisfiles, 'info' => $info , 'filesize' => $filesize);
						$dir = preg_replace("/^(\d+)_(\w){32}_(\d+).sql$/", "\\1", $files['files'][0]);
					}
				} else {
					$files = $this->get_folder_contents($dir);
				}
				$count = intval(count($files['files']));
				if ($files['errors']) {
					$forums->admin->print_cells_single_row($forums->lang['sqlfilecannotrestore']);
				} else {
					$forums->admin->print_cells_row(array(
						"<input type='radio' name='selectid' value='$dir' />",
						$dir,
						"<center>".$files['info'][0]."</center>",
						"<center>".$files['info'][1]."</center>",
						"<center>".$forums->func->fetch_number_format($files['filesize'], true)."</center>",
						"<center>".$count."</center>",
						"<center><a href='mysql.php?{$forums->sessionurl}do=delsql&amp;id=".$dir."'>".$forums->lang['delete']."</a></center>",)
					);
				}
			}
		}
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_cp_footer();
	}

	function get_folder_contents($folder)
	{
		$files = array();
		$info = array();
		$filesize = 0;
		$dh = @opendir(ROOT_PATH.'data/dbbackup/'.$folder);
		$file_nums = 1;
		while (false !== ($file = @readdir($dh))) {
 			if (($file != ".") && ($file != "..")) {
				$allfolder[] = $file;
 			}
 		}
		if (is_array($allfolder)) {
			natcasesort($allfolder);
			foreach ($allfolder AS $file) {
				if ($fp = @fopen(ROOT_PATH.'data/dbbackup/'.$folder.'/'.$file, 'rb')) {
					$size = filesize(ROOT_PATH.'data/dbbackup/'.$folder.'/'.$file);
					$filesize += $size;
					$info = explode(",", base64_decode(preg_replace("/^# key:\s*(\w+).*/s", "\\1", fgets($fp, 256))));
					$files[] = $file;
					if ($info[2] != 'NULL' AND intval($info[3]) < $file_nums) {
						@fclose($fp);
						continue;
					}
					@fclose($fp);
				}
				$file_nums++;
			}
		}
		@closedir($dh);
		if ($info['3'] != count($allfolder)) {
			return array('errors' => 1);
		}
 		return array('files' => $files, 'info' => $info , 'filesize' => $filesize);
 	}

	function dodeletesql()
	{
		global $forums, $_INPUT;
		$delfolder = trim($_INPUT['id']);
		if (SAFE_MODE) {
			$dh = opendir(ROOT_PATH.'data/dbbackup');
			while ($file = readdir($dh)) {
				if (($file != ".") && ($file != "..")) {
					if (preg_match("/^".$delfolder."_(\w){32}_(\d+).sql$/", $file)) {
						@unlink(ROOT_PATH.'data/dbbackup/'.$file);
					}
				}
			}
			closedir($dh);
		} else {
			$forums->admin->rm_dir(ROOT_PATH.'data/dbbackup/'.$delfolder);
			@rmdir($delfolder);
		}
		$forums->main_msg = $forums->lang['backupfolderdeleted'];
		$this->restore_form();
	}

	function confirmrestore()
	{
		global $forums, $_INPUT;
		if((! $_FILES['fromlocal']['name'] AND ! $_INPUT['fromserver'])) {
			$forums->main_msg = $forums->lang['norestorefiles'];
			$this->restore_form();
		} elseif (! $_FILES['fromlocal']['name'] AND ($_INPUT['fromserver'] AND ! $_INPUT['selectid'])) {
			$forums->main_msg = $forums->lang['norestorefiles'];
			$this->restore_form();
		}
		$filesize = 0;
		$rtype = $_FILES['fromlocal']['name'] ? 1 : 0;
		if ($_FILES['fromlocal']['name']) {
			$datafile = TIMENOW.'.tmp';
			$extension = strtolower(str_replace(".", "", substr($_FILES['fromlocal']['name'], strrpos($_FILES['fromlocal']['name'], '.'))));
			if ($extension == 'gz') {
				$forums->main_msg = $forums->lang['cannotextractgzfile'];
				$this->restore_form();
			}
			$filesize = $_FILES['fromlocal']['size'];
			$type = $forums->lang['local'];

			if (!@move_uploaded_file($_FILES['fromlocal']['tmp_name'], ROOT_PATH.'data/dbbackup/'.$datafile)) {
				if (!@copy($_FILES['fromlocal']['tmp_name'], ROOT_PATH.'data/dbbackup/'.$datafile)) {
					$forums->main_msg = $forums->lang['cannotimportfile'];
					$this->restore_form();
				}
			}
			$filestuff = @file_get_contents(ROOT_PATH.'data/dbbackup/'.$datafile);
			$info = explode(',', base64_decode(preg_replace("/^# key:\s*(\w+).*/s", "\\1", $filestuff)));
			if ($info[2] == 'NULL') {
				$file_nums = 1;
			} else {
				$file_nums = $forums->lang['localunknown'];
				$extra = "<br /><br />".$forums->lang['cannotbatchimport'];
			}
		} else {
			$type = $forums->lang['server'];
			if (SAFE_MODE) {
				$dh = opendir(ROOT_PATH.'data/dbbackup');
				while (false !== ($file = readdir($dh))) {
					if (($file != ".") && ($file != "..")) {
						$allfolder[] = $file;
					}
				}
				if (is_array($allfolder)) {
					natcasesort($allfolder);
					foreach ($allfolder AS $file) {
						if (preg_match("/^".$_INPUT['selectid']."_(\w){32}_(\d+).sql$/", $file)) {
							if ($fp = @fopen(ROOT_PATH.'data/dbbackup/'.$file, 'rb')) {
								$filesize += filesize(ROOT_PATH.'data/dbbackup/'.$file);
								$info = explode(",", base64_decode(preg_replace("/^# key:\s*(\w+).*/s", "\\1", fgets($fp, 256))));
								if($file_nums == 0) {
									$datafile = $file;
								}
								$files[] = $file;
								@fclose($fp);
							}
							$file_nums++;
						}
					}
				}
				closedir($dh);
			} else {
				$datapath = ROOT_PATH . 'data/dbbackup/'.$_INPUT['selectid'];
				$dh = opendir($datapath);
				$file_nums = 0;
				while (false !== ($file = readdir($dh))) {
					if (($file != ".") && ($file != "..")) {
						$allfolder[] = $file;
					}
				}
				if (is_array($allfolder)) {
					natcasesort($allfolder);
					foreach ($allfolder AS $file) {
						if ($fp = @fopen($datapath.'/'.$file, 'rb')) {
							$filesize += filesize($datapath.'/'.$file);
							$info = explode(",", base64_decode(preg_replace("/^# key:\s*(\w+).*/s", "\\1", fgets($fp, 256))));
							if($file_nums == 0) {
								$datafile = $file;
							}
							$files[] = $file;
							@fclose($fp);
						}
						$file_nums++;
					}
				}
				closedir($dh);
			}
		}
		$pagetitle  = "MySQL ".$this->true_version." ".$forums->lang['mysqlrestore'];
		$detail = $forums->lang['mysqlrestoredesc'];
		$forums->admin->nav[] = array('mysql.php?do=restore', $forums->lang['mysqlrestore']);
		$forums->admin->print_cp_header($pagetitle, $detail);
		$forums->admin->columns[] = array('' , '40%');
		$forums->admin->columns[] = array('', '60%');
		$forums->admin->print_form_header(array(
			1 => array('do', 'dorestore'),
			2 => array('type', $rtype),
			3 => array('file', urlencode($datafile)),
			4 => array('filepath', $_INPUT['selectid']),
			), "restoreform", "enctype='multipart/form-data'");
		$forums->admin->print_table_start($forums->lang['mysqlrestore']." - " . $type);
		$forums->admin->print_cells_single_row($forums->lang['confirmrestore'].$extra);
		$forums->admin->print_cells_row(array($forums->lang['restoresqlfilesize'].":", $filesize,));
		$forums->admin->print_cells_row(array($forums->lang['sqlfilenums'].":", $file_nums,));
		$forums->admin->print_cells_row(array($forums->lang['sqlbackuptype'].":", $info[1],));
		$forums->admin->print_cells_row(array($forums->lang['sqlsavedate'].":", $info[0],));
		$forums->admin->print_form_submit($forums->lang['restoresql']);
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_cp_footer();
	}

	function dorestore()
	{
		global $forums, $DB, $_INPUT;
		$type = $_INPUT['type'] ? 1 : 0;
		$file = trim(rawurldecode($_INPUT['file']));
		$filepath = trim($_INPUT['filepath']);
		if ($type) {
			$urlfile = ROOT_PATH.'data/dbbackup/'.$file;
		} else {
			if (SAFE_MODE) {
				$urlfile = ROOT_PATH.'data/dbbackup/'.$file;
			} else {
				$urlfile = ROOT_PATH.'data/dbbackup/'.$filepath.'/'.$file;
			}
		}
		$pp = $_INPUT['pp'] ? intval($_INPUT['pp']) : 1;
		if (!$fp = @fopen(ROOT_PATH.'data/dbbackup/unlock.key', 'w')) {
			$forums->main_msg = $forums->lang['cannotlock'];
			$this->restore_form();
		}
		@fclose($fp);
		if ($fp = fopen($urlfile, 'rb')) {
			$info = explode(",", base64_decode(preg_replace("/^# key:\s*(\w+).*/s", "\\1", fgets($fp, 256))));
			if ($info[2] != 'NULL' AND $pp == 0 AND $info[3] != 1 AND $type != 1) {
				if (SAFE_MODE) {
					$file = preg_replace("/^(\d+)_(\w{32})_(\d+).sql$/", "\\1_\\2_1.sql", $file);
				} else {
					$file = preg_replace("/^(\w{32})_(\d+).sql/", "\\1_1.sql"  , $file);
				}
				@fclose($fp);
				$forums->func->standard_redirect("mysql.php?{$forums->sessionurl}do=dorestore&amp;type=".$type."&amp;filepath=".$filepath."&amp;file=".urlencode($file)."pp=1");
			} else {
				$filesize = @filesize($urlfile);
				@flock($fp, LOCK_SH);
				while (!feof($fp))
				{
					$sql = '';
					$this->get_sql($fp, $sql);
					if(trim($sql) != '') {
						if(preg_match('/^(CREATE TABLE).*/i', $sql)) {
							$sql = str_replace(';', '', $sql);
							$sql = preg_replace("/DEFAULT CHARSET=(?:\w+)/is", '', $sql);
							$sql = preg_replace("/CHARACTER SET (?:\w+) /is", ' ', $sql);
							if (!preg_match("#(ENGINE|TYPE)=(HEAP|MEMORY)#is", $sql)) {
								$sql = preg_replace("/(ENGINE|TYPE)=[^ ]+/is", '', $sql);
								$add_engine = ' ENGINE=MyISAM';
							} else {
								$add_engine = '';
							}
							if ($this->mysql_version < 40100) {
								$sql = preg_replace("/(ENGINE|TYPE)=([^ ]+)/is", 'TYPE=HEAP', $sql);
								$add_engine = $add_engine ? ' TYPE=MyISAM' : '';
								$default_charset = '';
							} else {
								$sql = preg_replace("/(ENGINE|TYPE)=([^ ]+)/is", 'ENGINE=MEMORY', $sql);
								$default_charset = ' DEFAULT CHARSET='.($DB->dbcharset ? $DB->dbcharset : 'utf8');
							}
							$sql = $sql.$add_engine.$default_charset;
						}
						$DB->query_unbuffered($sql);
					}
				}
				@fclose($fp);
			}
			if ($info[2] == 'NULL' OR $type == 1) {
				@unlink(ROOT_PATH.'data/dbbackup/unlock.key');
				$forums->admin->redirect("mysql.php?do=backup", $forums->lang['managemysql'], $forums->lang['sqlfileimported']);
			} elseif ($info[4]) {
				@unlink(ROOT_PATH.'data/dbbackup/unlock.key');
				$forums->admin->redirect("mysql.php?do=backup", $forums->lang['managemysql'], $forums->lang['allsqlfileimported']);
			} else {
				$pp++;
				if (SAFE_MODE) {
					$nextfile = preg_replace("/^(\d+)_(\w{32})_(\d+).sql$/", "\\1_\\2_".$pp.".sql", $file);
				} else {
					$nextfile = preg_replace("/^(\w{32})_(.+?).sql/", "\\1_".$pp.".sql"  , $file);
				}
				$forums->admin->redirect("mysql.php?do=dorestore&amp;type=".$type."&amp;filepath=".$filepath."&amp;file=".urlencode($nextfile)."&amp;pp=".$pp."", $forums->lang['managemysql'], $forums->lang['importedsqlfile']." - ".$file." (vol:".($pp-1).")");
			}
		} else {
			$forums->main_msg = $forums->lang['cannotimportedfile']." - ".$file."";
			$this->restore_form();
		}
	}

	function get_sql(&$fp, &$sql)
	{
		$tmp = fgets($fp, 16384);
		$tmp = str_replace(array("\r\n", "\r"), array("\n", "\n"), $tmp);
		if ($tmp == '' || feof($fp) || preg_match('/^(-- |#)/', $tmp))
		{
			return '';
		}
		$sql .= $tmp;
		if (!preg_match("/;\n/", $sql))
		{
			$this->get_sql($fp, $sql);
		}
	}

	function view_sql($sql)
	{
		global $forums, $DB, $_INPUT;
		$limit = 30;
		$start = intval($_INPUT['pp']) == "" ? 0 : intval($_INPUT['pp']);
		$pages = "";
		$pagetitle  = "MySQL ".$this->true_version." ".$forums->lang['managemysql'];
		$detail = $forums->lang['managemysqldesc'];
		$forums->admin->nav[] = array('', $forums->lang['mysqlruninfo']);
		$forums->admin->print_cp_header($pagetitle, $detail);
		$map = array('processes' => $forums->lang['mysqlprocesses'],
					  'runtime'   => $forums->lang['mysqlruntime'],
					  'system'    => $forums->lang['mysqlsystem'],
					);
		if ($map[ $_INPUT['do'] ] != "") {
			$tbl_title = $map[ $_INPUT['do'] ];
			$this_query = FALSE;
		} else {
			$tbl_title = $forums->lang['manualquery'];
			$this_query = TRUE;
		}
		if ($this_query) {
			$forums->admin->columns[] = array("&nbsp;" , "100%");
			$forums->admin->print_form_header(array(1 => array('do' , 'runsql'),),'runsqlform');
			$forums->admin->print_table_start($forums->lang['doquery']);
			$forums->admin->print_cells_row(array("<center>".$forums->admin->print_textarea_row("query", $sql)."</center>"));
			$forums->admin->print_form_submit($forums->lang['doquery']);
			$forums->admin->print_table_footer();
			$forums->admin->print_form_end();
			if (preg_match("/^DROP|CREATE|FLUSH/i", trim($sql))) {
				$forums->admin->error = $forums->lang['querynorun'];
			}
		}
		$DB->return_die = 1;
		$DB->query($sql);
		if ($DB->error != "") {
			$forums->admin->columns[] = array("&nbsp;" , "100%");
			$forums->admin->print_table_start($forums->lang['sqlerrors']);
			$forums->admin->print_cells_row(array($DB->error));
			$forums->admin->print_table_footer();
			$forums->admin->print_cp_footer();
		}
		if (preg_match("/^INSERT|UPDATE|DELETE|ALTER/i", trim($sql))) {
			$forums->admin->columns[] = array("&nbsp;" , "100%");
			$forums->admin->print_table_start($forums->lang['sqlquerydone']);
			$forums->lang['queryalreadyrun'] = sprintf($forums->lang['queryalreadyrun'], $sql);
			$forums->admin->print_cells_row(array($forums->lang['queryalreadyrun']));
			$forums->admin->print_table_footer();
			$forums->admin->print_cp_footer();
		} else if (preg_match("/^SELECT/i", $sql)) {
			if (! preg_match("/LIMIT[ 0-9,]+$/i", $sql)) {
				$rows_returned = $DB->num_rows();
				if ($rows_returned > $limit) {
					$links = $forums->func->build_pagelinks(array('totalpages'  => $rows_returned,
														   'perpage'    => $limit,
														   'curpage'  => $start,
														   'pagelink'    => "mysql.php?{$forums->sessionurl}do=runsql&amp;query=".urlencode($sql),
														)
												 );
					$sql .= " LIMIT $start, $limit";
					$DB->query($sql);
				}
			}
		}
		$fields = $DB->get_result_fields();
		$cnt = count($fields);
		for($i = 0; $i < $cnt; $i++) {
			$forums->admin->columns[] = array($fields[$i]->name , "");
		}
		$forums->admin->print_table_start($forums->lang['result'].": ".$tbl_title);
		if ($links != "") {
			$forums->admin->print_cells_single_row($links, 'left', 'tdrow2');
		}
		while($r = $DB->fetch_array()) {
			$rows = array();
			for($i = 0; $i < $cnt; $i++) {
				if ($this_query == 1) {
					if (strlen($r[ $fields[$i]->name ]) > 200) {
						$r[ $fields[$i]->name ] = $forums->func->fetch_trimmed_title($r[ $fields[$i]->name ], 200);
					}
				}
				$rows[] = $r[ $fields[$i]->name ] ? wordwrap($forums->func->htmlspecialchars_uni(nl2br($r[ $fields[$i]->name ])) , 50, "<br />", 1) : "&nbsp;";
			}
			$forums->admin->print_cells_row($rows);
		}
		$forums->admin->print_table_footer();
		$forums->admin->print_cp_footer();
	}

	function sqltool()
	{
		global $forums, $DB, $_INPUT;
		$tables = array();
		$tables = $_INPUT['table'];
 		if (count($tables) < 1) {
 			$forums->admin->print_cp_error($forums->lang['requireselecttables']);
 		}
		if (strtoupper($_INPUT['tool']) == 'DROP' || strtoupper($_INPUT['tool']) == 'CREATE' || strtoupper($_INPUT['tool']) == 'FLUSH') {
			$forums->admin->print_cp_error($forums->lang['sqlerrors']);
		}
		$pagetitle  = "MySQL ".$this->true_version." ".$forums->lang['sqltools'];
		$detail = $forums->lang['managemysqldesc'];
		$forums->admin->print_cp_header($pagetitle, $detail);
		foreach($tables AS $table) {
			$DB->query(strtoupper($_INPUT['tool'])." TABLE $table");
			$fields = $DB->get_result_fields();
			$data = $DB->fetch_array();
			$cnt = count($fields);
			for($i = 0; $i < $cnt; $i++) {
				$forums->admin->columns[] = array($fields[$i]->name , "");
			}
			$forums->admin->print_table_start($forums->lang['result'].": ".$_INPUT['tool']." ".$table);
			$rows = array();
			for($i = 0; $i < $cnt; $i++) {
				$rows[] = $data[ $fields[$i]->name ];
			}
			$forums->admin->print_cells_row($rows);
			$forums->admin->print_table_footer();
		}
		$forums->admin->print_cp_footer();
	}

	function sqlmain()
	{
		global $forums, $DB;
		$form_array = array();
		if ($this->mysql_version < 32322) {
			$extra = "<br /><strong>".$forums->lang['mysqlversionold']."</strong>";
		}
		$pagetitle  = "MySQL ".$this->true_version." ".$forums->lang['sqltools'];
		$detail = $forums->lang['managemysqldesc'].$extra;
		$forums->admin->print_cp_header($pagetitle, $detail);
		$idx_size = 0;
		$tbl_size = 0;
		$forums->admin->print_form_header(array(1 => array('do' , 'dotool'),) , "mutliact"    );
		if ($this->mysql_version >= 32303) {
			$forums->admin->columns[] = array($forums->lang['sqltables'], "20%");
			$forums->admin->columns[] = array($forums->lang['sqlrows'], "10%");
			$forums->admin->columns[] = array($forums->lang['sqlsize'], "20%");
			$forums->admin->columns[] = array($forums->lang['sqlindexsize'], "20%");
			$forums->admin->columns[] = array($forums->lang['export'], "10%");
			$forums->admin->columns[] = array('<input name="allbox" type="checkbox" value="'.$forums->lang['selectall'].'" onClick="CheckAll(document.mutliact);" />'     , "10%");
			$forums->admin->print_table_start($forums->lang['managesqltables']);
			$DB->query("SHOW TABLE STATUS FROM `".$DB->database."`");
			while ($r = $DB->fetch_array()) {
				if (! preg_match("/^".TABLE_PREFIX."/", $r['Name'])) {
					continue;
				}
				$idx_size += $r['Index_length'];
				$tbl_size += $r['Data_length'];
				$iBit = ($r['Index_length'] > 0) ? 1 : 0;
				$tBit = ($r['Data_length'] > 0)  ? 1 : 0;
				$idx = $this->gen_size($r['Index_length'], 3, $iBit);
				$tbl = $this->gen_size($r['Data_length'] , 3, $tBit);
				$forums->admin->print_cells_row(array("<strong><span style='font-size:12px'><a href='mysql.php?{$forums->sessionurl}do=runsql&amp;query=".urlencode("SELECT * FROM {$r['Name']}")."'>{$r['Name']}</a></span></strong>",
														  "<center>{$r['Rows']}</center>",
														  "<div align='right'>{$tbl[0]} {$tbl[1]}</div>",
														  "<div align='right'>{$idx[0]} {$idx[1]}</div>",
														  "<center><a href='mysql.php?{$forums->sessionurl}do=export_tbl&amp;tbl={$r['Name']}&amp;createtable=1&amp;droptable=1'>".$forums->lang['export']."</a></center>",
														  "<center><input name='table[]' value='{$r['Name']}' type='checkbox' /></center>",
												)     );
			}
			$total = $idx_size + $tbl_size;
			$iBit = ($idx_size > 0) ? 1 : 0;
			$tBit = ($tbl_size > 0)  ? 1 : 0;
			$oBit = ($total    > 0)  ? 1 : 0;
			$idx = $this->gen_size($idx_size , 3, $iBit);
			$tbl = $this->gen_size($tbl_size , 3, $tBit);
			$tot = $this->gen_size($total    , 3, $oBit);
			$forums->admin->print_cells_row(array ("&nbsp;",
													  "&nbsp;",
													  "<div align='right'><strong>{$tbl[0]} {$tbl[1]}</strong></div>",
													  "<div align='right'><strong>{$idx[0]} {$idx[1]}</strong></div>",
													  array("<div align='right'>".$forums->lang['total']." (<strong>{$tot[0]} {$tot[1]}</strong>)</div>", 2),
											)      );
		} else {
			$forums->admin->columns[] = array($forums->lang['sqltables'], "60%");
			$forums->admin->columns[] = array($forums->lang['sqlrows'], "30%");
			$forums->admin->columns[] = array('<input name="allbox" type="checkbox" value="'.$forums->lang['selectall'].'" onClick="CheckAll(document.mutliact);" />'     , "10%");
			$forums->admin->print_table_start($forums->lang['managesqltables']);
			$tables = $DB->get_table_names();
			foreach($tables AS $tbl) {
				if (! preg_match("/^".TABLE_PREFIX."/", $tbl)) {
					continue;
				}
				$cnt = $DB->query_first("SELECT COUNT(*) AS Rows FROM $tbl");
				$forums->admin->print_cells_row(array("<strong>$tbl</strong>",
														  "<center>{$cnt['Rows']}</center>",
														  "<center><input name='table[]' value='$tbl' type='checkbox' /></center>",
												)     );
			}
		}
		if ($this->mysql_version < 32322) {
			$forums->admin->print_cells_single_row("<select class='button' name='tool'>
													<option value='optimize'>".$forums->lang['optimizesqltables']."</option>
												  </select>
												 <input type='submit' value='".$forums->lang['ok']."' class='button' />", "center", "tdrow2");
		} else {
			$forums->admin->print_cells_single_row("<select class='button' name='tool'>
													<option value='optimize'>".$forums->lang['optimizesqltables']."</option>
													<option value='repair'>".$forums->lang['repairsqltables']."</option>
													<option value='check'>".$forums->lang['checksqltables']."</option>
													<option value='analyze'>".$forums->lang['analyzesqltables']."</option>
												  </select>
												 <input type='submit' value='".$forums->lang['ok']."' class='button' />", "center", "tdrow2");
		}
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_form_header(array(1 => array('do' , 'runsql'),),'runsqlform');
		$forums->admin->columns[] = array($forums->lang['sqltables'], "30%");
		$forums->admin->columns[] = array($forums->lang['sqlrows'], "70%");
		$forums->admin->print_table_start($forums->lang['doquery']);
		$forums->admin->print_cells_row(array($forums->lang['domanualquery'], $forums->admin->print_textarea_row("query", "")));
		$forums->admin->print_form_submit($forums->lang['doquery']);
		$forums->admin->print_table_footer();
		$forums->admin->print_form_end();
		$forums->admin->print_cp_footer();
	}

	function gen_size($val, $li, $sepa)
	{
		$sep     = pow(10, $sepa);
		$li      = pow(10, $li);
		$retval  = $val;
		$unit    = 'Bytes';
		if ($val >= $li * 1000000) {
			$val = round($val / (1073741824/$sep)) / $sep;
			$unit  = 'GB';
		} else if ($val >= $li*1000) {
			$val = round($val / (1048576/$sep)) / $sep;
			$unit  = 'MB';
		} else if ($val >= $li) {
			$val = round($val / (1024/$sep)) / $sep;
			$unit  = 'KB';
		}
		if ($unit != 'Bytes') {
			$retval = number_format($val, $sepa, '.', ',');
		} else {
			$retval = number_format($val, 0, '.', ',');
		}
		return array($retval, $unit);
    }

    function gzip_four_chars($val)
	{
		for ($i = 0; $i < 4; $i ++) {
			$return .= chr($val % 256);
			$val     = floor($val / 256);
		}
		return $return;
	}

	function export_header($dumptype='Standard Backup', $vol='NULL', $step='1', $finish='1', $dbstrlen='0')
	{
		global $forums, $bboptions, $_INPUT;
		return "# key: ".base64_encode("".date("Y-m-d H:i:s").",$dumptype,$vol,$step,$finish, $dbstrlen, ".$this->md5_check."_")."\r\n\r\n".
				"# MolyX SQL DUMP\r\n".
				"# version 1.0.0\r\n".
				"# DUMP Siteurl: ".$bboptions['bburl']."\r\n".
				"# DUMP Type: ".$dumptype."\r\n".
				"# Current Volume: ".$vol."\r\n".
				"# ALL DONE: ".($finish ? 'TRUE' : 'FALSE')."\r\n".
				"# DUMP TIME: ".$forums->func->get_time(TIMENOW)."\r\n\r\n".
				"# THIS FILE BASED ON MOLYX\r\n".
				"# If You Any Questions, Please Visit: www.molyx.com\r\n".
				"# --------------------------------------------------------\r\n".
				"# start export\r\n\r\n\r\n"
				;
	}
}

$output = new mysql();
$output->show();

?>