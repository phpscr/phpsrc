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
class functions_cron
{
	var $root_path = './';
	var $timenow  = 0;
	var $datenow  = array();
	var $minute = 0;
	var $hour = 0;
	var $day = 0;
	var $month  = 0;
	var $year = 0;

	function functions_cron()
	{
		global $forums;
		$this->root_path = str_replace('\\', '/', realpath(ROOT_PATH)) . '/';
		$this->root_path = str_replace('//', '/', $this->root_path);
		$this->timenow = time();
		$this->datenow['minute'] = $forums->func->get_time($this->timenow, 'i');
		$this->datenow['hour'] = $forums->func->get_time($this->timenow, 'H');
		$this->datenow['weekday'] = $forums->func->get_time($this->timenow, 'w');
		$this->datenow['monthday'] = $forums->func->get_time($this->timenow, 'd');
		$this->datenow['month'] = $forums->func->get_time($this->timenow, 'm');
		$this->datenow['year'] = $forums->func->get_time($this->timenow, 'Y');
	}

	function docron()
	{
		global $forums, $DB;
		$result = $DB->query( "SELECT * FROM ".TABLE_PREFIX."cron WHERE enabled = 1 AND nextrun <= ".$this->timenow." ORDER BY nextrun ASC" );
		while ($cron = $DB->fetch_array($result)) {
			$nextrun = $this->next_run($cron);
			$DB->query_unbuffered( "UPDATE ".TABLE_PREFIX."cron SET nextrun=".$nextrun." WHERE cronid =".$cron['cronid']);
			if ( @file_exists( $this->root_path.'includes/cron/'.$cron['filename'] ) ) {
				include_once(ROOT_PATH.'includes/cron/'.$cron['filename']);
				$class_name = 'cron_'.substr($cron['filename'], 0, strrpos($cron['filename'], '.'));
				if (class_exists($class_name)) {
					$runcron = new $class_name();
					$runcron->register_class( $this );
					$runcron->pass_cron($cron);
					$runcron->docron();
				}
			}
		}
		$this->save_next_run();
	}
	
	function next_run($cron=array())
	{
		global $forums;
		$day_set = 1;
		$min_set = 1;
		$day_increment = 0;
		$this->day = $this->datenow['weekday'];
		$this->minute = $this->datenow['minute'];
		$this->hour = $this->datenow['hour'];
		$this->month  = $this->datenow['month'];
		$this->year = $this->datenow['year'];
		if ( $cron['weekday'] == -1 AND $cron['monthday'] == -1 ) {
			$day_set = 0;
		}
		if ( $cron['minute'] == -1 ) {
			$min_set = 0;
		}
		if ( $cron['weekday'] == -1 ) {
			if ( $cron['monthday'] != -1 ) {
				$this->day = $cron['monthday'];
				$day_increment = 'month';
			} else {
				$this->day = $this->datenow['monthday'];
				$day_increment = '';
			}
		} else {
			$this->day = $this->datenow['monthday'] + ( $cron['weekday'] - $this->datenow['weekday'] );
			$day_increment = 'week';
		}
		if ( $this->day < $this->datenow['monthday'] ) {
			switch ( $day_increment ) {
				case 'month':
					$this->bbmonth();
					break;
				case 'week':
					$this->bbday(7);
					break;
				default:
					$this->bbday();
					break;
			}
		}
		if ( $cron['hour'] == -1) {
			$this->hour = $this->datenow['hour'];
		} else {
			if ( ! $day_set AND ! $min_set ) {
				$this->bbhour( $cron['hour'] );
			} else {
				$this->hour = $cron['hour'];
			}
		}
		if ( $cron['minute'] == -1 ) {
			$this->bbminute();
		} else {
			if ( $cron['hour'] == -1 AND ! $day_set ) {
				$this->bbminute($cron['minute']);
			} else {
				$this->minute = $cron['minute'];
			}
		}
		if ( $this->hour <= $this->datenow['hour'] AND $this->day == $this->datenow['monthday'] ) {
			if ( $cron['hour'] == -1 ) {
				if ( $this->hour == $this->datenow['hour'] AND $this->minute <= $this->datenow['min'] ) {
 					$this->bbhour();
 				}
 			} else {
 				if ( ! $day_set AND ! $min_set ) {
 					$this->bbhour($cron['hour'] );
 				} else if ( ! $day_set ) {
 					$this->bbday();
 				} else {
 					switch ( $day_increment ) {
						case 'month':
							$this->bbmonth();
							break;
						case 'week':
							$this->bbday(7);
							break;
						default:
							$this->bbday();
							break;
					}
 				}
 			}
		}
		return $forums->func->mk_time( $this->hour, $this->minute, 0, $this->month, $this->day, $this->year );
	}
	
	function save_next_run()
	{
		global $forums, $DB;
		$cron = $DB->query_first( "SELECT nextrun FROM ".TABLE_PREFIX."cron WHERE enabled = 1 ORDER BY nextrun ASC LIMIT 0, 1" );
		if ( ! $cron['nextrun'] ) {
			$cron['nextrun'] = $this->timenow + 3600;
		}
		$forums->func->update_cache( array( 'name' => 'cron', 'value' => $cron['nextrun'], 'array' => 0 ) );
	}

	function cronlog($cron,$desc)
	{
		global $forums;
		if ( !$cron['loglevel'] ) return;
		$save = array( 'title' => $cron['title'], 'dateline'  => $this->timenow, 'description'  => $desc );
		$forums->func->fetch_query_sql( $save, 'cronlog' );
	}

	function bbmonth()
	{
		if ($this->datenow['month'] == 12) {
			$this->month = 1;
			$this->year++;
		} else {
			$this->month++;
		}
	}

	function bbday($days=1)
	{
		global $forums;
		if ( $this->datenow['monthday'] >= ( $forums->func->get_time($this->timenow, 't') - $days ) ) {
			$this->day = ($this->datenow['monthday'] + $days) - $forums->func->get_time($this->timenow, 't');
			$this->bbmonth();
		} else {
			$this->day += $days;
		}
	}

	function bbhour($hour=1)
	{
		if ($this->datenow['hour'] >= (24 - $hour ) ) {
			$this->hour = ($this->datenow['hour'] + $hour) - 24;
			$this->bbday();
		} else {
			$this->hour += $hour;
		}
	}

	function bbminute($minutes=1)
	{
		if ( $this->datenow['minute'] >= (60 - $minutes) ) {
			$this->minute = ( $this->datenow['minute'] + $minutes ) - 60;
			$this->bbhour();
		} else {
			$this->minute += $minutes;
		}
	}
}

?>