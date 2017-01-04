httpcurl-php
============

Http File transfer with PHP CURL


Transfer remote files with your php script through http.

Features
------------

- Download remote file 
- Read remote file content (ex: html, xml, etc.)
- Upload file to remote host (emulate form upload)
- Transfer remote file to remote host 


Basic usage
------------

Ini 

	include "httpfiles.php";
	$httpf= new Httpfiles();

Download remote file 

	$httpf->get_file("http://www.host.com/example.jpg","./downloads/example.jpg");


Read remote file

	$content= $httpf->read_file("http://www.host.com/index.html")
	print_r($content);


Upload file to remote host

	$httpf->send_file("./dowmloads/example.jpg","http://www.host.com/upload.php","userfile");

	// userfile is the 'field' of the upload file (like a file input in html form: <input type="file" name="userfile"/>)



Transfer remote file to remote host

	$http->transfer_file("http://www.host1.com/example.jpg","http://www.host2.com/upload.php","userfile","example.jpg");
