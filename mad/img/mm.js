var count = 1

function MM_findObj(n, d)	{ //v4.0
	var p,i,x;	if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
	d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
	if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
	for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
	if(!x && document.getElementById) x=document.getElementById(n); return x;
}
	
function MM_changeProp(objName,x,theProp,theValue)	{ //v3.0
	var obj = MM_findObj(objName);
	if (obj && (theProp.indexOf("style.")==-1 || obj.style)) eval("obj."+theProp+"='"+theValue+"'");
}

function readCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}


function confirmDelete()	{
	return confirm('Вы собираетесь навсегда удалить этот объект. Нажмите ОК если это так.');
}

function deleteCookie(name) {
	
	document.cookie = name +
	'=; expires=Thu, 01-Jan-70 00:00:01 GMT;';
} 

function stateChanged() 
{ 

	if (xmlHttp.readyState==4 || xmlHttp.readyState=="complete")
	   { 
		var id = readCookie('id')
		var option = readCookie('option')
		setTimeout("document.getElementById('loading').style.display='none';",1000);

deleteCookie('id')
deleteCookie('option')

	   }

}

function ChangeHiddenFieldValue(fieldon) {

	if (document.getElementById('label'+fieldon).value == 0)
	{
		document.getElementById('label'+fieldon).value = 1
	}
		else document.getElementById('label'+fieldon).value = 0

}

function ChangeValue(option, value, id, table)
{

	// 31.10.2006 added table var in order to change values in different sections (tables)
	
	xmlHttp=GetXmlHttpObject()

	document.cookie = "id="+id
	document.cookie = "option="+option

	if (value.length==0)
	{ 

	alert("Changed")
	return
	}
	xmlHttp=GetXmlHttpObject()
	if (xmlHttp==null)
	{
	alert ("Browser does not support HTTP Request")
	return
	}

	var url="/mad/"+table+"/changevalue/"+id
	url=url+"?"+option+"="+value
	url=url+"&sid="+Math.random()
	xmlHttp.onreadystatechange=stateChanged
	xmlHttp.open("POST",url,true)
	xmlHttp.send()
	document.getElementById("loading").style.display="block"



} 



function GetXmlHttpObject()
{ 
	var objXMLHttp=null
	if (window.XMLHttpRequest)
	  {
	  objXMLHttp=new XMLHttpRequest()
	  }
	else if (window.ActiveXObject)
	  {
	  objXMLHttp=new ActiveXObject("Microsoft.XMLHTTP")
	  }
	return objXMLHttp
} 


function MassChangeValue(table, column, value)
{

	xmlHttp=GetXmlHttpObject()

	if (value.length==0)
		{ 
			alert("Changed")
			return
		}
			xmlHttp=GetXmlHttpObject()
			if (xmlHttp==null)
		{
			alert ("Browser does not support HTTP Request")
			return
		}

	var url="/mad/pages/changecolumns/?"
	url=url+column+"="+value
	url=url+"&sid="+Math.random()
	xmlHttp.onreadystatechange=stateChanged
	xmlHttp.open("POST",url,true)
	xmlHttp.send()
	document.getElementById("loading").style.display="block"

}

function id(x)
{
    return document.getElementById(x);
}



function toggle_visibility_old(ident) {

	ide = id(ident);

		if(ide.style.display == '') ide.style.display = 'none';
		else ide.style.display = '';

}

function toggle_visibility(ident) {

	var ide = id(ident);
	var children = ide.childNodes;

		if(ide.style.display == '') {
			
			ide.style.display = 'none';

			for (var i = 0; i < children.length; i++) { 

				children[i].disabled = true
					
				}
			}

		else {
			
			ide.style.display = '';
			for (var i = 0; i < children.length; i++) { 
				
				children[i].disabled = false
				
				}

		}



}


var checkflag = "false";

function setCheckboxesByType(do_check) {
    var elts = document.getElementsByTagName("input");
    var elts_cnt  = (typeof(elts.length) != 'undefined')
                  ? elts.length
                  : 0;
 if (elts_cnt) {
        for (var i = 0; i < elts_cnt; i++) {
            if (elts[i].type == "checkbox") {
                elts[i].checked = do_check;
            }
        } 
    } else {
        if (elts.type == "checkbox") {
            elts.checked = do_check;
        }
    }
}

function ShowSelectTagFor(option, id) {
	document.getElementById(option+ "select" +id).style.display= 'block';
	document.getElementById(option+id).style.display= 'none';
}


function ClipBoard(url,name) {
	url = '<a href="'+url+'">'+name+'</a>'
	clipboardData.setData('Text',url); 
}

function ShowChangeForm(id) {
	document.getElementById("priority"+id).style.display = "none";
	document.getElementById("prioritychangeform"+id).style.display = "block";
}

function MakeChanesAndSave(table,id,value,section) {
	ChangeValue(table,value,id,section);
	id("prioritychangeform"+id).style.display = "none";
	id("priority"+id).style.display = "block";
	id("priority"+id).innerHTML = value	
}

function MakeChanesAndSaveCheckBox(table,id,value,section,cb) {

	cb.checked ? value=1 : value=0
	ChangeValue(table,value,id,section);

/* 	document.getElementById("prioritychangeform"+id).style.display = "none";
	document.getElementById("priority"+id).style.display = "block";
	document.getElementById("priority"+id).innerHTML = value	*/
}

function changeVisibleURL()
{
    var txt = document.forms['0'].page_path.value;
	id("visibleURL").innerHTML = txt;
}

function SetThisPageAsDefault() {

	if (id('page_path').style.display == 'none')
	{
		id('page_path').style.display = 'inline';
		id('InnerLink').innerHTML = ('Cделать страницу <B id="red">заглавной</B> в выбранном разделе');
		document.forms[0].page_path.select();
	}

	else {

		document.forms[0].page_path.value=('default');
		id('visibleURL').innerHTML = ('default'); 
		id('page_path').style.display = 'none';
		id('InnerLink').innerHTML = ('<B id="red">Отменить</b> режим заглавной страницы для выбранного раздела');

	}

	return false;

}

function ShowUploadForm(where,filegroupid) {

	count++
	id(where).innerHTML = id(where).innerHTML+"<br><input type='file' name='uploadfile[" + filegroupid +"][" + count + "]'>"

}

// Выполнение запроса AJAX
function RunAJAX(url) {

	url=url+"&sid="+Math.random()

	// alert(url)

	xmlHttp.onreadystatechange=stateChanged
	xmlHttp.open("POST",url,true)
	xmlHttp.send()

}


// Подтверждение и последующее удаление файла, прикрепленного к заказу на перевод
function unlinkfile(filename, fileid, showuploadform)
{

	// 31.10.2006 added table var in order to change values in different sections (tables)
	
	xmlHttp=GetXmlHttpObject()

	confirmDelete();

	if (xmlHttp==null)
	{
	alert ("Browser does not support HTTP Request")
	return
	}
	
	RunAJAX("/mad/ajax.php?action=unlinkfile&filename="+filename+"&fileid="+fileid)

	document.getElementById("fileid"+fileid).style.display = "none";

}

function insertCurrDate(timestampa) {
	id("labeltimestamp").value = timestampa
}

function ChangeValueOfFormElement(val) {

	document.forms[0].sql.value = val

}