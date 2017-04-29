<?php
// processTicket stores new or updated tickets, and performs deletions as specified by the id parameter
// The item to be operated upon is selected by the id parameter, except for create, where that item is created
// processTicket also sends email to the appropriate managers to alert them with ticket info
// First set up the DB access
/*if (($_SERVER["HTTP_HOST"] == 'asccintranet.bajzek.com')) {		// Set DB by development or production purpose
	$database= "troubles";										// ...for development work
	$username= "prj_bajzek";
	$password= "2725Waterlily?";
	$recipient= 'tom@bajzek.com';								// Default, overwritten later
	$sender= 'thomas@bajzek.com';
	$domain= '@bajzek.com';
} else {
	$database= "troubles";										// ...for production work
	$username= 'asccDBM';										
	$password= '//OgleView15+';
	$recipient= 'FacilityTechs@asccinc.com';					// Default, overwritten later
	$sender= 'tickets@asccnet.com';
	$domain= '@asccinc.com';
}*/

include('../../dbInclude.php');
include('recipientInclude.php');

$database = 'troubles';


//initialize arguments
if(!isset($_REQUEST['id'])){
	$_REQUEST['id'] = '';
}
if(!isset($_REQUEST['action'])){
	$_REQUEST['action'] = '';
}
if(!isset($_REQUEST['description'])){
	$_REQUEST['description'] = '';
}
if(!isset($_REQUEST['category'])){
	$_REQUEST['category'] = '';
}
if(!isset($_REQUEST['email'])){
	$_REQUEST['email'] = '';
}
if(!isset($_REQUEST['status'])){
	$_REQUEST['status'] = '';
}
if(!isset($_REQUEST['facility'])){
	$_REQUEST['facility'] = '';
}
if(!isset($_REQUEST['devstatus'])){
	$_REQUEST['devstatus'] = '';
}
if(!isset($_REQUEST['severity'])){
	$_REQUEST['severity'] = '';
}
if(!isset($_REQUEST['tickettime'])){
	$_REQUEST['tickettime'] = '';
}

// First fetch arguments proplery
$id=			$_REQUEST['id'];
$action=		$_REQUEST['action'];
$description=	addslashes($_REQUEST['description']);
$category=		$_REQUEST['category'];
$email=			$_REQUEST['email'];
$status=		$_REQUEST['status'];
$facility=		$_REQUEST['facility'];
$devstatus=		$_REQUEST['devstatus'];
$severity=		$_REQUEST['severity'];
$tickettime=	$_REQUEST['tickettime'];

// First fetch the logged-in user
$REMOTE_USER= $_SERVER['REMOTE_USER'];

// Next we must set up to retrieve the ticket data 
$hostname= 'localhost';
$linkid = mysql_connect($hostname, $username, $password) or die("Connect error- " . mysql_errno() . ": " . mysql_error());
mysql_select_db($database,$linkid);								// Now define the DB to use with this connection

// Now set up the userAttributes DB
$alinkid = mysql_connect($hostname, $username, $password,true) or die("Connect error userAttributes- " . mysql_errno() . ": " . mysql_error());
mysql_select_db('userAttributes',$alinkid);						// Now define the DB to use with this connection

// Get the info about the logged-in user
$uqry= "select * from roles where email = '$REMOTE_USER'";
$uqresult= mysql_query($uqry,$alinkid) or die("Can't retrieve user info");
$uptr= mysql_fetch_assoc($uqresult);
$facrole= $uptr['facRole'];										// Get the role of the logged-in user

// Now fetch the techs as they are the recipients of the notification email
$rqry= "select email from roles where facRole = 'tech'";	
$rresult= mysql_query($rqry,$alinkid) or die("Can't retrieve techs!");

// Now build the email list of techs
$recipient= '';
while ($rptr= mysql_fetch_assoc($rresult)) {
	$recipient.= $rptr['email'];
	if (strpos($rptr['email'],'@') === false) {$recipient.= $domain;}
	$recipient.= ',';
}

if (strlen($recipient) > 13) {
	$recipient= substr($recipient,0,strlen($recipient) - 1);	// Trim the trailing comma
} else {
	die("Techs missing or malformed!");							// Result too short for our email address
}


// Now we need to determine what operation is to be done
if ($action == "Create") {
	
	// // If a tech creates a ticket, set its status to 'open,' but otherwise, it will be 'unseen'
	// if ($facrole == 'tech') {
	// 	$ticketstatus= 'open';
	// } else {
	// 	$ticketstatus= 'unseen';
	// }

	// As of request on may2511, a new ticket is to have status='open' no matter who creates it
	$ticketstatus= 'open';	
	
	$qry= "insert into ftickets (updated,description,category,email,status,facility,devstatus,severity,tickettime) 
	values('',\"$description\",\"$category\",\"$email\",\"$ticketstatus\",\"$facility\",\"$devstatus\",\"$severity\",NOW())";	// Create the ticket record and timestamp its creation (tickettime) with current time
	$qresult= mysql_query($qry,$linkid) or die("Can't create this ticket");
	
//  Now retrieve the id of the new ticket for the alert email
	$ticketNo= mysql_insert_id($linkid);
	
// 	Now notify techs of this new ticket
	$ticketnotice=  "$REMOTE_USER has created a new ticket\n";
	$ticketnotice.= "Category: $category\n";
	$ticketnotice.= "Severity: $severity\n";
	$ticketnotice.= "Description:\n\n";
	$ticketnotice.= $description;
	mail($recipient,"New ticket alert: Ticket=" . $ticketNo,$ticketnotice,"From: $sender\r\ncc:$REMOTE_USER" . $domain);
}
if ($action == 'Update') {										// See if we are closing a critical ticket
	if ($status == 'closed' and $severity == 'critical') {
		$severity= 'ex-critical';								// If so, set it to 'ex-critical'
	}
	$qry= 'update ftickets set description = "' . $description . 
	'", category= "' . $category .
	'", responder= "'. $responder . 
	'", email= "'. $email . 
	'", status= "' . $status .
	'", facility= "' . $facility . 
	'", devstatus= "' . $devstatus . 
	'", severity= "' . $severity .
	'" where id = "' . $id . '"';								// Update this ticket (and timestamp it)
//	print 'query=' . $qry;
if (! isset($id) or empty($id) or $id == '' or strlen($id) == 0) {
	die("ERROR: Can't update Facility ticket info with an empty ticket id! Please report this to tom@bajzek.com and file an IT Trouble ticket right away.");
}	
	$qresult= mysql_query($qry,$linkid) or die("Can't update this ticket");
}
if ($action == 'Destroy') {
	$qry= 'delete from ftickets where id="' . $id . '"';		// Delete this ticket
	if (! isset($id) or empty($id) or $id == '' or strlen($id) == 0) {
		die("ERROR: Can't delete Facility ticket info with an empty ticket id! Please report this to tom@bajzek.com and file an IT Trouble ticket right away.");
	}	
	$qresult= mysql_query($qry,$linkid) or die("Can't delete this ticket");	
}
// Note: There is no proceessing to be done here for a Show

// In all cases, we return to the List page
header("Location: listfTickets.php");
?>
