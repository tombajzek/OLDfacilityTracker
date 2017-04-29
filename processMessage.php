<?php
// processMessage stores new or updated messages, and performs deletions as specified by the id parameter
// The item to be operated upon is selected by the id parameter, except for create, where that item is created
// First set up the DB access
/*if (($_SERVER["HTTP_HOST"] == 'asccintranet.bajzek.com')) {			// Set DB by development or production purpose
	$database= "troubles";									// ...for development work
	$username= "prj_bajzek";
	$password= "2725Waterlily?";
	$recipient= 'tom@bajzek.com';
	$sender= 'thomas@bajzek.com';
} else {
	$database= "troubles";									// ...for production work
	$username= 'asccDBM';										
	$password= '//OgleView15+';
	$recipient= 'facilityTechs@asccinc.com';
	$sender= 'tickets@asccnet.com';
}*/
include('../../dbInclude.php');
include('recipientInclude.php');

$database = 'troubles';

//initialize arguments
if(!isset($_REQUEST['id'])){
	$_REQUEST['id'] = '';
}
if(!isset($_REQUEST['resolved'])){
	$_REQUEST['resolved'] = '';
}
if(!isset($_REQUEST['action'])){
	$_REQUEST['action'] = '';
}
if(!isset($_REQUEST['email'])){
	$_REQUEST['email'] = '';
}
if(!isset($_REQUEST['content'])){
	$_REQUEST['content'] = '';
}
if(!isset($_REQUEST['calltype'])){
	$_REQUEST['calltype'] = '';
}

// First fetch the arguments properly
$id=		$_REQUEST['id'];
$resolved=	$_REQUEST['resolved'];
$action=	$_REQUEST['action'];
$email=		$_REQUEST['email'];
$content=	$_REQUEST['content'];
$calltype=	$_REQUEST['calltype'];

// First fetch the logged-in user
$REMOTE_USER= $_SERVER['REMOTE_USER'];

// Next we must retrieve the Messages data 
$hostname= 'localhost';
$linkid = mysql_connect($hostname, $username, $password) or die("Connect error- " . mysql_errno() . ": " . mysql_error());
mysql_select_db($database,$linkid);							// Now define the DB to use with this connection

// Now we need to fetch the ticket to see if it's critical to handle that properly
$query= 'select * from ftickets where id=' . $id;			// Fetch the ticket
$result= mysql_query($query,$linkid) or die("Can't retrieve ticket");
$ptr= mysql_fetch_assoc($result);

// Now see if this is the resolution for the ticket, and, if so, whether the ticket had severity='critical'
if ($resolved != 'yes') {									// If "yes" is checked it is
	$resolved= 'no';										// Otherwise it's unresolved
} else {
	$critupdate= '';										// Update severity iff severity is 'critical'
	if ($ptr[severity] == 'critical') {
		$critupdate= ', severity= "ex-critical" ';			// If it was 'critical' make it 'ex-critical'
	}
  	$cquery= 'update ftickets set status="closed" where id=' . $id;
//	print $cquery;
if (! isset($id) or empty($id) or $id == '' or strlen($id) == 0) {
	die("ERROR: Can't update message info with an empty message id! Please report this to tom@bajzek.com and file an IT Trouble ticket right away.");
}	
  	$cresult= mysql_query($cquery,$linkid) or die("Can't update ticket status:");	// Close this ticket if this message says it has been resolved 
}

// Now we need to determine what operation is to be done

	if ($action == "Create") {
		$qry= "replace into fmessages (id,email,ticket,content,resolved,datetime) values(0,\"$email\",\"$id\",\"$content\",\"$resolved\",NOW())";	// Create the message record and timestamp it
		$qresult= mysql_query($qry,$linkid) or die("Can't create this message");
	// 	Now notify techs of this new message
			$messagenotice= "$REMOTE_USER has created a new message for ticket $id";
	//		mail($recipient,"New message alert",$messagenotice,"From: $sender\r\ncc:$REMOTE_USER");		// Do not send email notification
	}

	if ($calltype == 'AJAX') {						// If called from doTicket by AJAX to add a message, return a new messagelist
		// First, get the list of messages
		$mqry= 'select * from fmessages where ticket=' . $id . ' order by datetime desc';
		$mqresult= mysql_query($mqry,$linkid) or die("Can't retrieve messages");
	
		// Here we need to output the table header for the messagelist
		print '<div id="messagelist">';
		print '<table width="100%" border="0" cellspacing="5" cellpadding="2">';
		print '<tr>';
			print '<th width="20%" class="bodycopyheading" scope="col"><div align="left">When</div></th>';
			print '<th width="20%" class="bodycopyheading" scope="col"><div align="left">Who</div></th>';
			print '<th width="*%" class="bodycopyheading" scope="col"><div align="left">Content</div></th>';
		print '</tr>';
						
		// Now generate a new Message List to repopulate the messagelist in the calling routine response div
		while ($mptr= mysql_fetch_assoc($mqresult)) {
			print '<tr>';
			print "<td class='bodycopy' >" . $mptr['datetime'] . "</td>";
			print "<td class='bodycopy' >" . $mptr['email'] . "</td>";
			if ($mptr['resolved'] == 'yes') {
				print "<td class = 'bodycopyheading' >";				// Make the resolution big and bold
			} else {
				print "<td class = 'bodycopy' >";
			}
			print $mptr['content'] . "</td>";
			print '</tr>';												// Add a newline to make the page source readable
		}
		print '</table>';
		print '</div>';
	} else {
		header("Location: listfTickets.php");							// If called to create or edit a message, return to listfTickets
	}
																		// If "cancel" there is nothing to do
?>
