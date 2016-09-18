function editcolor() {
	if (my_getbyid("title_color_picker")) {
		closeColorSp();
	} else if (typeof arrColors == 'object') {
		var colorTableDiv = document.createElement('div');
		colorTableDiv.id = 'title_color_picker';
		colorTableDiv.style.display = 'inline';
		colorTableDiv.style.position = 'absolute';
		colorTableDiv.style.background = '#FFFFFF';
		colorTableDiv.style.border = '#7FB9F8 1px solid';
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
				colorTD.id = 'titlecolor_sp_' + arrColors[n][m] ;
				colorTD.className = 'row1';
				var colorDiv = document.createElement('div');
				colorDiv.style.width = '11px';
				colorDiv.style.height = '11px';
				colorDiv.style.border = 'white 1px solid';
				colorDiv.style.fontSize = '1px';
				colorDiv.style.cursor = 'hand';
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
		my_getbyid("title_color").parentNode.appendChild(colorTableDiv);
	}
}

function changeCss1(e) {
	var el;
	if (isIE) el = window.event.srcElement;
	else el = e.target;
	eventid = el.parentNode;
	eventid.style.backgroundColor = 'darkgray' ;
}

function changeCss2(e) {
	var el;
	if (isIE) el = window.event.srcElement;
	else el = e.target;
	eventid = el.parentNode;
	eventid.style.backgroundColor = '';
}

function chTitleColor(e) {
	var el;
	if (isIE) el = window.event.srcElement;
	else el = e.target;
	eventid = el.parentNode;
	var text = eventid.id;
	var color = text.substr(text.lastIndexOf('titlecolor_sp_') + 14);
	var cbutton = my_getbyid("title_color");
	var chidden = my_getbyid("titlecolor");
	if (color == 'X') chidden.value = cbutton.style.backgroundColor = '';
	else chidden.value = cbutton.style.backgroundColor = color;
	closeColorSp();
}

function closeColorSp() {
		my_getbyid("title_color_picker").parentNode.removeChild(my_getbyid("title_color_picker"));
}

function showiconDiv() {
	my_getbyid("div_icon").style.visibility = my_getbyid("div_icon").style.visibility=='visible'?'hidden':'visible';
}

function closeiconDiv() {
	my_getbyid("div_icon").style.visibility = 'hidden';
}

function chicon(iconid, image) {
	my_getbyid("icon_change").src = 'images/icons/'+image;
	my_getbyid("iconid").value = iconid;
	closeiconDiv();
}