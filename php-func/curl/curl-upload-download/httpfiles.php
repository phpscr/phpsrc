<?php
/*
 * Upload, download, read and transfer files over HTTP with PHP CURL 
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author 	  Peter Varga <admin@vargapeter.com>
 * @copyright Peter Varga 2012
 * @license	  GNU General Public License 3.0
 * @version   1.3
 *  
 */
 

class Httpfiles
{

	var $useragent="Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)";
	var $ctimeout=120;
	var $timeout=120;
	
	function check_curl(){
		if  (in_array  ('curl', get_loaded_extensions())) return true;
		return false;		
	}

	// download remote file to local 
    function get_file($remote,$local){
    
	   	$fp = fopen ($local, 'w+');
	   	$ch = curl_init($remote);
	   	curl_setopt($ch, CURLOPT_RETURNTRANSFER ,0);     
	   	curl_setopt($ch, CURLOPT_HEADER,0);   
	   	curl_setopt($ch, CURLOPT_FILE, $fp);
	   	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_ENCODING,"");       
		curl_setopt($ch, CURLOPT_USERAGENT,$this->useragent);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);    
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,$this->ctimeout);      
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);    
		curl_setopt($ch, CURLOPT_MAXREDIRS,10);
		curl_exec( $ch );
		fclose($fp);
		
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);    
		curl_close($ch);
		
		if ($status == 200 && is_file($local)) {
		 	return true;
		} else {
    		return false;
    	} 	    
	    
    }
    
    // read remote file
    function read_file($remote){
	    $ch = curl_init($remote);
	   	curl_setopt($ch, CURLOPT_RETURNTRANSFER ,1);     
	   	curl_setopt($ch, CURLOPT_HEADER,0);   
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION,true);     
		curl_setopt($ch, CURLOPT_ENCODING,"");       
		curl_setopt($ch, CURLOPT_USERAGENT,$this->useragent);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);    
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->ctimeout);      
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);    
		curl_setopt($ch, CURLOPT_MAXREDIRS,10);
	
		$content = curl_exec( $ch );
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);    
		curl_close($ch);
		
		if ($status == 200) {
		 	return $content;
		} else {
    		return false;
    	} 	    
	    
    }
    
    // send local file to remote host (upload)
    function send_file($fname, $handler, $field){
    
	    $ch = curl_init($handler);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_VERBOSE, 0);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);
	    curl_setopt($ch, CURLOPT_POST, true);
	    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->ctimeout);      
		curl_setopt($ch, CURLOPT_TIMEOUT,$this->timeout);    

	    $post = array($field=>"@$fname");
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $post); 

	    $response = curl_exec($ch);
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);    
		curl_close($ch);
		
		if ($status == 200) {
		 	return $response;
		} else {
    		return false;
    	} 
    }
    
    // transfer remote file to remote host (upload) - local cache on disk
    function transfer_file($remote,$handler,$field,$filename){
    	$content=$this->read_file($remote);
	    if($content!=false){
	    
		    $file = sys_get_temp_dir();
		    $file .=$filename;

		    file_put_contents($file, $content); 

		    $status=$this->send_file($file,$handler,$field);
		    
		    unlink($file);
		    
		    return $status;		    
	    }
    }
    
}