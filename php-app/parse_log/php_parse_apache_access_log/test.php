<?php
include("class.access_log_parser.php"); //include the class file

$apache_log_parser = new apache_log_parser(); // Create an apache log parser 

if ($apache_log_parser->open_log_file('/var/log/httpd/access_log')){ // Make sure it opens the log file 
  while ($line = $apache_log_parser->get_line()){ // while it can get a line 
  	$loopcount++;
  	$parsed_line = $apache_log_parser->format_line($line); // format the line 
    $ip = mysql_real_escape_string($parsed_line['ip']);
  	$identity = $parsed_line['identity'];
  	$log_user = $parsed_line['user'];
  	$log_date = $parsed_line['date'];
  	$log_time = $parsed_line['time'];
  	$timezone = $parsed_line['timezone'];
  	$method = mysql_real_escape_string($parsed_line['method']);
  	$path = mysql_real_escape_string($parsed_line['path']);
  	$protocol = mysql_real_escape_string($parsed_line['protocol']);
  	$status = mysql_real_escape_string($parsed_line['status']);
  	$log_bytes = mysql_real_escape_string($parsed_line['bytes']);
  	$referer = mysql_real_escape_string($parsed_line['referer']);
  	$agent = mysql_real_escape_string($parsed_line['agent']);
  	$clean_line = mysql_real_escape_string($line);
    
    //do stuff with the data here
    echo "<br /> $loopcount: $ip $log_date $log_time $method $path $protocol $status";
    
  }
                
}

$apache_log_parser->close_log_file(); // close the log file 
//$apache_log_parser->clear_log_file('/var/log/apache2/access.log'); // empty the file (optional)
?>
