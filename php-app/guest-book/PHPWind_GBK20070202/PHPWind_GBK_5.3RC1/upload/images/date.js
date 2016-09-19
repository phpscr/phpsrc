var months = new Array("һ��", "����", "����", "����", "����", "����", "����", "����", "����", "ʮ��", "ʮһ��", "ʮ����"); 
var days   = new Array(31,28,31,30,31,30,31,31,30,31,30,31);
var weeks  = new Array("��","һ","��","��","��","��","��");
var today;
var pX;
var pY;

document.writeln("<div id='Calendar' style='position:absolute; z-index:1; visibility: hidden;'></div>");

function getDays(month,year){
    if (1 == month){
        return ((0 == year % 4) && (0 != (year % 100))) || (0 == year % 400) ? 29 : 28;
    }else{
        return days[month];
	}
}
function getToday(){
    var date  = new Date();
    this.year = date.getFullYear();
    this.month= date.getMonth();
    this.day  = date.getDate();
}
function getSelectDay(str){
    var str=str.split("-");
    
    var date  = new Date(parseFloat(str[0]),parseFloat(str[1])-1,parseFloat(str[2]));
    this.year = date.getFullYear();
    this.month= date.getMonth();
    this.day  = date.getDate();
}

function ShowDays() {
	var obj_Year =get_object('Year');
	var obj_Month=get_object('Month');

    var parseYear = parseInt(obj_Year.options[obj_Year.selectedIndex].value);
    var Seldate = new Date(parseYear,obj_Month.selectedIndex,1);
    var day = -1;
    var startDay = Seldate.getDay();
    var daily = 0;
    
    if ((today.year == Seldate.getFullYear()) &&(today.month == Seldate.getMonth())){
        day = today.day;
	}
    var tableDay = get_object('Day');
    var DaysNum =getDays(Seldate.getMonth(),Seldate.getFullYear());
    for (var intWeek = 1;intWeek < tableDay.rows.length;intWeek++){
        for (var intDay = 0;intDay < tableDay.rows[intWeek].cells.length;intDay++){
            var cell = tableDay.rows[intWeek].cells[intDay];
            if ((intDay == startDay) && (0 == daily)){
                daily = 1;
			}
                
            if(day==daily){
                cell.style.background='#6699CC';
                cell.style.color='#FFFFFF';
            } else if(intDay==6){
                cell.style.color='green';
			} else if (intDay==0){
                cell.style.color='red';
			}
            
            if ((daily > 0) && (daily <= DaysNum)){
				cell.innerHTML = daily;
                daily++;
            } else{
				cell.style.cssText = '';
                cell.innerHTML = '';
			}
        }
	}
}

function GetDate(idname,e){
    var sDate;
	var getElement = is_gecko ? e.target : event.srcElement;
    if (getElement.tagName == "TD"){
        if(getElement.innerHTML != ""){
            sDate = get_object('Year').value + "-" + get_object('Month').value + "-" + getElement.innerHTML;
            get_object(idname).value=sDate;
            HiddenCalendar();
        }
	}
} 

function HiddenCalendar(){
    get_object('Calendar').style.visibility='hidden';
}

function ShowCalendar(idname){
    var x,y,i,intWeeks,intDays;
    var table;
    var year,month,day;
    var obj=get_object(idname);
    var thisyear;
    
    thisyear=new Date();
    thisyear=thisyear.getFullYear();
    
    today = obj.value;
    if(isDate(today)){
        today = new getSelectDay(today);
	}else{
        today = new getToday();
	}
    
    x=obj.offsetLeft;
    y=obj.offsetTop;
    while(obj=obj.offsetParent){
        x+=obj.offsetLeft;
        y+=obj.offsetTop;
    }
	var Cal=get_object('Calendar');
    Cal.style.left=x+2+'px';
    Cal.style.top=y+20+'px';
    Cal.style.visibility="visible";
    
    table="<table border='0' cellspacing='0' style='border:1px solid #0066FF; background-color:#FFFFFF'>";
    table+="<tr>";
    table+="<td style='border-bottom:1px solid #0066FF; background-color:#84AACE'>";
    
    table+="<select name='Year' id='Year' onChange='ShowDays()' style='font-family:Verdana; font-size:12px'>";
    for(i = thisyear - 35;i < (thisyear + 5);i++){ 
        table+="<option value=" + i + " " + (today.year == i ? "Selected" : "") + ">" + i + "</option>"; 
	}
	table+="</select>";

    table+="<select name='Month' id='Month' onChange='ShowDays()' style='font-family:Verdana; font-size:12px'>";
    for(i = 0;i < months.length;i++){
        table+="<option value= " + (i + 1) + " " + (today.month == i ? "Selected" : "") + ">" + months[i] + "</option>";
	}

	table+="</select>";
    table+="</td>";
    table+="<td style='border-bottom:1px solid #0066FF; background-color:#84AACE; font-weight:bold; font-family:Wingdings 2,Wingdings,Webdings; font-size:16px; padding-top:2px; color:#4477FF; cursor:hand' align='center' title='�ر�' onClick='javascript:HiddenCalendar()'>S</td>";
    table+="</tr>";
    table+="<tr><td align='center' colspan='2'>";
    table+="<table id='Day' border='0' width='100%'>";
    table+="<tr>";

    for(i = 0;i < weeks.length;i++){
        table+="<td align='center' style='font-size:12px;'>" + weeks[i] + "</td>";
	}
	table+="</tr>";

    for(intWeeks = 0;intWeeks < 6;intWeeks++){
        table+="<tr>";
        for (intDays = 0;intDays < weeks.length;intDays++){
            table+="<td onClick='GetDate(\"" + idname + "\",event)' style='cursor:pointer;border-right:1px solid #BBBBBB; border-bottom:1px solid #BBBBBB; color:#215DC6; font-family:Verdana; font-size:12px' align='center'></td>";
		}
        table+="</tr>";
    }
    table+="</table></td></tr></table>";

    Cal.innerHTML=table;
    ShowDays();
}

function isDate(dateStr){
    var datePat = /^(\d{4})(\-)(\d{1,2})(\-)(\d{1,2})$/;
    var matchArray = dateStr.match(datePat);
    if (matchArray == null) return false;
    var month = matchArray[3];
    var day = matchArray[5];
    var year = matchArray[1];
    if (month < 1 || month > 12) return false;
    if (day < 1 || day > 31) return false;
    if ((month==4 || month==6 || month==9 || month==11) && day==31) return false;
    if (month == 2){
        var isleap = (year % 4 == 0 && (year % 100 != 0 || year % 400 == 0));
        if (day > 29 || (day==29 && !isleap)) return false;
    }
    return true;
}
function get_object(idname){
	if (document.getElementById){
		return document.getElementById(idname);
	}else if (document.all){
		return document.all[idname];
	}else if (document.layers){
		return document.layers[idname];
	}else{
		return null;
	}
}