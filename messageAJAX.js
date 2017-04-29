// JavaScript Document
//Event.observe(window,'load',init,false);

function saveMessage(Act,tid)
{
	// alert('saveMessage Act=  + Act');
	if (Act == 'Create') {
		var url= 'processMessage.php';
		var pars= {calltype:'AJAX', 
		email: $F('email'), 
		content: $F('content'),
		id: tid,
		resolved: $F('resolved'),
		action: 'Create'};	
		var target= 'messagelist';	
		var myAjax= new Ajax.Updater(target,url,{method: 'get', parameters: pars});
	}
	doNewMessage("hide");
}