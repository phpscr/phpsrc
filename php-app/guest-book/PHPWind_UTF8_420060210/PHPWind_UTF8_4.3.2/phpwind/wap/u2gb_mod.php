<?php
!function_exists('readover') && exit('Forbidden');
function U2GB($source,$tar='GB2312'){

	if(ord(substr($source,0,1)) <= 0xE0){
		return $source;
	}
	$unicode = array();
	$tmp	 = openfile(R_P."require/gb-unicode.table");
	foreach($tmp as $key => $value){
		$unicode[hexdec(substr($value,7,6))]=substr($value,0,6);
	}
	$ret = "";
	$len = strlen($source);
	$i = 0;
	while($i < $len) {
		$c = ord( substr( $source, $i++, 1 ) );
		switch($c >> 4){
			case 0: case 1: case 2: case 3: case 4: case 5: case 6: case 7:
				$ret .= substr( $source, $i-1, 1 );
			break;
			case 12: case 13:
				$char2 = ord( substr( $source, $i++, 1 ) );
				$char3 = $unicode[(($c & 0x1F) << 6) | ($char2 & 0x3F)];

				if ($tar=="GB2312"){
					$ret .= hex2bin( dechex(  $char3 + 0x8080 ) );
				} elseif ($tar=="BIG5"){
					$ret .= hex2bin( $char3 );
				}
			break;
			case 14:
				$char2 = ord( substr( $source, $i++, 1 ) );
				$char3 = ord( substr( $source, $i++, 1 ) );
				$char4 = $unicode[(($c & 0x0F) << 12) | (($char2 & 0x3F) << 6) | (($char3 & 0x3F) << 0)];

				if ($tar=="GB2312"){
					$ret .= hex2bin( dechex ( $char4 + 0x8080 ) );
				} elseif ($tar=="BIG5"){
					$ret .= hex2bin( $char4 );
				}
			break;
		}
	}
	return $ret;
}
function hex2bin( $hexdata )
{
	for ( $i=0; $i<strlen($hexdata); $i+=2 ){
		$bindata.=chr(hexdec(substr($hexdata,$i,2)));
	}
	return $bindata;
}
?>