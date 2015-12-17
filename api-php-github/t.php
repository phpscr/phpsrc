# Yahoo Finance API

#Simple library for getting stock information using the YQL API on yahoo.finance tables

## Download

#    $ git clone git://github.com/aygee/yahoo-finance-api

## Setup
<?php

    require 'lib/YahooFinance/YahooFinance.php';

    $yf = new YahooFinance;

## Usage

  # $historicaldata = $yf->getHistoricalData('ASX', '2015-01-01', '2015-12-08');
   #$quote          = $yf->getQuotes('ASX');	   		// single quote
   $quote          = $yf->getQuotes('wb');	   		// single quote
  # $quotes	   = $yf->getQuotes(array('ASX', 'WOW'));	// multiple quotes

#	var_dump($quote);

#print_r($quote);
$data=json_decode($quote,true);
if ( is_array( $data ) ) {
	echo "data is ok! \n";
	#print_r( $data);
}else{
	echo "Is not array!\n";
}

#print_r( $data);
echo"\n";
print_r($data['query']['results']['quote']['symbol']);
echo"\n";
print_r($data['query']['results']['quote']['Ask']);
echo"\n";
print_r($data['query']['results']['quote']['Open']);
echo"\n";
/*
foreach($quote as $var){
#	echo "$var\n";
}
*/	

?>
