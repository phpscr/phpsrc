<?php
!function_exists('readover') && exit('Forbidden');

function payto($code){
	global $imgpath,$stylepath,$db_bbsurl,$lang;
	require_once GetLang('bbscode');
	$tmp          = substr($code,strpos($code,'(seller)')+8);
	$seller       = str_replace(array('[email]','[/email]'),'',substr($tmp,0,strpos($tmp,'(/seller)')));
	$tmp          = substr($code,strpos($code,'(subject)')+9);
	$subject      = substr($tmp,0,strpos($tmp,'(/subject)'));
	$tmp          = substr($code,strpos($code,'(body)')+6);
	$body         = substr($tmp,0,strpos($tmp,'(/body)'));
	$tmp          = substr($code,strpos($code,'(price)')+7);
	$price        = substr($tmp,0,strpos($tmp,'(/price)'));
	$tmp          = substr($code,strpos($code,'(ordinary_fee)')+14);
	$ordinary_fee = substr($tmp,0,strpos($tmp,'(/ordinary_fee)'));
	$tmp          = substr($code,strpos($code,'(express_fee)')+13);
	$express_fee  = substr($tmp,0,strpos($tmp,'(/express_fee)'));
	$tmp          = substr($code,strpos($code,'(contact)')+9);
	$contact      = substr($tmp,0,strpos($tmp,'(/contact)'));
	$tmp          = substr($code,strpos($code,'(demo)')+6);
	$demo         = substr($tmp,0,strpos($tmp,'(/demo)'));
	$tmp          = substr($code,strpos($code,'(method)')+8);
	$method       = substr($tmp,0,strpos($tmp,'(/method)'));

	$body=str_replace('\"','"',$body);
	$str = '<br>';
	$seller       && $str .= "$lang[seller]$seller<br><br>";
	$subject      && $str .= "$lang[subject]$subject<br><br>";
	$body         && $str .= "$lang[body]$body<br><br>";
	$price        && $str .= "$lang[price]$price<br><br>";
	if(($ordinary_fee || $express_fee) && $method=='2'){
		$str .= $lang['postage'];
		$ordinary_fee && $str .= "$lang[ordinary_fee]$ordinary_fee&nbsp; ";
		$express_fee  && $str .= "$lang[express_fee]$express_fee";
		$str .= "<br><br>";
	}else{
		$str .= "$lang[postage_seller]<br><br>";
	}
	$contact      && $str .= "$lang[contact]$contact<br><br>";
	$demo         && $str .= "$lang[demo]$demo<br><br>";
	$body = substrs(str_replace('<br>',"\n",$body),100);
	if($method==1){
		$str .= "<a href='https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=".rawurlencode(str_replace('&#46;','.',$seller))."&item_name=".rawurlencode($subject)."&item_number=phpw*&amount=$price&no_shipping=0&no_note=1&currency_code=CNY&notify_url=http://www.phpwind.com/pay/payto.php?date=".$_SERVER['HTTP_HOST'].get_date(time(),'-YmdHis')."&bn=phpwind&charset=$db_charset' target='_blank'><img src='$imgpath/paypal.gif'></a>";
	}elseif($method==2){
		$str .= "<a href='https://www.alipay.com/payto:$seller?subject=".rawurlencode($subject)."&body=".rawurlencode($body)."&price=$price&ordinary_fee=$ordinary_fee&express_fee=$express_fee&partner=8868&readonly=true' target='_blank'><img src='$imgpath/alipay.gif'></a>";
	}elseif($method==3){
		$str.="<a href='https://www.99bill.com/paylink/intialPaylinkIndexForw.do?pay=".rawurlencode(str_replace('&#46;','.',$seller))."&dealAmount=$price' target='_blank'><img src='$imgpath/99bill.gif'></a>";
	}
	return $str;
}
?>