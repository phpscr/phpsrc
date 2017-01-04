var win = window;
var n = 0;
function pop_win(theUrl, winName, theWidth, theHeight)
{
	if (winName == '') { winName = 'Preview'; }
	if (theHeight == '') { theHeight = 400; }
	if (theWidth == '') { theWidth = 400; }
	window.open(theUrl,winName,'width='+theWidth+',height='+theHeight+',resizable=yes,scrollbars=yes');
}
function togglediv( id, show )
{
	if ( show ) {
		my_show_div( my_getbyid(id) );
	} else {
		my_hide_div( my_getbyid(id) );
	}
	return false;
}
function togglemenucategory( fid, add )
{
	saved = new Array();
	clean = new Array();
	if ( tmp = get_cookie('cpcollapseprefs') ) {
		saved = tmp.split(",");
	}
	for( i = 0 ; i < saved.length; i++ ) {
		if ( saved[i] != fid && saved[i] != "" ) {
			clean[clean.length] = saved[i];
		}
	}
	if ( add ) {
		clean[ clean.length ] = fid;
		my_show_div( my_getbyid( 'fc_'+fid  ) );
		my_hide_div( my_getbyid( 'fo_'+fid  ) );
	} else {
		my_show_div( my_getbyid( 'fo_'+fid  ) );
		my_hide_div( my_getbyid( 'fc_'+fid  ) );
	}
	set_cookie( 'cpcollapseprefs', clean.join(','), 1 );
}

function expandmenu()
{
	saved = new Array();
	joined = new Array();
	clean = new Array();
	if ( tmp = get_cookie('cpcollapseprefs') ) {
		saved = tmp.split(",");
	}
	joined = menu_ids.split(",");
	for( c = 0 ; c < joined.length; c++ ) {
		clean[clean.length] = joined[c];
	}
	set_cookie( 'cpcollapseprefs', clean.join(','), 1 );
	window.location=window.location;
}
function collapsemenu()
{
	set_cookie( 'cpcollapseprefs', '', 1 );
	window.location=window.location;
}

function checkcol(IDnumber,status)
{
	var f = document.cpform;
	str_part = '';
	if (IDnumber == 1) { str_part = 'read' }
	if (IDnumber == 2) { str_part = 'repl' }
	if (IDnumber == 3) { str_part = 'star' }
	if (IDnumber == 4) { str_part = 'uplo' }
	if (IDnumber == 5) { str_part = 'show' }
	for (var i = 0 ; i < f.elements.length; i++) {
		var e = f.elements[i];
		if ( e.type == 'checkbox' ) {
			s = e.name;
			a = s.substring(0, 4);
			if (a == str_part) {
				if ( status == 1 ) {
					e.checked = true;
				} else {
					e.checked = false;
				}
			}
		}
	}
}

function checkrow(IDnumber,status)
{
	var f = document.cpform;
	str_part = '';
	if ( status == 1 ) {
		mystat = 'true';
	} else {
		mystat = 'false';
	}
	eval( 'f.read_'+IDnumber+'.checked='+mystat );
	eval( 'f.reply_'+IDnumber+'.checked='+mystat );
	eval( 'f.start_'+IDnumber+'.checked='+mystat );
	eval( 'f.upload_'+IDnumber+'.checked='+mystat );
	eval( 'f.show_'+IDnumber+'.checked='+mystat );
}

function updatepreview()
{
	var formobj  = document.cpform;
	var dd_weekday  = new Array();
	dd_weekday[0]   = '周日';
	dd_weekday[1]   = '周一';
	dd_weekday[2]   = '周二';
	dd_weekday[3]   = '周三';
	dd_weekday[4]   = '周四';
	dd_weekday[5]   = '周五';
	dd_weekday[6]   = '周六';
	var output       = '';
	chosen_min   = formobj.minute.options[formobj.minute.selectedIndex].value;
	chosen_hour  = formobj.hour.options[formobj.hour.selectedIndex].value;
	chosen_weekday  = formobj.weekday.options[formobj.weekday.selectedIndex].value;
	chosen_monthday  = formobj.monthday.options[formobj.monthday.selectedIndex].value;
	var output_min   = '';
	var output_hour  = '';
	var output_day   = '';
	var timeset      = 0;
	if ( chosen_monthday == -1 && chosen_weekday == -1 ) {
		output_day = '';
	}
	if ( chosen_monthday != -1 ) {
		output_day = '在每月 '+chosen_monthday+' 号，';
	}
	if ( chosen_monthday == -1 && chosen_weekday != -1 ) {
		output_day = '于' + dd_weekday[ chosen_weekday ]+'.';
	}
	if ( chosen_hour != -1 && chosen_min != -1 ) {
		output_hour = '在 '+chosen_hour+':'+formatnumber(chosen_min)+' 的时候运行';
	} else {
		if ( chosen_hour == -1 ) {
			if ( chosen_min == 0 ) {
				output_hour = '每小时运行一次';
			} else {
				if ( output_day == '' ) {
					if ( chosen_min == -1 ) {
						output_min = '每分钟运行一次';
					} else {
						output_min = '每隔 '+chosen_min+' 分钟运行一次。';
					}
				} else {
					output_min = '第一个 '+formatnumber(chosen_min)+' 分钟的时候执行';
				}
			}
		} else {
			if ( output_day != '' ) {
				output_hour = '到 ' + chosen_hour + ':00' + ' 运行';
			} else {
				output_hour = '每隔 ' + chosen_hour + ' 小时运行';
			}
		}
	}
	output = output_day + ' ' + output_hour + ' ' + output_min;
	formobj.showcron.value = output;
}
							
function formatnumber(num)
{
	if ( num == -1 ) {
		return '00';
	}
	if ( num < 10 ) {
		return '0'+num;
	} else {
		return num;
	}
}

function confirmupload(tform, filefield)
{
	if (filefield.value == "") {
		return confirm("你并未指定从本地上传文件\n程序将尝试从服务器上导入风格文件:\n\n" + tform.fromserver.value + "\n\n你是否确认导入？");
	}
	return true;
}

function findInPage(tmpl, str)
{
	var txt, i, found;
	if (str == '') {
		return false;
	}
	if (isGecko) {
		if (!win.find(str, false, true)) {
			while(win.find(str, false, true)) {
				n++;
			}
		} else {
			n++;
		}
		if (n == 0) {
			alert('未找到要搜索的字符');
		}
	}
	if (isIE) {
		txt = tmpl.createTextRange();
		for (i = 0; i <= n && (found = txt.findText(str)) != false; i++) {
			txt.moveStart('character', 1);
			txt.moveEnd('textedit');
		}
		if (found) {
			txt.moveStart('character', -1);
			txt.findText(str);
			txt.select();
			txt.scrollIntoView(true);
			n++;
		} else {
			if (n > 0) {
				n = 0;
				findInPage(tmpl, str);
			}
			else { alert('未找到要搜索的字符'); }
		}
	}
	return false;
}