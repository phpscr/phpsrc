<?php
$sym="";
if( $argc < 2 ){

    exit( " need argument!" ."\n");
    //var_dump($argc);
}else{
    //var_dump($argv);
  //  echo ($argv[1]);
    $sym=$argv[1]; 
}
echo $sym;


// get quote 
/*
http://download.finance.yahoo.com/d/[FILENAME]?s=[TICKER SYMBOL(S)]&f=[TAGS]&e=.csv
where
[FILENAME] - Name of the file to save to (usually "quotes.csv")
[TAGS] - The stock information to download (usually "sl1d1t1c1ohgv")
[TICKER SYMBOL(S)] - Ticker symbol of a company (separate multiple tickers with a comma)

//$handle = @fopen("http://download.finance.yahoo.com/d/quotes.csv?s={$_GET["symbol"]}&f=e1l1", "r"); 
*/
$key={'ri'};
$handle = @fopen("http://download.finance.yahoo.com/d/quotes.csv?s='$sym'&f=d1sl1t1c1ohgv", "r"); 
var_dump($handle);
if ($handle !== FALSE) 
{ 
    $data = fgetcsv($handle); 
   // var_dump($data);
    foreach( $data as $key =>$value)
    {
        echo  $key."=>".$value."\n" ;
    }

    /*
       if ($data !== FALSE && $data[0] !== "N/A") 
      print($data[1]); 
      */
    fclose($handle); 
} 

?> 
