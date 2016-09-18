	var oldType, oldHTML, imgURL, imgTID, oldTHREAD;
	function SetPromptTable() {
		var promptTable = document.createElement('table');
		promptTable.id = "pmnotifywrap";
		promptTable.cellPadding = "1";
		promptTable.cellSpacing = "1";
		promptTable.width = "480px";
		for (r = 0; r < 2; r++) {
			var promptTR = promptTable.insertRow(-1);
			if (r == 0){
				promptTR.className = 'pmnotifytop';
				var promptTD = promptTR.insertCell(-1);
				promptTD.height = '12';
				promptTD.align = 'center';
				promptTD.valign = 'middle';
				var promptDiv = document.createElement('div');
				promptDiv.id = 'pmnotifytop';
				promptDiv.innerHTML = lang_a['info_prinfo'];
				promptTD.appendChild(promptDiv);
			}
			if (r == 1){
				promptTR.className = 'pmnotify';
				var promptTD = promptTR.insertCell(-1);
				promptTD.className = 'pmnotify';
				promptTD.width = '480px';
				promptTD.align = 'left';
				promptTD.valign = 'middle';
				promptTD.innerHTML = '<br /><div id="showinfo" style="float:right;vertical-align:middle;text-align:center;width:400px;"></div>&nbsp;<img src="./images/'+imageurl+'/y.gif" width="25" height="48" />&nbsp;';
			}
		}
		var prompt_Div = document.createElement('div');
		prompt_Div.id = 'promptinfo';
		prompt_Div.style.zindex = '1';
		prompt_Div.style.left = '340px';
		prompt_Div.style.width = '480px';
		prompt_Div.style.position = 'absolute';
		prompt_Div.style.top = '585px';
		prompt_Div.style.height = '150px';
		prompt_Div.style.display = 'none';
		prompt_Div.style.textAlign = 'center';
		prompt_Div.style.filter = 'Alpha(Opacity=90);progid:DXImageTransform.Microsoft.DropShadow(color=#cccccc,offX=4,offY=4,positives=true)';
		prompt_Div.appendChild(promptTable);
		my_getbyid('footer').appendChild(prompt_Div);
	}
	function find(n, d) {
      var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
        d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
      if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
      for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=find(n,d.layers[i].document);
      if(!x && d.getElementById) x=d.getElementById(n); return x;
     }

    function show() {
      var i,p,v,obj,args=show.arguments;
      for (i=0; i<(args.length-2); i+=3) if ((obj=find(args[i]))!=null) { v=args[i+2];
        if (obj.style) { obj=obj.style; v=(v=='show')?'visible':(v=='hide')?'hidden':v; }
        obj.visibility=v; }
    }

	function L1show(info){
		showi();
		info += "<br /><font color=red>"+lang_a['info_nocorrs']+"</font>";
		my_getbyid("showinfo").innerHTML = info;
	}

	function DoRep_Cb(data){
	    var text = data.substr(data.lastIndexOf(']:::[') + 5);
	    var pid = data.substring(0,data.lastIndexOf(']:::['));
	   	var showrep = my_getbyid("rep" + pid);
		showrep.style.display='block';
        showrep.innerHTML = text;
	}

	function DoRep(puid,tid,pid,num,type){
		var showrep = my_getbyid("rep" + pid);
		if (type == 1)
		{
			 x_rep(puid + "]:::[" + tid + "]:::[" + pid + "]:::[" + num, DoRep_Cb);
		}
		else if (type == 0)
		{
			var info = lang_a['info_norep'];
			showrep.style.display='block';
			showrep.innerHTML = info;
			setTimeout(function() {
				showrep.innerHTML = "";
				showrep.style.display='none';
		   }, 3000);
		}
	}

	function name_cb(data){
		var text = data.substr(data.lastIndexOf(']:::[') + 5);
		var tid = data.substring(0,data.lastIndexOf(']:::['));
		var oldHTML = my_getbyid("oldhtml" + tid);
		var html = oldHTML.value;
		oldHTML.parentNode.innerHTML = html;
		my_getbyid("show" + tid).innerHTML = text;
		//nothing
	}

	function change_name(tid,value){
		strlen = calculate_byte(value);
		if (strlen > 0)	{
			x_change_name( tid + "]:::[" + value, name_cb);
		} else {
			alert(lang_a['info_threadlene']);
			var oldHTML = my_getbyid("oldhtml" + tid);
			var html = oldHTML.value;
			oldHTML.parentNode.innerHTML = html;
		}
	}

	function opent_cb(data){
		var fid = data.substr(data.lastIndexOf(']:::[') + 5);
		var tid = data.substring(0,data.lastIndexOf(']:::['));
		if (tid || fid) {
			var pic = "<img src='images/"+imageurl+"/folder.gif' border='0' alt='"+lang_a['info_closet']+"' onDblClick='turnthread(" + tid + ",1," + fid + ");' />";
			my_getbyid("pic" + tid).innerHTML = pic;
		}
	}

    function closet_cb(data){
		var fid = data.substr(data.lastIndexOf(']:::[') + 5);
		var tid = data.substring(0,data.lastIndexOf(']:::['));
		if (tid || fid) {
			var pic = "<img src='images/"+imageurl+"/closedfolder.gif' border='0' alt='"+lang_a['info_opent']+"' onDblClick='turnthread(" + tid + ",0," + fid + ");' />";
			my_getbyid("pic" + tid).innerHTML = pic;
		}
	}

	function turnthread(tid,type,fid){
		if (type == 1) x_closethread(tid, fid, closet_cb);
		if (type == 0) x_openthread(tid, fid, opent_cb);
	}

	function edithread(tid){
		if (my_getbyid("thread_c_b_sp")) closeColorSp();
		var oldTN = my_getbyid("thread_c_b_" + tid);
		var arrColors=[["#800000","#8b4513","#006400","#2f4f4f","#000080","#4b0082","#800080","#000000"],["#ff0000","#daa520","#6b8e23","#708090","#0000cd","#483d8b","#c71585","#696969"],["#ff4500","#ffa500","#808000","#4682b4","#1e90ff","#9400d3","#ff1493","#a9a9a9"],["#ff6347","#ffd700","#32cd32","#87ceeb","#00bfff","#9370db","#ff69b4","#dcdcdc"],["X","#ffffe0","#98fb98","#e0ffff","#87cefa","#e6e6fa","#dda0dd","#ffffff"]];
		if (!my_getbyid("show" + tid)) return false;
		var title = my_getbyid("show" + tid).innerHTML;
		var boldck = title.toLowerCase().lastIndexOf('<strong>') == '-1' ? '' : 'checked';
		var colorTableDiv = document.createElement('div');
		colorTableDiv.id = 'thread_c_b_sp';
		colorTableDiv.style.display = 'inline';
		colorTableDiv.style.position = 'absolute';
		colorTableDiv.style.border = '#7FB9F8 1px solid';
		colorTableDiv.style.background = '#FFFFFF';
		var chDiv = document.createElement('div');
		chDiv.style.width = '124px';
		chDiv.style.padding = '0px 2px 0px 2px';
		chDiv.innerHTML = "<span style='float:right;'><a href='javascript:resetTitleColor("+tid+");'>"+lang_a['info_renew']+"</a>&nbsp;<a href='javascript:closeColorSp();'>"+lang_a['info_close']+"</a></span><input id='showbb"+tid+"' style='width:12px;height:12px;' type='checkbox' "+boldck+" />&nbsp;<strong>"+lang_a['info_bold']+"</strong>";
		colorTableDiv.appendChild(chDiv);
		var colorTable = document.createElement('table');
		colorTable.cellPadding = "1";
		colorTable.cellSpacing = "1";
		colorTable.style.fontSize = "9px";
		for (var n in arrColors)
		{
			var colorTR = colorTable.insertRow(-1);
			for (var m in arrColors[n])
			{
				var colorTD = colorTR.insertCell(-1);
				colorTD.id = 'forecolor_' + tid + '_sp_' + arrColors[n][m] ;
				colorTD.className = 'row1';
				var colorDiv = document.createElement('div');
				colorDiv.style.width = '11px';
				colorDiv.style.height = '11px';
				colorDiv.style.border = 'white 1px solid';
				colorDiv.style.fontSize = '1px';
				colorDiv.style.cursor='hand';
				colorDiv.onclick = chTitleColor;
				colorDiv.onmouseover = changeCss1;
				colorDiv.onmouseout = changeCss2;
				if (arrColors[n][m] == 'X') {
					colorDiv.innerHTML = 'X';
					colorDiv.style.font = '11px Arial';
					colorDiv.style.textAlign = 'center';
					colorDiv.style.lineHeight = '11px';
				}
				else colorDiv.style.background = arrColors[n][m];
				colorTD.appendChild(colorDiv);
			}
		}
		colorTableDiv.appendChild(colorTable);
		oldTN.appendChild(colorTableDiv);
	}

	function cbsend_cb(data){
		var text = data.substr(data.lastIndexOf(']:::[') + 5);
		var tid = data.substring(0,data.lastIndexOf(']:::['));
		my_getbyid("show" + tid).innerHTML = text;
	}

	function changeCss1(e){
		var el;
		if (isIE) el = window.event.srcElement;
		else el = e.target;
		eventid = el.parentNode;
		eventid.style.backgroundColor = 'darkgray' ;
	}

	function changeCss2(e){
		var el;
		if (isIE) el = window.event.srcElement;
		else el = e.target;
		eventid = el.parentNode;
		eventid.style.backgroundColor = '';
	}

	function resetTitleColor(tid){
		closeColorSp();
		x_cbsend(tid, 'reset', 0, cbsend_cb);
	}

	function chTitleColor(e){
		var el;
		if (isIE) el = window.event.srcElement;
		else el = e.target;
		eventid = el.parentNode;
		var text = eventid.id;
		var color = text.substr(text.lastIndexOf('_sp_') + 4);
		var tid = text.substring(10,text.lastIndexOf('_sp_'));
		var bold = my_getbyid("showbb" + tid).checked ? 1 : 0;
		closeColorSp();
		x_cbsend(tid,color,bold,cbsend_cb);
	}

	function closeColorSp(){
		my_getbyid("thread_c_b_sp").parentNode.removeChild(my_getbyid("thread_c_b_sp"));
	}

	function noajax(){
		//nothing
	}

    function quickreply_cb(data) {
		if (my_getbyid('querySubmit')) {
			var querySubmit = my_getbyid('querySubmit');
			querySubmit.disabled = false;
		}
		if (my_getbyid('queryPreview')) {
			var queryPreview = my_getbyid('queryPreview');
			queryPreview.disabled = false;
		}
		var showl1 = my_getbyid("promptinfo");
		var showform = my_getbyid("mxbform");
	    var text = data.substr(data.lastIndexOf(']:::[') + 5);
	    var num = data.substring(0,data.lastIndexOf(']:::['));
		if (num.lastIndexOf(']:::[') != -1) {
			var num_tmp = num.substring(0,num.lastIndexOf(']:::['));
			num = num_tmp;
		}
		if (num == 1)
		{
             isint = 1;
			 if (text) var info = text;
			 else var info = lang_a['info_ermember'];
			 L1show(info);
			 setTimeout(function() {
				showl1.style.display='none';
				showl1.style.visibility='hidden';
			    }, 6000);
		 	 var pnum = showform.pnum.value;
			 if (pnum > 11)
			 {
				ppnum = parseInt(pnum);
				showform.pnum.value = ppnum;
			 }
			 return;
		}else{
			isint = 0;
		}
		antim = text.substr(text.lastIndexOf('+:::+') + 5);
		pagetext = text.substring(0,text.lastIndexOf('+:::+'));
		if (my_getbyid("ajaxrep" + num)) my_getbyid("ajaxrep" + num).style.display = "block";
		else {
			if (isGecko) alert(lang_a['info_erfxre']);
			else return false;
		}
		if (isIE) my_getbyid("ajaxrep" + num).innerHTML = pagetext;
		else {
			try
			{
				my_getbyid("ajaxrep" + num).innerHTML = pagetext;
			}
			catch (e)
			{
				numvalue = 11;
				my_getbyid("ajaxrep" + numvalue).innerHTML = pagetext;
			}
		}
		antimhash = antim.substr(antim.lastIndexOf('-:::-') + 5);
		antimtext = antim.substring(0,antim.lastIndexOf('-:::-'));
		var numnew = parseInt(num) + 1;
		showform.pnum.value = numnew;
		if (antimhash == 1 && antimtext == 1)
		{
			//nothing
		}
		else
		{
			if (my_getbyid("antispam")){
				showform.userimagehash.value = antimhash;
				my_getbyid("antispamtext").innerHTML = antimtext;
			}else{
				//nothing
			}
		}
		showl1.style.display='none';
		showl1.style.visibility='hidden';
		openquick();
	}


	function queryly(){
		if (my_getbyid('querySubmit'))
			var querySubmit = my_getbyid('querySubmit');
			querySubmit.disabled = true;
		if (my_getbyid('queryPreview'))
			var queryPreview = my_getbyid('queryPreview');
			queryPreview.disabled = true;
		var showredirect = my_getbyid("redirect").checked;
		var showl1 = my_getbyid("promptinfo");
		var showan = my_getbyid("antispam");
		var showform = my_getbyid("mxbform");
		if (showredirect == true) {
			mxeGet();
			showform.submit();
			return false;
		}
		if (showan)
		{
			var antispam = showan.value;
		}
		else
		{
            var antispam = 1;
		}
		if (showan)
		{
			if (antispam.length != 4 || !antispam)
			{
				if (antispam != 1)
				{
					 showan.value = "";
					 var info = lang_a['info_inmenber'];
					 L1show(info);
					 setTimeout(function() {
						showl1.style.display='none';
						showl1.style.visibility='hidden';
					   }, 3000);
				}
				querySubmit.disabled = false;
				queryPreview.disabled = false;
				return;
			}
		}
		mxeGet();
		var content = mxeTxH.value;
		initData();
		var contentlength = calculate_byte(content);
		if ( contentlength < postminchars || contentlength > postmaxchars )
		{
			try
			{
				showan.value = "";
			}
			catch (e)
			{
				//nothing
			}
			var info = lang_a['info_ctis']+postminchars+"~"+postmaxchars+lang_a['info_bhstr'];
			L1show(info);
			querySubmit.disabled = false;
			queryPreview.disabled = false;
			setTimeout(function() {
				showl1.style.display='none';
				showl1.style.visibility='hidden';
			   }, 3000);
			 return;
		}
		else
		{
			mxeTxH.value = '';
			var todo = 'update';
			var s = showform.s.value;
			var f = showform.f.value;
			var t = showform.t.value;
			var qreply = 1;
			var userimagehash = showform.userimagehash.value;
			var smile,signature,url,quote;
			var allowsmile = showform.allowsmile.checked;
			allowsmile==true?smile=1:smile=0;
			var showsignature = showform.showsignature.checked;
			showsignature==true?signature=1:signature=0;
			var parseurl = showform.parseurl.checked;
			parseurl==true?url=1:url=0;
			var quotepost = showform.quotepost.checked;
			quotepost==true?quote=1:quote=0;
			if (showform.anonymous)
			{
				var anonymous = showform.anonymous.checked;
				anonymous==true?anonymous=1:anonymous=0;
			}
			else
			{
				var anonymous = 0;
			}
			var givecash = showform.givecash.value;
			if (!givecash)
			{
				givecash = 0;
			}
			var allowbbcode = showform.allowbbcode.value;
			var postnum = showform.postnum.value;
			var pnum = showform.pnum.value;
			var modeType = wMode ? 'wysiwyg' : 'bbcode';
			x_quickreply( f, userimagehash, pnum, t, content, s, smile, signature, url, quote ,givecash ,allowbbcode ,postnum ,antispam ,qreply, modeType, anonymous, quickreply_cb);
			if (antispam != 1)
			{
				showan.value = "";
			}
			var info = lang_g['g_refering'];
			L1show(info);
		}
	}

	function returnpagetext_cb(data){
		var html1 = data.substr(data.lastIndexOf(']:::[') + 5);
		var pid = data.substring(0,data.lastIndexOf(']:::['));
		var showDiv = my_getbyid('show' + pid);
		var html2 = showDiv.innerHTML;
		oldHTML = document.createElement('input');
		oldHTML.type = 'hidden';
		oldHTML.id = 'oldHTML' + pid;
		oldHTML.value = html2;
		if (html1.lastIndexOf('<!--editpost-->') != -1 ) html1 = html1.substring(0,html1.lastIndexOf('<!--editpost-->'));
		else html1 = html1;
		wMode = 1;
		var showParent = showDiv.parentNode;
		showDiv.innerHTML = '';
		showDiv.appendChild(oldHTML);
		showTxa = document.createElement('textarea');
		showTxa.id = 'showarea' + pid;
		showTxa.style.width = '500px';
		showTxa.style.height = '300px';
		showTxa.value = html1;
		showDiv.appendChild(showTxa);
		mxeditor('showarea' + pid);
		showSubmit = document.createElement('div');
		showSubmit.style.width = '500px';
		showSubmit.style.height = '30px';
		showSubmit.style.textAlign = 'center';
		var click_se_nd = 'editorsend('+pid+')';
		var type = 'text';
		showSubmit.innerHTML = '<input type="button" value="&nbsp;&nbsp;'+lang_a['info_refer']+'&nbsp;&nbsp;" class="button" onclick="'+click_se_nd+';" />&nbsp;<input type="button" value="&nbsp;&nbsp;'+lang_a['info_centre']+'&nbsp;&nbsp;" class="button" onclick="editorreset('+pid+',\''+type+'\');openquick();" />';
		showDiv.appendChild(showSubmit);
		mxeWin.focus();
	}

	function showedit(pid,fid,bbcode,html,type,uid){
		if (mxe) {
			if (typeof mxeDoc == 'object' &&  mxe.substr(0, 8) == 'showarea') {
				var mxe_pid = mxeTxH.id.substr(8);
				if (mxe_pid == pid) return;
				editorreset(mxe_pid,oldType);
			} else {
				closedquick();
			}
		}
		if (my_getbyid('querySubmit')) {
			if (my_getbyid('querySubmit').disabled == true) {
				my_getbyid('post').onclick = null;
			} else {
				my_getbyid('querySubmit').disabled = true;
			}
		}
		oldType = type;
		if (isIE) var geit = 1;
		else var geit = 0;
		if (type == 'text') {
			thisforum = realthisforum;
			x_returnpagetext(pid, returnpagetext_cb);
		} else {
			thisforum = 'signature';
			var showDiv = my_getbyid('signature' + pid);
			var html = showDiv.innerHTML;
			oldHTML = document.createElement('input');
			oldHTML.type = 'hidden';
			oldHTML.id = 'oldHTML' + pid;
			oldHTML.value = html;
			if (html.lastIndexOf('<!--editpost-->') != -1 ) {
				html = html1.substring(0,html1.lastIndexOf('<!--editpost-->'));
			}
			wMode = 1;
			var showParent = showDiv.parentNode;
			showDiv.innerHTML = '';
			showDiv.appendChild(oldHTML);
			showTxa = document.createElement('textarea');
			showTxa.id = 'showarea' + pid;
			showTxa.style.width = '300px';
			showTxa.style.height = '150px';
			showTxa.value = html;
			showDiv.appendChild(showTxa);
			mxeditor('showarea' + pid);
			showSubmit = document.createElement('div');
			showSubmit.style.width = '300px';
			showSubmit.style.height = '30px';
			showSubmit.style.textAlign = 'center';
			var click_se_nd = 'sigsend('+pid+','+uid+')';
			showSubmit.innerHTML = '<input type="button" value="&nbsp;&nbsp;'+lang_a['info_refer']+'&nbsp;&nbsp;" class="button" onclick="'+click_se_nd+';" />&nbsp;<input type="button" value="&nbsp;&nbsp;'+lang_a['info_centre']+'&nbsp;&nbsp;" class="button" onclick="editorreset('+pid+',\''+type+'\');openquick();" />';
			showDiv.appendChild(showSubmit);
			mxeWin.focus();
		}
    }

	function returnsig_cb(data){
		var tmp = data.substr(data.lastIndexOf(']:::[') + 5);
		var pid = data.substring(0,data.lastIndexOf(']:::['));
		my_getbyid("signature"+pid).innerHTML = tmp;
		var showli = my_getbyid("promptinfo");
		showli.style.display='none';
		showli.style.visibility='hidden';
		openquick();
	}

	function sigsend(pid,uid){
		thisforum = realthisforum;
		mxeGet();
		var content = mxeTxH.value;
		var contentlength = calculate_byte(content);
		if (contentlength > postmaxchars || contentlength <= 4)
		{
			var info= lang_g['g_outstr']+"(4~"+postmaxchars+")"+lang_a['info_again']+".";
			var id = "signature" + pid;
			CreatDiv(id,info);
			setTimeout(function() {
				my_getbyid(id).innerHTML = oldHTML.value;
		    }, 3000);
			openquick();
			return false;
		}
		my_getbyid("signature" + pid).style.display='block';
		var info="<font color='blue'>"+lang_a['info_reading']+"</font>";
		var id = "signature" + pid;
		CreatDiv(id,info);
		var modeType = wMode ? 'wysiwyg' : 'bbcode';
		x_returnsig(pid, content, uid, modeType, returnsig_cb);
	}

	function editorreset(pid,type){
		thisforum = realthisforum;
		if (typeof mxeDoc == 'object' &&  mxe == 'showarea' + pid +'W') {
			var oldHTML = my_getbyid('oldHTML' + pid);
			if (oldHTML) {
				var html = oldHTML.value;
				if (typeof(load_qmxe)=="function") {
					my_getbyid("post").onclick = Function("load_qmxe()");
				}
			} else {
				return;
			}
			if (type == 'text')	var showDiv = my_getbyid('show' + pid);
			else var showDiv = my_getbyid('signature' + pid);
			showDiv.innerHTML = html;
			mxe = mxeWin = mxeDoc = mxeTxa = mxeTxH = mxeEbox = mxeStatus = mxeWidth = mxeHeight = eWidth = null;
		}
	}

	function openquick() {
		if (canwMode) {
			var cookiemode = get_cookie('mxeditor');
			if (cookiemode == 'wysiwyg')  wMode = 1;
			else if (cookiemode == 'bbcode')  wMode = 0;
		} else wMode = 0;
		if (my_getbyid('post')) {
			my_getbyid('querySubmit').disabled = false;
			if (typeof(load_qmxe)=="function") {
				my_getbyid("post").onclick = Function("load_qmxe()");
			}
			if (fEdit) {
				var quickREdit = my_getbyid('eDiv_postW');
				if (quickREdit) {
					quickREdit.parentNode.removeChild(quickREdit);
					mxeditor('post', qmxemenu);
				} else {
					mxe = mxeWin = mxeDoc = mxeTxa = mxeTxH = mxeEbox = mxeStatus = mxeWidth = mxeHeight = eWidth = null;
				}
			}
			if (my_getbyid('querySubmit')) my_getbyid('querySubmit').onclick = queryly;
		}
	}

	function closedquick(){
		if (typeof mxeDoc == 'object' &&  mxe == 'postW') {
			if (fEdit) {
				if (wMode) {
					if (isIE) {
						mxeWin.document.open();
						mxeWin.document.close();
					}
					mxeWin.document.designMode = "off";
				} else mxeTxa.readOnly = 'readonly';
				var quickREdit = my_getbyid('eDiv_' + mxe);
				if (quickmxemenu == "1") {
					var ncBu;
					for (var n in mBut) {
						if(mBut[n] != '|') {
							ncBu = my_getbyid(mBut[n] + '_mxButton_' + mxe);
							ncBu.className = 'bu_miss';
							ncBu.onmouseover = ncBu.onmouseout  = ncBu.onmousedown = ncBu.onmouseup = null;
						}
					}
				}
				mxeStatus.innerHTML = lang_a['info_closequ'];
				quickREdit.onmouseover = quickREdit.onmouseout  = quickREdit.onmousedown = quickREdit.onmouseup = null;
			}
			mxe = mxeWin = mxeDoc = mxeTxa = mxeTxH = mxeEbox = mxeStatus = mxeWidth = mxeHeight = eWidth = null;
			my_getbyid('querySubmit').onclick = 'function ff_c_l() {return false;}';
		}
	}

	function returntext_cb(data){
		var tmp = data.substr(data.lastIndexOf(']:::[') + 5);
		var pid = data.substring(0,data.lastIndexOf(']:::['));
		my_getbyid("show" + pid).innerHTML = tmp;
		var showli = my_getbyid("promptinfo");
		showli.style.display='none';
		showli.style.visibility='hidden';
		openquick();
	}

    function editorsend(pid){
		var f = my_getbyid('forum_id').value;
		mxeGet();
		var content = mxeTxH.value;
		var contentlength = calculate_byte(content);
		if (contentlength < postminchars || contentlength > postmaxchars)
		{
			var info= lang_g['g_outstr']+"("+postminchars+"~"+postmaxchars+")"+lang_a['info_again']+"?";
			var id = "show" + pid;
			CreatDiv(id,info);
			setTimeout(function() {
				my_getbyid("show" + pid).innerHTML = oldHTML.value;
		    }, 3000);
			openquick();
			return false;
		}

		my_getbyid("show" + pid).style.display='block';
		var info="<font color='blue'>"+lang_a['info_reading']+"</font>";
		var id = "show" + pid;
		CreatDiv(id,info);
		var modeType = wMode ? 'wysiwyg' : 'bbcode';
		x_returntext(pid,content,f,modeType,returntext_cb);
    }

	function checkuser_cb(data){
		var isok_username = my_getbyid("isok_username");
		var isno_username = my_getbyid("isno_username");
		var isexist_username = my_getbyid("isexist_username");
		var islength_username = my_getbyid("islength_username");
		isno_username.style.display = 'none';
		isok_username.style.display = 'none';
		isexist_username.style.display = 'none';
		islength_username.style.display = 'none';
		if(data=='no') {
			isno_username.style.display = 'block';
		} else if (data=='length') {
			islength_username.style.display = 'block';
		} else if (data=='exist') {
			isexist_username.style.display = 'block';
		} else if(data=='ok') {
			isok_username.style.display = 'block';
		}
	}

	function checkuser(){
		userD_Htable = my_getbyid("user");
        var username = my_getbyid("username").value;
		var isempty_username = my_getbyid("isempty_username");
		if (username){
			if (username.length <= 0) {
				isempty_username.style.display ='block';
			} else {
				isempty_username.style.display ='none';
				x_checkuser(username,checkuser_cb);
			}
		}
	}

	function checkmail_cb(data){
		var isok_email = my_getbyid("isok_email");
		var isno_email = my_getbyid("isno_email");
		var isexist_email = my_getbyid("isexist_email");
		isno_email.style.display = 'none';
		isok_email.style.display = 'none';
		isexist_email.style.display = 'none';
		if(data=='no'){
			isno_email.style.display = 'block';
		} else if (data=='exist') {
			isexist_email.style.display = 'block';
		} else if(data=='ok') {
			isok_email.style.display = 'block';
		}
	}

	function checkmail(){
         var email = my_getbyid("email").value;
		 var isempty_email = my_getbyid("isempty_email");
		 if (email.length <= 0) {
			isempty_email.style.display = 'block';
		 } else {
            isempty_email.style.display = 'none';
			x_checkmail(email,checkmail_cb);
		 }
	}

	function sendcolor(color,tid){
		if (color == 1)
		{
			my_getbyid("colorf"+tid).innerHTML = "<span style=\"background: "+color+"; width:33px; position:absolute; margin:2px; border:1px ridge ;\" id='preview_0' onclick='showcolor("+tid+");' onmouseover=\"this.style.cursor='hand';\">"+lang_a['info_renew']+"</span>";
			my_getbyid("titlecolor"+tid).value = "";
		}
		else{
			my_getbyid("colorf"+tid).innerHTML = "<span style=\"background: "+color+"; width:18px; position:absolute; margin:2px; border:1px ridge ;\" id='preview_0' onclick='showcolor("+tid+");' onmouseover=\"this.style.cursor='hand';\">&nbsp;&nbsp;&nbsp;</span>";
			my_getbyid("titlecolor"+tid).value = color;
		}
	}

	function showthreadin(tid){
		if (my_getbyid("show" + tid)){
			var oldTN = my_getbyid("show" + tid);
		} else {
			return false;
		}
		var oldTNP = oldTN.parentNode.parentNode;
		for (var i=0; i<2; i++){
			if (oldTN.childNodes[0].tagName) oldTN = oldTN.childNodes[0];
		}
		var oldHTML = document.createElement('input');
		oldHTML.id = 'oldhtml'+tid;
		oldHTML.type = 'hidden';
		oldHTML.value = oldTNP.innerHTML;
		oldTHREAD = oldTNP.innerHTML;
		var text = oldTN.innerHTML.replace(/&gt;/g,'>').replace(/&lt;/g,'<').replace(/&amp;/g,'&');
		oldTNP.innerHTML = "<input type='text' size='40' maxlength='50' id='threadname"+tid+"' value='' class='bginput' onkeydown=\"if( event.keyCode==13){change_name("+tid+",this.value);}\" onblur=\"change_name('"+tid+"',this.value)\";>";
		oldTNP.appendChild(oldHTML);
		var textIp = my_getbyid("threadname" + tid);
		textIp.value = text;
		textIp.focus();
	}

	function CreatDiv( id , info){
		var value = '';
		var showli = my_getbyid("promptinfo");
		showli.style.left = mxeTxH.offsetLeft + 30;
		showli.style.top = mxeTxH.offsetTop + 60;
		showli.style.display='block';
		showli.style.visibility='visible';
		my_getbyid("showinfo").innerHTML = "<center>"+info+"<br />"+lang_a['info_nocorrs']+"</center>";
	}
	function showi(){
		var showli = my_getbyid("promptinfo");
		var width = 480;
		var height = 150;
		var screenW = window.screen.width;
		var screenH = window.screen.height;
	    var w = document.body.clientWidth;
	    var h = document.body.clientHeight;
	    var w0 = Math.floor(((w - width) / 2) - 50);
	    var h0 = Math.floor((h - height) - 250 );
		if (isIE) {
			showli.style.left=w0 + 'px';
			showli.style.top=h0 + 'px';
		} else {
			var wfox = ((screenW - width) / 2) - 50;
			if (screenH > 700) var hfox = h-424;
			else if (screenH < 700) var hfox = h-324;
			showli.style.left=wfox + 'px';
			showli.style.top= hfox + 'px';
		}
		showli.style.display='block';
		showli.style.visibility='visible';
	}
	function urlencode(text){
		text = text.toString();
		var matches = text.match(/[\x90-\xFF]/g);
		if (matches)
		{
			for (var matchid = 0; matchid < matches.length; matchid++)
			{
				var char_code = matches[matchid].charCodeAt(0);
				text = text.replace(matches[matchid], '%u00' + (char_code & 0xFF).toString(16).toUpperCase());
			}
		}
		return escape(text).replace(/\+/g, "%2B");
	}
	function removeattach_cb(data){
		my_getbyid('showattach').innerHTML = data;
		var formValue = my_getbyid('mxbform');
		my_getbyid('submitform').disabled = false;
		if (my_getbyid('preview')) {
			my_getbyid('preview').disabled = true;
		}
	}
	function ajaxremoveattach(id){
		var formValue = my_getbyid('mxbform');
		var posthash = formValue.posthash.value;
		if (formValue.p){
			var p = formValue.p.value;
		} else {
			var p = 'NULL';
		}
		my_getbyid('submitform').disabled = true;
		if (my_getbyid('preview'))
		{
			my_getbyid('preview').disabled = true;
		}
		var info = "<font color=red>"+lang_a['info_deling']+"</font>";
		my_getbyid('showattach').innerHTML = info;
		x_removeattach(id,posthash,p,removeattach_cb);
	}
	function deletepost_search_cb(data){
		var pid = data.substr(data.lastIndexOf(']:::[') + 5);
		var isnot = data.substring(0,data.lastIndexOf(']:::['));
		if (isnot == 1){
			var tableDel = my_getbyid('search_div_'+pid);
			tableDel.innerHTML = '';
			tableDel.style.visibility = 'hidden';
			tableDel.style.display = 'none';
		} else {
			alert(lang_a['info_delno']);
		}
	}
	function deletepost_cb(data){
		var pid = data.substr(data.lastIndexOf(']:::[') + 5);
		var isnot = data.substring(0,data.lastIndexOf(']:::['));
		if (isnot == 1){
			var tableDel = my_getbyid('table_'+pid);
			tableDel.innerHTML = '';
			tableDel.style.visibility = 'hidden';
			tableDel.style.display = 'none';
		} else {
			alert(lang_a['info_delno']);
		}
	}
	function delete_post_ajax(pid,theURL,isThread,fid){
		if (isThread == 2){
			if (my_getbyid('post_1_count')) var post_1_count = my_getbyid('post_1_count').value;
			if (post_1_count && pid == post_1_count){
				if (confirm( lang_g['g_delt'] ))	{
					window.location.href=theURL;
				} else {
					return;
				}
			} else {
				var fid = my_getbyid('forum_id').value;
				var msginfo = prompt(lang_a['info_delex'],'');
				x_deletepost(pid, msginfo, fid, deletepost_cb);
			}
		} else {
			if (isThread == 1){
				var confim_delete = lang_a['info_delok'];
				if (confirm( confim_delete ))	{
					window.location.href=theURL;
				} else {
					return;
				}
			} else {
				var msginfo = prompt(lang_a['info_delex'],'');
				x_deletepost(pid, msginfo, fid, deletepost_search_cb);
			}
		}
	}
	function ChangRule(fid){
		var Txaisexist = my_getbyid('rule_' + fid);
		if (!Txaisexist)
		{
			var rule_area = my_getbyid('forum_rule');
			var rule_control = my_getbyid('edit_rule');
			oldHTML = rule_area.innerHTML;
			rule_area.innerHTML = '';
			showTxa = document.createElement('textarea');
			showTxa.id = 'rule_' + fid;
			showTxa.style.width = '550px';
			showTxa.style.height = '100px';
			showTxa.value = oldHTML.replace(/\<BR>/g,'\n').replace(/\<BR \/>/g,'\n').replace(/\<br>/g,'\n').replace(/\<br \/>/g,'\n');
			rule_area.appendChild(showTxa);
			showTxa.focus();
			rule_control.innerHTML = '<input type="button" value="&nbsp;&nbsp;'+lang_a['info_refer']+'&nbsp;&nbsp;" class="button" onclick="rule_send('+ fid +')" /><br /><br /><input type="button" value="&nbsp;&nbsp;'+lang_a['info_centre']+'&nbsp;&nbsp;" class="button" onclick="rule_cancle('+ fid +');" />';
		}
		else
		{
			Txaisexist.focus();
		}
	}
	function changerule_cb(data){
		if (data.lastIndexOf(']:::[') != -1 ) {
			var content = data.substr(data.lastIndexOf(']:::[') + 5);
			var fid = data.substring(0,data.lastIndexOf(']:::['));
			var rule_area = my_getbyid('forum_rule');
			var rule_control = my_getbyid('edit_rule');
			rule_area.innerHTML = content;
			rule_control.innerHTML = '';
		} else {
			var info = data.substr(data.lastIndexOf('-:::-') + 5);
			var fid = data.substring(0,data.lastIndexOf('-:::-'));
			var rule_area = my_getbyid('forum_rule');
			var rule_control = my_getbyid('edit_rule');
			rule_area.innerHTML = "<font color=red>"+info+"</font>";
			rule_control.innerHTML = '';
			setTimeout(function() {
				rule_area.innerHTML = oldHTML;
			}, 2000);
		}
	}
	function rule_send(fid){
		var rule_Txa = my_getbyid('rule_'+fid);
		if (rule_Txa.value) x_changerule(rule_Txa.value,fid,changerule_cb);
		else alert(lang_a['info_isnotu']);
	}
	function rule_cancle(fid){
		var rule_area = my_getbyid('forum_rule');
		var rule_control = my_getbyid('edit_rule');
		rule_area.innerHTML = oldHTML;
		rule_control.innerHTML = '';
	}

	function change_cash_cb(data){
		var info = data.substr(data.lastIndexOf(']:::[') + 5);
		var id = data.substring(0,data.lastIndexOf(']:::['));
		var pid = id.substr(id.lastIndexOf('-:::-') + 5);
		var uid = id.substring(0,id.lastIndexOf('-:::-'));
		var showcash = my_getbyid("cash" + pid);
		var RepDiv = my_getbyid('rep_div_'+pid);
		showcash.style.display='block';
        showcash.innerHTML = info;
	}
	function change_cash(uid,pid,tid,fid){
		var cash = my_getbyid('give_cash_'+pid).value;
		var numberset="0123456789-";
		if (cash && check(numberset,cash)) {
			x_change_cash(uid,pid,cash,tid,fid,change_cash_cb);
		} else {
			var showcash = my_getbyid("cash" + pid);
			showcash.style.display='block';
			var info = lang_a['info_innatural'];
			showcash.innerHTML = info;
		}
	}
	function sendpreview_cb(data){
		var preview_Div = my_getbyid('previewpost');
		preview_Div.innerHTML = '';
		showDiv = document.createElement('div');
		showDiv.id = 'wborde';
		showDiv.className = 'wborder';
		showDiv_content = document.createElement('div');
		showDiv_content.id = 'pre_content';
		showDiv_content.className = 'tablepad';
		showDiv_content.innerHTML = data;
		showDiv_title = document.createElement('div');
		showDiv_title.id = 'pre_title';
		showDiv_title.className = 'thead';
		showDiv_title.innerHTML = lang_a['info_pretitle'];
		showDiv.appendChild(showDiv_title);
		showDiv.appendChild(showDiv_content);
		preview_Div.appendChild(showDiv);
		var top = preview_Div.offsetTop;
		window.scroll(0,top);
	}
	function previewpost(fid){
		mxeGet();
		var obj = my_getbyid('allowsmile');
		if (obj) {
			if (obj.checked) {
				var allowsmile = 1;
			} else {
				var allowsmile = 0;
			}
		} else {
			var allowsmile = 1;
		}
		var content = mxeTxH.value;
		x_sendpreview(content, fid, allowsmile, sendpreview_cb);
	}
	function smilespage_cb(data) {
        if (data == 'none') {
            return;
        }
        var text = data.split('-:::-');
		var obj = my_getbyid('smiliespage');
        obj.innerHTML = text[0];
        my_getbyid('smileslastpage').href = 'javascript:smilespage('+text[1]+', 0);';
        my_getbyid('smilesnextpage').href = 'javascript:smilespage('+text[1]+', 1);';
	}
	function smilespage(num, p) {
	   x_smilespage(num, p, smilespage_cb);
	}