<?php
$fp = fsockopen("10.55.37.64", 8810, $errno, $errstr, 30);
if (!$fp) {
        echo "$errstr ($errno)<br />\n";
} else {
        $out = "GET / HTTP/1.1\r\n";
            $out .= "Host: 10.55.37.64\r\n";
                $out .= "Connection: Close\r\n\r\n";
                    fwrite($fp, $out);
                        while (!feof($fp)) {
                                    echo fgets($fp, 128);
                                        }
                            fclose($fp);
}
?>
