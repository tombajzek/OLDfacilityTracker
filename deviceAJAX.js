// JavaScript Document
//Event.observe(window,'load',init,false);

function saveDevice(Act,previousdevice)
{
	// alert('saveDevice= ' + Act );
	var thisaction = 'Create';	
	if (Act == 'cancel') { thisaction = "Cancel"};
	var url= 'processDevice.php';
	var pars= {calltype:'AJAX', 
	devname: $F('devname'), 
	description: $F('description'),
	devstatus: $F('devstatus'),
	action: thisaction,thisticketdevice: previousdevice};
	var target= 'devicelist';
	var myAjax= new Ajax.Updater(target,url,{method: 'get', parameters: pars});
	doNewDevice("hide");
}