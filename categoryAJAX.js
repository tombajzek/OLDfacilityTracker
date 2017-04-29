// JavaScript Document
//Event.observe(window,'load',init,false);

function saveCategory(Act,previouscategory)
{
	// alert("saveCategory " + thisticketcategory);
	//alert('saveCategory cancel=' + previouscategory + '|');
var thisaction = 'Create';	
	 if (Act == 'cancel') { thisaction = "Cancel"};
	
		var url= 'processCategory.php'
		var pars= {calltype:'AJAX', name: $F('categoryname'), action: thisaction, thisticketcategory: previouscategory};
		var target= 'categorylist';
		var myAjax= new Ajax.Updater(target,url,{method: 'get', parameters: pars});
	doNewCategory("hide");
}