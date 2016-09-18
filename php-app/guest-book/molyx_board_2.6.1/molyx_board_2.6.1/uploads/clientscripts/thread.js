function delete_post(theURL) {
	if (confirm( lang_g['g_delt'] ))	{
		window.location.href=theURL;
	} else {
		return;
	} 
}
function url_changecash(uid,pid,tid,url) {
	var cash = my_getbyid('give_cash_'+pid).value;
	var numberset="0123456789-";
	var confim_add_cash = lang_t['t_confirm'];
	if (cash && check(numberset,cash)) {
		if (confirm( confim_add_cash )) {
			var f = my_getbyid('forum_id').value;
			var theURL = url+"/moderate.php?do=postdomoney&uid="+uid+"&t="+tid+"&f="+f+"&pid="+pid+"&cash="+cash;
			window.location.href=theURL;
		}
	} else {
		var info = lang_t['t_innatural'];
		var showrep = my_getbyid("rep" + pid);
		showrep.style.display='block';
		showrep.innerHTML = info;
	}
}
function multiquote_add(id) {
	saved = new Array();
	clean = new Array();
	add   = 1;
	if ( tmp = get_cookie('mqtids') ) {
		saved = tmp.split(",");
	}
	for( i = 0 ; i < saved.length; i++ ) {
		if ( saved[i] != "" ) {
			if ( saved[i] == id ) {
				 add = 0;
			} else 	{
				clean[clean.length] = saved[i];
			}
		}
	}
	if ( add ) {
		clean[ clean.length ] = id;
		eval("document.mad_"+id+".src=removequotebutton");
	} else {
		eval(" document.mad_"+id+".src=addquotebutton");
	}
	set_cookie( 'mqtids', clean.join(','), 0 );
	return false;
}

function em_size(str) {
    var a = document.getElementsByName(str);
    var n = a.length;
    try {
        var input_checkall = document.getElementById("checkall_"+str);
        var size = 0;
        input_checkall.checked = true ;
        for (var i=0; i < n; i++) {
            if (a[i].checked) {
                var piecesArray = a[i].value.split( "|" );
                size += piecesArray[3]*1;
            } else {
                input_checkall.checked = false;
            }
        }
        test = document.getElementById("size_"+str);
        test.innerHTML = gen_size(size, 3, 2);
    } catch (e) {
    }
}

function gen_size(val, li, sepa ) {
    sep = Math.pow(10, sepa);
    li = Math.pow(10, li);
    retval  = val;
    unit    = 'Bytes';
    if (val >= li*1000000000) {
        val = Math.round( val / (1099511627776/sep) ) / sep;
        unit  = 'TB';
    } else if (val >= li*1000000) {
        val = Math.round( val / (1073741824/sep) ) / sep;
        unit  = 'GB';
    } else if (val >= li*1000) {
        val = Math.round( val / (1048576/sep) ) / sep;
        unit  = 'MB';
    } else if (val >= li) {
        val = Math.round( val / (1024/sep) ) / sep;
        unit  = 'KB';
    }
    return val + unit;
}
function checkAll(str,checked) {
    var a = document.getElementsByName(str);
    var n = a.length;

    for (var i = 0; i < n; i++) {
        a[i].checked = checked;
    }
    em_size(str);
}
function download(str, i, first) {
    var a = document.getElementsByName(str);
    var n = a.length;
    for (var i = i; i < n; i++) {
        if(a[i].checked) {
            window.location=a[i].value;
            if (first)
                timeout = 6000;
            else
                timeout = 500;
            i++;
            window.setTimeout("download('"+str+"', "+i+", 0)", timeout);
            break;
        }
    }

}
function copy(str) {    
    var a = document.getElementsByName(str);
    var n = a.length;
    var ed2kcopy = "";
    for (var i = 0; i < n; i++) {
        if(a[i].checked) {
            ed2kcopy += a[i].value;
            ed2kcopy += "\r\n";
        }
    }
	ed2kcopy = decodeURI(ed2kcopy);
    copyToClipboard(ed2kcopy);
}
function copyToClipboard(txt) {
	if(window.clipboardData) {
   		window.clipboardData.clearData();
   		window.clipboardData.setData("Text", txt);
	} else if(navigator.userAgent.indexOf("Opera") != -1) {
		window.location = txt;
	} else if (window.netscape) {
		try {
			netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		} catch (e) {
			alert(lang_t['t_error1']+"'about:config'"+lang_t['t_error2']+"'signed.applets.codebase_principal_support'"+lang_t['t_error3']+"'true'");
		}
		var clip = Components.classes['@mozilla.org/widget/clipboard;1'].createInstance(Components.interfaces.nsIClipboard);
		if (!clip)
			return;
		var trans = Components.classes['@mozilla.org/widget/transferable;1'].createInstance(Components.interfaces.nsITransferable);
		if (!trans)
			return;
		trans.addDataFlavor('text/unicode');
		var str = new Object();
		var len = new Object();
		var str = Components.classes["@mozilla.org/supports-string;1"].createInstance(Components.interfaces.nsISupportsString);
		var copytext = txt;
		str.data = copytext;
		trans.setTransferData("text/unicode",str,copytext.length*2);
		var clipid = Components.interfaces.nsIClipboard;
		if (!clip)
			return false;
		clip.setData(trans,null,clipid.kGlobalClipboard);
	}
}
function check(charset,checkstr){
	var len,i,charstr;
	len=checkstr.length;
	for (i=0;i<len;i++)	
	{
		 charstr=checkstr.charAt(i);
		 if (charset.indexOf(charstr)==-1) return false;
	}
	return true;
} 
function ChangeRepCashDiv(pid){
	var RepDiv = my_getbyid('rep_div_'+pid);
	var CashDiv = my_getbyid('user_cash_'+pid);
	var CashDivContent = CashDiv.innerHTML;
	var RepDivContent = RepDiv.innerHTML;
	RepDiv.innerHTML = CashDivContent;
	CashDiv.innerHTML = RepDivContent;
}