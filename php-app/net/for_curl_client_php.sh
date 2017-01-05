#!/bin/bash

mkdir -p wc_work
cd wc_work
for ((i=0;i<500;i++))
do

    echo $i
php ../curl_client.php 


done 
