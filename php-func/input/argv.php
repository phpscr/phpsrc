<?php
// Process args
#var_dump($argv);


foreach($argv as $arg) {
        
	if ($arg == '--help') {
                print $help;
                exit;
        }
        if ($arg == '--forecast' || $arg == '-f') {
                $forecast_only = true;
        }
        if ($arg == '--conditions' || $arg == '-c') {
                $conditions_only = true;
        }
        if ($arg == '-C') {
                $units = 'c';
        }
        if (preg_match('/\d{5}/', $arg)) {
                $zip = $arg;
        }
	if ($arg == 'a') {
		print "hello,this is a !\n";	 
	}
	if ($arg == 'exit') {
		print "exit !!!! \n";
		exit;
	}
}
?>
