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
class db {

	var $connection_id = "";
	var $server = "localhost";
	var $user = "root";
	var $password = "";
	var $database = "";
	var $pconnect = 0;
	var $shutdown_queries = array();
	var $query_id = "";
	var $lastquery = '';
	var $query_count = 0;
	var $record_row = array();
	var $return_die = 0;
	var $failed = 0;
	var $halt = "";

	function connect()
	{
		if ($this->pconnect)
		$this->connection_id = @mysql_pconnect( $this->server, $this->user, $this->password );
		else
		$this->connection_id = @mysql_connect( $this->server, $this->user, $this->password );
		if ( ! $this->connection_id ) $this->halt("Can not connect MySQL Server");
		if ( ! @mysql_select_db($this->database, $this->connection_id) ) $this->halt("Can not connect MySQL Database");
		if ($this->dbcharset) {
			@mysql_query("SET NAMES ".$this->dbcharset);
		}
		return TRUE;
	}

	function query($query_id, $query_type='mysql_query')
	{
		$this->query_id = $query_type($query_id, $this->connection_id);
		$this->lastquery = $query_id;
		if (! $this->query_id ) {
			$this->halt("Query Errors:\n$query_id");
		}
		$this->query_count++;
		return $this->query_id;
	}

	function query_unbuffered($query_id = "")
	{
		return $this->query($query_id, 'mysql_unbuffered_query');
	}

	function fetch_array($query_id = "")
	{
		if ($query_id == "") $query_id = $this->query_id;
		$this->record_row = @mysql_fetch_array($query_id, MYSQL_ASSOC);
		return $this->record_row;
	}

	function query_first($query_id = "")
	{
		$query_id = $this->query($query_id);
		$returnarray = $this->fetch_array($query_id);
		$this->free_result($query_id);
		return $returnarray;
	}

	function shutdown_query($query_id = "")
	{
		if ( ! USE_SHUTDOWN )	return $this->query($query_id);
		else	$this->shutdown_queries[] = $query_id;
	}

	function affected_rows() {
		return @mysql_affected_rows($this->connection_id);
	}

	function num_rows($query_id="") {
		if ($query_id == "") $query_id = $this->query_id;
		return @mysql_num_rows($query_id);
	}

	function geterrno() {
		$this->errno = @mysql_errno($this->connection_id);
		return $this->errno;
	}

	function insert_id() {
		return @mysql_insert_id($this->connection_id);
	}

	function query_count() {
		return $this->query_count;
	}

	function escape_string($str)
	{
		if (function_exists('mysql_real_escape_string')) {
			return mysql_real_escape_string($str, $this->connection_id);
		} else {
			return mysql_escape_string($str);
		}
	}

	function free_result($query_id="")
	{
		if ($query_id == "") $query_id = $this->query_id;
		@mysql_free_result($query_id);
	}

	function close_db()
	{
		if ( $this->connection_id ) return @mysql_close( $this->connection_id );
	}

	function get_table_names()
	{
		$result = mysql_query("SHOW TABLES FROM ".$this->database, $this->connection_id);
		while($row = mysql_fetch_array($result, MYSQL_NUM)) {
			$tables[] = $row[0];
		}
		mysql_free_result($result);
		return $tables;
	}

	function get_result_fields($query_id="")
	{
		if ($query_id == "") $query_id = $this->query_id;
		while ($field = mysql_fetch_field($query_id)) {
			$fields[] = $field;
		}
		return $fields;
	}

	function halt($the_error="")
	{
		global $forums, $bboptions, $bbuserinfo, $technicalemail;
		$forums->func->check_lang();
		@require (ROOT_PATH."lang/{$bboptions['language']}/db.php");
		if ($this->return_die == 1) {
			$this->failed   = 1;
			return;
		}
		if ($bbuserinfo['usergroupid']==4) {
			$this->error = @mysql_error($this->connection_id);
		} else {
			$the_error = '';
		}
		$message  = $lang['db_errors'].": \r\n";
		$message  .= $the_error."\r\n";
		$message .= $lang['mysql_errors'].': ' . $this->error . "\r\n";
		echo "<html><head><title>".$bboptions['bbtitle']." ".$lang['mysql_errors']."</title>";
		echo "<style type=\"text/css\"><!--.error { font: 11px tahoma, verdana, arial, sans-serif, simsun; }--></style></head>\r\n";
		echo "<body>\r\n";
		echo "<blockquote><p class=\"error\">&nbsp;</p><p class=\"error\"><strong>".$bboptions['bbtitle']." ".$lang['db_found_errors']."</strong><br />\r\n";
		$db_sendmail = sprintf( $lang['db_sendmail'], $technicalemail );
		echo $db_sendmail."</p>\r\n";
		echo "<p class=\"error\">".$lang['db_apologies']."</p>\r\n";
		if ($bbuserinfo['usergroupid']==4) {
			echo "<textarea class=\"error\" rows=\"15\" cols=\"100\" wrap=\"on\" >" . $forums->func->htmlspecialchars_uni($message) . "</textarea>\r\n";
		}
		echo "</blockquote>\r\n</body></html>";
		die("");
	}
}

?>