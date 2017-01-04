<?php
!function_exists('adminmsg') && exit('Forbidden');
//===========bank===========//
 if($hackset=='bank'){
	 require_once(R_P.'hack/bankset.php');
}
//===========bank===========//

//===========colony===========//
 if($hackset=='colony'){
	 require_once(R_P.'hack/colonyset.php');
}
//===========colony===========//

//===========advert===========//
 if($hackset=='advert'){
	 require_once(R_P.'hack/advert.php');
}
//===========advert===========//

//===========new===========//
 if($hackset=='new'){
	 require_once(R_P.'hack/new.php');
}
//===========new===========//

//===========medal===========//
 if($hackset=='medal'){
	 require_once(R_P.'hack/medalset.php');
}
//===========medal===========//

//===========toolcenter===========//
 if($hackset=='toolcenter'){
	 require_once(R_P.'hack/toolsetting.php');
}
//===========toolcenter===========//

//===========blog===========//
 if($hackset=='blog'){
	 require_once(R_P.'hack/blogset.php');
}
//===========blog===========//

?>