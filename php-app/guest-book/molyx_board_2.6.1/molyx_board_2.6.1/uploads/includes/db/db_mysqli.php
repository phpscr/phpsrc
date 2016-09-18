<?php
#**************************************************************************#
#   MolyX Board 2
#   ---------------------------
#   copyright � 2004 - 2006 MolyX Studios.  All Rights Reserved. http://www.molyx.com
#   MolyX Board is licensed software. This file may not be redistributed in whole or in
#   part. By installing and/or using this software you hereby agree to all of the terms,
#   conditions, and restrictions set by MolyX. If you do not accept these Terms and
#   Conditions, please do not use this software.
#   MolyX Studios may revise these Terms and Conditions at any time without notice.
#   Please visit www.molyx.com periodically to review the Terms and Conditions,
#   or contact the author for clarification.
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
		$this->connection_id = @mysqli_connect($this->server, $this->user, $this->password, $this->database);
		if ( ! $this->connection_id ) $this->halt("Can not connect mysqli Server or DataBase");
		if ($this->dbcharset) {
			if (function_exists('mysqli_set_charset')) {
				mysqli_set_charset($this->connection_id, $this->dbcharset);
			} else {
				@mysqli_query($this->connection_id, "SET NAMES ".$this->dbcharset);
			}
		}
		return TRUE;
	}

	function query($query_id, $query_type='mysqli_query')
	{
		$this->query_id = $query_type($this->connection_id, $query_id);
		$this->lastquery = $query_id;
		if (! $this->query_id ) {
			$this->halt("Query Errors:\n$query_id");
		}
		$this->query_count++;
		return $this->query_id;
	}

	function query_unbuffered($query_id = "")
	{
		return $this->query($query_id, 'mysqli_query');
	}

	function fetch_array($query_id = "")
	{
		if ($query_id == "") $query_id = $this->query_id;
		$this->record_row = @mysqli_fetch_array($query_id, MYSQLI_ASSOC);
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
		return @mysqli_affected_rows($this->connection_id);
	}

	function num_rows($query_id="") {
		if ($query_id == "") $query_id = $this->query_id;
		return @mysqli_num_rows($query_id);
	}

	function geterrno() {
		$this->errno = @mysqli_errno($this->connection_id);
		return $this->errno;
	}

	function insert_id() {
		return @mysqli_insert_id($this->connection_id);
	}

	function query_count() {
		return $this->query_count;
	}

	function escape_string($str)
	{
		return mysqli_real_escape_string($this->connection_id, $str);
	}

	function free_result($query_id="")
	{
		if ($query_id == "") $query_id = $this->query_id;
		@mysqli_free_result($query_id);
	}

	function close_db()
	{
		if ( $this->connection_id ) return @mysqli_close( $this->connection_id );
	}

	function get_table_names()
	{
		$result = mysqli_query($this->connection_id, "SHOW TABLES FROM ".$this->database);
		while($row = mysqli_fetch_array($result, MYSQLI_NUM)) {
			$tables[] = $row[0];
		}
		mysqli_free_result($result);
		return $tables;
	}

	function get_result_fields($query_id="")
	{
		if ($query_id == "") $query_id = $this->query_id;
		while ($field = mysqli_fetch_field($query_id)) {
			$fields[] = $field;
		}
		return $fields;
	}

	function halt($the_error="")
	{
		global $forums, $bboptions, $bbuserinfo, $technicalemail;
		$forums->lang = $forums->func->load_lang($forums->lang, 'db' );
		if ($this->return_die == 1) {
			$this->failed   = 1;
			return;
		}
		if ($bbuserinfo['usergroupid']==4) {
			$this->error = @mysqli_error($this->connection_id);
		} else {
			$the_error = '';
		}
		$message  = $forums->lang['db_errors'].": \n\n";
		$message  .= $the_error."\n\n";
		$message .= $forums->lang['mysqli_errors'].': ' . $this->error . "\n\n";
		echo "<html><head><title>".$bboptions['bbtitle']." ".$forums->lang['mysqli_errors']."</title>";
		echo "<style type=\"text/css\"><!--.error { font: 11px tahoma, verdana, arial, sans-serif, simsun; }--></style></head>\r\n";
		echo "<body>\r\n";
		echo "<blockquote><p class=\"error\">&nbsp;</p><p class=\"error\"><strong>".$bboptions['bbtitle']." ".$forums->lang['db_found_errors']."</strong><br />\r\n";
		$db_sendmail = sprintf( $forums->lang['db_sendmail'], $technicalemail );
		echo $db_sendmail."</p>";
		echo "<p class=\"error\">".$forums->lang['db_apologies']."</p>";
		if ($bbuserinfo['usergroupid']==4) {
			echo "<textarea class=\"error\" rows=\"15\" cols=\"100\" wrap=\"on\" >" . $forums->func->htmlspecialchars_uni($message) . "</textarea></blockquote>";
		}
		echo "\r\n\r\n</body></html>";
		die("");
	}
}

?>