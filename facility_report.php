<?php
// This module is designed to produce a very simplified report for facilities tickets of one selected category, which is specified by the category parameter

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

// First we must set up the DB 
$hostname= 'localhost';
$linkid = mysql_connect($hostname, $username, $password) or die("Connect error- " . mysql_errno() . ": " . mysql_error());
mysql_select_db($database,$linkid);							// Now define the DB to use with this connection

// Now set up the userAttributes DB
$alinkid = mysql_connect($hostname, $username, $password,true) or die("Connect error userAttributes- " . mysql_errno() . ": " . mysql_error());
mysql_select_db('userAttributes',$alinkid);						// Now define the DB to use with this connection

//  First fetch the arguments properly
if(!isset($_REQUEST['category'])){$_REQUEST['category']='';}
$category=	$_REQUEST['category'];

// Next, get the info about the logged-in user
$theUser= $_SERVER['REMOTE_USER'];

$uqry= "select * from roles where email = \"$theUser\"";
$uqresult= mysql_query($uqry,$alinkid) or die("Can't retrieve user info");
$uptr= mysql_fetch_assoc($uqresult);
$facrole= $uptr["facRole"];									// Get the role of the logged-in user

// Now we need to get the ticket data. Techs and Managers see all. Users see only their own tickets.
if ($facrole == 'tech' or $facrole == 'manager') {
	$qry= "select * from ftickets where category = \"$category\" order by status desc, tickettime desc";
} else {
	$qry= "select * from ftickets where category = \"$category\" and email = \"$theUser\" order by status desc, tickettime desc";
}

$qresult= mysql_query($qry,$linkid) or die("Can't retrieve tickets");

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<title>ASCC - Facility Category Report</title>
<style type="text/css" media="screen">
<!--
@import url("../../ascc.css");
-->
</style>
</head>
<body>
<p class="bodycopyheadingbig">ASCC Facility Tracker Category Report for 
<?php
echo date("m/d/y");
?>
</p>
<table class="listdiv" border="0" cellpadding="5" cellspacing="0" width="100%">
<tbody>
<tr class="bodycopy" valign="top">
<th scope="col" width="5%">TID</th>
<th scope="col" width="15%">When</th>
<th scope="col" width="10%">Status</th>
<th scope="col" width="5%">Res.</th>
<th scope="col" width="15%">Originator</th>
<th scope="col" width="10%">Facility</th>
<th scope="col" width="15%">Problem Category</th>
<th scope="col" width="*%" align="left">Description</th>
</tr>
<?php // Now that we have the data, we loop through to display one per line
while ($ptr= mysql_fetch_assoc($qresult)) {
	print '<tr valign="top">';
	$tdate= date('m.d.y:H:i',$ptr["tickettime"]);
	print "<td class='bodycopy' align='center'>";
	print $ptr["id"] . '</td>';
	print "<td class='bodycopy' align='center'>";
	print $ptr["tickettime"] . '</td>';
	if ($ptr["severity"] == 'critical' and $ptr["status"] != 'closed') {
	print "<td class='bodycopyred' align='center'>"; 										// If critical and not closed, display in red
	  } else {
	print "<td class='bodycopy' align='center'>"; 											// Otherwise, display normally
	}
	print $ptr["status"] . '</td>';
	print "<td class='bodycopy' align='center'>";
	print $ptr["responder"] . '</td>';
	print "<td class='bodycopy' align='center'>";
	print $ptr["email"] . '</td>'; print "<td class='bodycopy' align='center'>";
	print $ptr["facility"] . '</td>'; print "<td class='bodycopy' align='center'>";
	print $ptr["category"] . '</td>'; print "<td class='bodycopy' align='left'>";
	print $ptr["description"] . '</td>';
	print '</tr>';
}
?>
</tbody>
</table>
<form action="javascript:printPage()" method="get" target="_self">
<input name="submit" type="submit" value="Print this page" />
</form>
<script type="text/javascript">
function printPage() {
	print();
}	
</script>

</body>
</html>
