<?php
// processCategory stores new or updated categories, and performs deletions as specified by the id parameter
// The item to be operated upon is selected by the id parameter, except for create, where that item is created
// First set up the DB access
/*if (($_SERVER["HTTP_HOST"] == 'asccintranet.bajzek.com')) {			// Set DB by development or production purpose
	$database= "troubles";									// ...for development work
	$username= "prj_bajzek";
	$password= "2725Waterlily?";
} else {
	$database= "troubles";									// ...for production work
	$username= 'asccDBM';										
	$password= '//OgleView15+';
}*/
include('../../dbInclude.php');
$database = 'troubles';

//initialize arguments
if(!isset($_REQUEST['action'])){
	$_REQUEST['action'] = '';
}
if(!isset($_REQUEST['name'])){
	$_REQUEST['name'] = '';
}
if(!isset($_REQUEST['id'])){
	$_REQUEST['id'] = '';
}
if(!isset($_REQUEST['calltype'])){
	$_REQUEST['calltype'] = '';
}
if(!isset($_REQUEST['thisticketcategory'])){
	$_REQUEST['thisticketcategory'] = '';
}

// First fetch the arguments properly
$action=				$_REQUEST['action'];
$name=					$_REQUEST['name'];
$id=					$_REQUEST['id'];
$calltype=				$_REQUEST['calltype'];
$thisticketcategory=	$_REQUEST['thisticketcategory'];

// Next we must retrieve the Category data 
$hostname= 'localhost';
$linkid = mysql_connect($hostname, $username, $password) or die("Connect error- " . mysql_errno() . ": " . mysql_error());
mysql_select_db($database,$linkid);							// Now define the DB to use with this connection

// Now we need to determine what operation is to be done
if ($action == "Create") {
	$qry= "replace into fcategories (name,updated) values(\"$name\",NOW())";			// Create the category record and timestamp it
	$qresult= mysql_query($qry,$linkid) or die("Can't create this category");
}
if ($action == 'Update') {
	$qry= 'update fcategories set name = "' . $name . '" where id = "' . $id . '"';	// Update this category and timestamp it
	if (! isset($id) or empty($id) or $id == '' or strlen($id) == 0) {
		die("ERROR: Can't update category info with an empty category id! Please report this to tom@bajzek.com and file an IT Trouble ticket right away.");
	}	
	$qresult= mysql_query($qry,$linkid) or die("Can't update this category");
}
if ($action == 'Destroy') {
	$qry= 'delete from fcategories where id="' . $id . '"';							// Delete this category
	$qresult= mysql_query($qry,$linkid) or die("Can't delete this category");	
}
// Note: There is no proceessing to be done here for a Show

// Now, we must exit properly
if ($calltype == 'AJAX') {						// If called from doTicket by AJAX to add or edit a category, return a new Select element
	// First, get the list of categories
	$cqry= 'select * from fcategories order by name';
	$cqresult= mysql_query($cqry,$linkid) or die("Can't retrieve categories");
	
// Set the name of the selcted category.  It is $name if creating new, or $thisticketcategory if Canceling the process
	if ($action == "Create") {
		$selectedcategory = $name;
	} else {
		$selectedcategory = $thisticketcategory;
	}

// Now generate a new Category List to repopulate the Select element in the calling routine response div
	print '<p>';
   	print '<select name="category" id="categoryList" onchange= "javascript:checkNewCategory(' . "'show'" . ') "> ' ; // 
	print "<option value='' >Choose a category</option>";
	while ($cptr= mysql_fetch_assoc($cqresult)) {
		if ($cptr['name'] == $selectedcategory) {
			echo '<option value="' . $cptr['name'] . '" selected>' . $cptr['name'] . '</option>';
		} else {
			echo '<option value="' . $cptr['name'] . '">' . $cptr['name'] . '</option>';
		}
	}
	print '<option value= "New Category">New Category</option>';
	print '</select>';
	print "</p>";				
} else {
	header("Location: listfCategories.php");		// If called to create or edit a category, return to listCaterogies
}
?>
