<?php
// processDevice stores new or updated devices, and performs deletions as specified by the id parameter
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
if(!isset($_REQUEST['devname'])){
	$_REQUEST['devname'] = '';
}
if(!isset($_REQUEST['type'])){
	$_REQUEST['type'] = '';
}
if(!isset($_REQUEST['description'])){
	$_REQUEST['description'] = '';
}
if(!isset($_REQUEST['devstatus'])){
	$_REQUEST['devstatus'] = '';
}
if(!isset($_REQUEST['calltype'])){
	$_REQUEST['calltype'] = '';
}
if(!isset($_REQUEST['thisticketdevice'])){
	$_REQUEST['thisticketdevice'] = '';
}
if(!isset($_REQUEST['id'])){
	$_REQUEST['id'] = '';
}

//  First fetch the arguments properly
$action=			$_REQUEST['action'];
$name=				$_REQUEST['devname'];
$type=				$_REQUEST['type'];
$description=		$_REQUEST['description'];
$devstatus=			$_REQUEST['devstatus'];
$calltype=			$_REQUEST['calltype'];
$thisticketdevice=	$_REQUEST['thisticketdevice'];
$id=				$_REQUEST['id'];

// Next we must retrieve the Device data 
$hostname= 'localhost';
$linkid = mysql_connect($hostname, $username, $password) or die("Connect error- " . mysql_errno() . ": " . mysql_error());
mysql_select_db($database,$linkid);							// Now define the DB to use with this connection

// Now we need to determine what operation is to be done
if ($action == "Create") {
	$qry= "insert into fdevices (name,type,description,devstatus,updated) 
	values(\"$devname\",\"$type\",\"$description\",\"$devstatus\",\"NOW()\")";		// Create the device record and timestamp it
	$qresult= mysql_query($qry,$linkid) or die("Can't create this facility");
}
if ($action == 'Update') {
	$qry= 'update fdevices set name = "' . $devname . 
	'", type= "' . $type .
	'", description= "'. $description . 
	'", devstatus= "' . $devstatus . 
	'", updated= NOW() where id = "' . $id . '"';									// Update this device and timestamp it
if (! isset($id) or empty($id) or $id == '' or strlen($id) == 0) {
	die("ERROR: Can't update facility info with an empty facility id! Please report this to tom@bajzek.com and file an IT Trouble ticket right away.");
}	
	$qresult= mysql_query($qry,$linkid) or die("Can't update this facility");
}
if ($action == 'Destroy') {
	$qry= 'delete from fdevices where id="' . $id . '"';							// Delete this device
	$qresult= mysql_query($qry,$linkid) or die("Can't delete this facility");	
}
// Note: There is no proceessing to be done here for a Show

// Now, we must exit properly
if ($calltype == 'AJAX') {						// If called from doTicket by AJAX to add or edit a category, return a new Select element
	// First, get the list of devices
	$dqry= 'select * from fdevices order by name';
	$dqresult= mysql_query($dqry,$linkid) or die("Can't retrieve facilities");
	
	// Set the name of the selcted category.  It is $name if creating new, or $thisticketcategory if Canceling the process
if ($action == "Create") {
	$selecteddevice = $devname;
} else {
	$selecteddevice = $thisticketdevice;
}

						
// Now generate a new Device List to repopulate the Select element in the calling routine response div
	print '<p>';
   	print '<select name="facility" id="facility" onchange= "javascript:checkNewDevice(' . "'show'" . ') "> ' ; // 
	print "<option value='' selected>Choose a facility</option>";
	while ($dptr= mysql_fetch_assoc($dqresult)) {
		if ($dptr['name'] == $selecteddevice) {
			echo '<option value="' . $dptr['name'] . '" selected>' . $dptr['name'] . '</option>';
		} else {
			echo '<option value="' . $dptr['name'] . '">' . $dptr['name'] . '</option>';
		}
	}
	print '<option value= "New Facility">New Facility</option>';
	print '</select>';
	print "</p>";				
} else {										// Not called via AJAX
	header("Location: listfDevices.php");		// If called to create or edit a device, return to listDevices
}
?>
