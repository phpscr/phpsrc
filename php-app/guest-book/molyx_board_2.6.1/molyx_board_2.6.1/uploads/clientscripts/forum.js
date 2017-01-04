function forum_toggle_tid( tid )
{
	saved = new Array();
	clean = new Array();
	add   = 1;
	tmp = document.modform.t.value;
	saved = tmp.split(",");
	for( i = 0 ; i < saved.length; i++ )
	{
		if ( saved[i] != "" )
		{
			if ( saved[i] == tid )
			{
				 add = 0;
			}
			else
			{
				clean[clean.length] = saved[i];
			}
		}
	}
	if ( add )
	{
		clean[ clean.length ] = tid;
		eval("document.img"+tid+".src=selectedbutton");
	}
	else
	{
		eval(" document.img"+tid+".src=unselectedbutton");
	}
	document.modform.t.value = clean.join(',');
	newcount = stacksize(clean);
	document.modform.gobutton.value = lang_gobutton + '(' + newcount + ')';
	return false;
}