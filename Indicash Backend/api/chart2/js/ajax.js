function genericAjaxFunction(queryString,outputDiv,recallInterval)
{
	var xmlHttp;
	try
	{
		xmlHttp=new XMLHttpRequest();
	}
	catch (e)
	{
	try
	{
		xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
	}
	catch (e)
	{
		try
		{
			xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
		}
		catch(e)
		{
			alert("your browser doesnot support ajax");
			return false;
		}
	}
	}
	xmlHttp.onreadystatechange=function()
	{
		if(xmlHttp.readyState==4)
		{
		document.getElementById(outputDiv).innerHTML=xmlHttp.responseText;
		if(recallInterval>0)
			setTimeout("genericAjaxFunction( '"+queryString+"', '"+outputDiv+"', '"+recallInterval+"')",recallInterval);
		}
	}
	xmlHttp.open("POST",queryString,true);
	xmlHttp.send(null);
};