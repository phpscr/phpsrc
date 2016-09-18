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
		if ( ! $this->connection_id ) die("Can not connect MySQL Server");
		if ( ! @mysql_select_db($this->database, $this->connection_id) ) die("Can not connect MySQL Database");
		if ($this->dbcharset) {
			@mysql_query("SET NAMES ".$this->dbcharset);
		}
		return TRUE;
	}

    function query($query_id, $query_type='mysql_query') 
	{
        $this->query_id = $query_type($query_id, $this->connection_id);
		$this->lastquery = $query_id;
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
		$result = mysql_list_tables($this->database);
		$num_tables = @mysql_numrows($result);
		for ($i = 0; $i < $num_tables; $i++) {
			$tables[] = mysql_tablename($result, $i);
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
}

?>