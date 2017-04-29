<?php
// First set up the DB access
/*if (($_SERVER["HTTP_HOST"] == 'asccintranet.bajzek.com')) {			// Set DB by development or production purpose
	$database= "troubles";											// ...for development work
	$username= "prj_bajzek";
	$password= "2725Waterlily?";
	$recipient= 'tom@bajzek.com';
} else {
	$database= "troubles";											// ...for production work
	$username= 'asccDBM';										
	$password= '//OgleView15+';
	$recipient= 'facilityTechs@asccinc.com';
}*/

include('../../dbInclude.php');
include('recipientInclude.php');
$database = 'troubles';

//initialize arguments
if(!isset($_REQUEST['chronorder'])){
	$_REQUEST['chronorder'] = '';
}
if(!isset($_REQUEST['statusorder'])){
	$_REQUEST['statusorder'] = '';
}

//define title for page template
$templatePath="../";
$pageTitle='ASCC Facility Tracker - List Tickets';
$pageTitleLong='ASCC Facility Tracker - List Tickets';
$needDatatables = true;
$needPrototype = false;
$needMore = true;
$needDatatablesEditor = false;
$needJQDatePicker = false;


// First we must set up the DB 
$hostname= 'localhost';
$linkid = mysql_connect($hostname, $username, $password) or die("Connect error- " . mysql_errno() . ": " . mysql_error());
mysql_select_db($database,$linkid);											// Now define the DB to use with this connection

// Now set up the userAttributes DB
$alinkid = mysql_connect($hostname, $username, $password,true) or die("Connect error userAttributes- " . mysql_errno() . ": " . mysql_error());
mysql_select_db('userAttributes',$alinkid);							// Now define the DB to use with this connection

// Next, get the info about the logged-in user
$theUser= $_SERVER['REMOTE_USER'];

// Now set the default page_length,  max age, and role for displaying items per page
$page_length= 10;
$maxAge= '7 day';
$facRole= 'user';

$facPageLen= 10;

// Here we need to set up a table to map age terms to number of days for use in computing age intervals for retrieving closed tickets
$ageMap= array(
	"1 week" => "7 day",
	"1 month" => "1 month",
	"1 quarter" => "91 day",
	"1 half-year" => "183 day",
	"1 year" => "1 year",
	"all" => "all"
	);

// Now fetch the arguments properly
if (isset($_REQUEST['chronorder'])) {$chronorder= $_REQUEST['chronorder'];}
if (isset($_REQUEST['statusorder'])) {$statusorder= $_REQUEST['statusorder'];}
if (isset($_REQUEST['catorder'])) {$catorder= $_REQUEST['catorder'];}
if (isset($_REQUEST['from'])) {$from= $_REQUEST['from'];}
if (isset($_REQUEST['thru'])) {$thru= $_REQUEST['thru'];}
if (isset($_REQUEST['email'])) {$email= $_REQUEST['email'];}
if (isset($_REQUEST['responder'])) {$responder= $_REQUEST['responder'];}
if (isset($_REQUEST['facility'])) {$facility= $_REQUEST['facility'];}
if (isset($_REQUEST['category'])) {$category= $_REQUEST['category'];}
if (isset($_REQUEST['status'])) {$status= $_REQUEST['status'];}
if (isset($_REQUEST['pageno'])) {$pageno= $_REQUEST['pageno'];}

$uqry= 'select roles.facRole,roles.email,preferences.* from roles,preferences where roles.email = "' . $theUser . '" and preferences.email = "' . $theUser .'"';
$uqresult= mysql_query($uqry,$alinkid) or die("Can't retrieve user info");
$uptr= mysql_fetch_assoc($uqresult);
$facrole= $uptr["facRole"];													// Get the role of the logged-in user

// error_log('theUser=' . $theUser . ' facRole=' . $facrole . ' email=' . $email);

if (isset($uptr['facRole'])) {$facRole= $uptr['facRole'];}					// Set to the User Preference itfacRole, if specified 
if (isset($uptr['facPageLen']) and $uptr['facPageLen'] > 0) {$page_length= $uptr['facPageLen'];}		// Set to the User Preference page length, if specified
if (isset($uptr['facMaxAge'])) {$maxAge= $ageMap[$uptr['facMaxAge']];}			// Set to the User Preference max age, if specified
if ($page_length == 'all') {$page_length= 1000000;}							// A big enough number for $page_length will always give 1 page, so do it

// error_log('Quattro lT: maxAge= ' . $uptr['facMaxAge']);

// First, see if we are here to filter records for our retrieval
// Start with a selection string containing excluding closed tickets older than the default 1 week
$selectionString= '';

if ($uptr['facRole'] != 'tech' and $uptr['facRole'] != 'manager') {$selectionString= 'email = "' . $theUser . '" and ';}

// Now examine each possible filter selector, and add it if it is used
if (isset($from) and $from !='' and $from != '-No selection-') {$selectionString.= 'tickettime >="' . $from .'" and ';}
if (isset($thru) and $thru !='' and $thru != '-No selection-'){$selectionString.= 'tickettime <="' . $thru .'" and ';}
if (isset($email) and $email != '-No selection-') {$selectionString.= 'email="' . $email . '" and ';}
if (isset($responder) and $responder != '-No selection-') {$selectionString.= 'responder="' . $responder .'" and ';} 
if (isset($facility) and $facility != '-No selection-') {$selectionString.= 'facility="' . $facility .'" and ';}
if (isset($category) and $category != '-No selection-') {$selectionString.= 'category="' . $category .'" and ';}
if (isset($status) and $status != '-No selection-'){$selectionString.= 'status="' . $status .'" and ';}

// Now see if have a trailing '. ', and remove it if we do, yielding the selectionString that we need
if (strlen($selectionString) > 0) {$selectionString= substr_replace($selectionString,'',-5,5);}

// If we have a selectionString, put the 'where ' in front of it
// if (strlen($selectionString) != 0){
//	$selectionString= ' where ' . $selectionString;
// }

// Now set the sort order for retrieving ftickets, defaulting to descending status and descending tickettime
if ($chronorder == '') {													// If no $chronorder param, default to desc
	$chronorder= 'desc';													// Otherwise, use the param
}

// Now set the sort order for retrieving ftickets, defaulting to descending status and descending tickettime
if ($statusorder == '') {													// If no $statusorder param, default to desc
	$statusorder= 'desc';													// Otherwise, use the param
}

// Now we need to get the ticket data. Techs and Managers see all. Users see only their own tickets.
// if ($facrole == 'tech' or $facrole == 'manager') {
// 	$qry= "select * from ftickets order by $catsearch status $statusorder, tickettime $chronorderr";
// } else {
// 	$qry= "select * from ftickets where email = \"$theUser\" order by $catsearch status $statusorder, tickettime $chronorder";
// }

// Now build the query, including closed tickets only within one week, and adding filtering parameters if needed

// Now inspect whether $from or $thru have been set. If not, apply $maxAge interval from Preferences; otherwise ignore $maxAge, as it has been overridden
// print '<br />maxAge=' . $maxAge . '<br />';

// error_log('Quattro lT: pre-qry= ' . $maxAge);

if ((!isset($from) or $from !='') and (!isset($thru) or $thru != '') and $maxAge != 'all') {
	$interval= ' and tickettime > date_sub(now(),interval ' . $maxAge . ')';
} else {
	$interval= '';
}

// Now build the query, including closed tickets only within $maxAge, and adding filtering parameters if needed
if (strlen($selectionString) > 0) {
	$pageqry= "select * from ftickets where (" . $selectionString . " and status !='closed') or (" . $selectionString . $interval . ") order by status $statusorder, severity asc, tickettime $chronorder";
} else {
	$pageqry= "select * from ftickets where status !='closed' or (status = 'closed' " . $interval . ") order by status $statusorder, severity asc, tickettime $chronorder";
}

 error_log('Filter=' . $pageqry);

$qpresult= mysql_query($pageqry,$linkid) or die("Can't retrieve number of tickets");
$rows= mysql_num_rows($qpresult);											// Save the number of tickets we retrieved

if (!isset($pageno)) {
	$pageno=1;																// if no page number is set, start at 1
}
$page_count= ceil($rows / $page_length);													// See how many pages we need to display
$offset= ($pageno - 1) * $page_length;														// Start reading records at this offset
$limit= ' limit ' . $page_length;													// Begin to build our limit phrase
if ($offset != 0) {
	$limit.= ' offset ' . $offset;											// Specify an offset if need be
}
// $limit.= $page_length;												// Retrieve in chunks of $page_length

// error_log('Quattro lT: page_length= ' . $page_length . ', page_count= ' . $page_count . ', offset= ' . $offset);

// Everyone sees all tickets, so get the ticket data for the page to be displayed
// Build the query correctly depending on whether there is a selection or not

// Now build the query, including closed tickets only within one week, and adding filtering parameters if needed

// Now inspect whether $from or $thru have been set. If not, apply $maxAge interval from Preferences; otherwise ignore $maxAge, as it has been overridden
if ((!isset($from) or $from !='') and (!isset($thru) or $thru != '') and $maxAge != 'all') {
	$interval= ' and tickettime > date_sub(now(),interval ' . $maxAge . ')';
} else {
	$interval= '';
}

// Now build the query, including closed tickets only within one week, and adding filtering parameters if needed
if (strlen($selectionString) > 0) {
	$qry= "select * from ftickets where (" . $selectionString . " and status !='closed') or (" . $selectionString . $interval . ") order by status $statusorder, severity asc, tickettime $chronorder" . ' '. $limit;
} else {
	$qry= "select * from ftickets where status !='closed' or (status = 'closed'" . $interval . ") order by status $statusorder, severity asc, tickettime $chronorder" . $limit;
}

error_log('Quattro lT: qry= ' . $qry);

$qresult= mysql_query($qry,$linkid) or die("Can't retrieve tickets");

// Here we retrieve the data from the DB tables to populate the filter selectors
$catquery= "select distinct category from ftickets where category is not null and category !='' order by category asc";
$catresult= mysql_query($catquery,$linkid) or die("Can't get category info");

$emquery= "select distinct email from ftickets order by email asc";
$emresult= mysql_query($emquery,$linkid) or die("Can't get originator info");

$statusquery= "select distinct status from ftickets where status is not null and status !='' order by status desc";
$statusresult= mysql_query($statusquery,$linkid) or die("Can't get status info");

// NOTE: ON Dec 2, 2009, at Lynn's request, Facilities, known in the code as 'fdevices' have been eliminated, and this field now is known as '% Completed', although the field name remains as 'facility'
// The links to 'List Facilities', listfdevices.php, and dofDevices.php have been eliminated as it makes no sense to enter a table of completion %

$facquery= "select distinct facility from ftickets where facility is not null and facility !='' order by facility asc";
$facresult= mysql_query($facquery,$linkid) or die("Can't get facility info");

$respquery= "select distinct responder from ftickets where responder is not null and responder !='' order by responder asc";
$respresult= mysql_query($respquery,$linkid) or die("Can't get responder info");

//-------------------------------------begin header and template inclusion

echo('<html lang="en"><head>');

include('../Templates/pageTemplateHeader.php');

//-------------------------------------any additions to the header go here
?>

<?php

echo('</head>');

include('../Templates/pageTemplateTop2017.php');

//----------------------------------------------end header and template

?>
    <!-- InstanceBeginEditable name="PageContent" -->
		<form action="listfTickets.php" method="get" name="FilterForm" target="_self">
	<div align="right">
<?php
// Here we build the page query string whether or not this result requires pagination
$pageQueryBase= '<a href= "listfTickets.php?';								// Start with the page URL, and append the selection parameters
if (isset($from)) {$pageQueryBase.= "from=" . $from . '&';}
if (isset($thru)) {$pageQueryBase.= "thru=" . $thru . '&';}
if (isset($tag)) {$pageQueryBase.= "tag=" . $tag . '&';}
if (isset($status)) {$pageQueryBase.= "status=" . $status. '&';}
if (isset($email)) {$pageQueryBase.= "email=" . $email . '&';}
if (isset($responder)) {$pageQueryBase.= "responder=" . $responder . '&';}
if (isset($category)) {$pageQueryBase.= "category=" . $category . '&';}

// print 'pageQueryBase=' . $pageQueryBase . 'test';

// Now deal with the issue of displaying the page or pages required
if ($page_count > 1) {print 'Page#: ';}
for ($pagenumber= 1; $pagenumber <= $page_count; $pagenumber++){			// Loop for each page
	if ($page_count > 1) {													// We need to write page navigation links
		if ($pagenumber != $pageno) {
			$pageQueryString= $pageQueryBase;																//Make a link for each page, with filter params
			$pageQueryString.= '&chronorder=' . $chronorder . '&statusorder=' . $statusorder . '&';	// Preserve the sort order
			$pageQueryString.= 'pageno=' . $pagenumber . '">' . $pagenumber . '</a> ';	// End link for non-current page
		} else {
			$pageQueryString= $pagenumber . ' ';														// No link for the current page
		}																																	// End null link for current page
		print $pageQueryString;																						// Display link for this page
	}																																		// End test for multiple pages
}																																			// End loop thru pages
print "&nbsp;&nbsp;&nbsp;&nbsp;";																			// Make space before New Ticket link
?>
  <a href="doTicket.php?action=New" target="_self" class="bodycopyLink"><u>New Ticket</u></a>
	</div>
  <div align="right">
      <a href="../editPrefs.php?type=Facility&id=<?php echo $uptr['id'];?>" target="_blank" class="bodycopyLink"><u>Edit Tracker Preferences for Facilities</u></a>  </div> 
  <script type="text/javascript" src="../A.js"></script>
  <link href="../A.css" rel="stylesheet" media="screen" />
<div id="AccordionContainer" class="AccordionContainer">
	<div class="AccordionTitle"  onClick="runAccordion(1)" onselectstart="return false">
		<b>click to FILTER or PRINT</b>
	</div>
	<div id="Accordion1Content" class="AccordionContent">
	    <!-- <div class="AccordionButton" id="peer">Peer Assessment</div> -->
		<table class="Filter" bgcolor="#DDDBFE"  border="1" cellpadding="5" cellspacing="0" width="600" >
			<tr height=24 bgcolor="#DDDBFE"  class="bodycopy" valign="top">
			  <td width="20" bgcolor="#DDDBFE" >From</td>
				<td width="100" height="24"  >
					<input type="text" name="from" class="datePicker" id="from" value=
					<?php 
						if (!isset($from)) {
							echo '"' . '-No selection-' . '"';
						} else {
							echo '"' . $from . '"';
						}
					?>
					/>
   				</td>
				<td width="20" bgcolor="#DDDBFE">Originator</td>
				<td width="100" height="24" >
				  <select  name="email" id="email">
                	<option>-No selection-</option>
					<?php
						while ($ptr= mysql_fetch_assoc($emresult)) {
							echo "<option";
							if ($email == $ptr['email']) {echo ' selected';}		// Display the selection, if any
							echo '>' . $ptr['email'] . '</option>';
						}
					?>
				  </select>
				</td>
              </tr>
			<tr bgcolor="#DDDBFE">
				<td class="bodycopy">Through</td><td>
					<input type="text" name="thru" class="datePicker" id="thru" value=
					<?php 
						if (!isset($thru)) {
							echo '"' . '-No selection-' . '"';
						} else {
							echo '"' . $thru . '"';
						}
					?>
					/>
                </td>
                <td valign="top" class="bodycopy">Responder</td>
                <td valign="top" class="bodycopy">
				  <select name="responder" id="responder">
                	<option>-No selection-</option>
					<?php
						while ($ptr= mysql_fetch_assoc($respresult)) {
							echo "<option";
							if ($responder == $ptr['responder']) {echo ' selected';}	// Display the selection, if any
							echo '>' . $ptr['responder'] . '</option>';
						}
					?>
                 </select>
                </td>
              </tr>
			<tr bgcolor="#DDDBFE">
			  <td valign="top" class="bodycopy">% Complete</td>
			  <td valign="top" class="bodycopy">
				<select name="facility" id="facility">
                	<option>-No selection-</option>
				<?php
					while ($ptr= mysql_fetch_assoc($facresult)) {
						echo "<option";
						if ($facility == $ptr['facility']) {echo ' selected';}			// Display the selection, if any
						echo '>' . $ptr['facility'] . '</option>';
					}
				?>                </select>              </td>
			  <td class="bodycopy"> Category </td>
			  <td>
				<select name="category" id="category">
                	<option>-No selection-</option>
				<?php
					while ($ptr= mysql_fetch_assoc($catresult)) {
						echo "<option";
						if ($category == $ptr['category']) {echo ' selected';}			// Display the selection, if any
						echo '>' . $ptr['category'] . '</option>';
					}
				?>
                </select>
              </td>
			</tr>
			<tr bgcolor="#DDDBFE">
			  <td valign="top" class="bodycopy">Status</td>
			  <td valign="top" class="bodycopy">
			    <select name="status" id="status">
                	<option>-No selection-</option>
				<?php
			while ($ptr= mysql_fetch_assoc($statusresult)) {
				echo "<option";
				if ($status == $ptr['status']) {echo ' selected';}			// Display the selection, if any
				echo '>' . $ptr['status'] . '</option>';
			}
				?>
                </select>
			  </td>
			  <td>
			    <input type="button" name="print" onclick='window.print()' value="Print page">
              </td>
			  <td><input name="Go" id="Go" type="submit" value="Apply filter" /></td>
			  </tr>
		</table>
	</div>
</div> <!--close AccordionContent -->
		</form>    
<table class="listdiv" border="0" cellpadding="5" cellspacing="0" width="100%">
<tbody>
<tr class="bodycopy" valign="top">
<th scope="col" width="4%">TID</th>
<th scope="col" width="15%">
<!-- <a href="listfTickets.php? -->
			<?php
				if($pageQueryBase != '') {echo $pageQueryBase;}										// If we have a filter, apply it
				echo "statusorder=" . $statusorder . '&';										// Now set the link to flip the chronorder parameter and preserve the statusorder
				if ($chronorder != 'asc') {
					echo 'chronorder=asc">When</a><img src="../images/sort_up.gif" width="12" height="20" alt="sort up" align="absmiddle">';
				} else {
					echo 'chronorder=desc">When</a><img src="../images/sort_down.gif" width="12" height="20" alt="sort down" align="absmiddle">';  
				}
			?>
</th>
<th scope="col" width="10%">
<!-- <a href="listfTickets.php? -->
			<?php
				if($pageQueryBase != '') {echo $pageQueryBase;}										// If we have a filter, apply it
				echo "chronorder=" . $chronorder . '&';											// Here, preserve the chronorder parameter and flip the statusorder
				if ($statusorder != 'asc') {
					echo 'statusorder=asc">Status</a><img src="../images/sort_up.gif" width="12" height="20" alt="sort up" align="absmiddle">';
				} else {
					echo 'statusorder=desc">Status</a><img src="../images/sort_down.gif" width="12" height="20" alt="sort down" align="absmiddle">';  
				}
			?>
			</th>
			<th scope="col" width="4%">Res.</th>
			<th scope="col" width="15%">Originator</th>
			<th scope="col" width="11%">% Complete /Facility Info</th>
			<th scope="col" width="13%">Problem Category</th>
			<th scope="col" width="*%">Description</th>
			<th width="4%">&nbsp;</th>
			<th width="4%">&nbsp;</th>
		</tr>
			<?php // Now that we have the data, we loop through to display one per line
			// If pagination is in effect, we display the retrieved tickets in pages of size=$page_length

			while ($ptr= mysql_fetch_assoc($qresult)) {
				print '<tr valign="top">';
				$tdate= date('m-d-y:H:i',$ptr["tickettime"]);
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
				if ($theUser == $ptr["email"]) {
					print "<td class='bodycopyblue' align='center'>";									// User's ticket=> blue
				} else {
					print "<td class='bodycopy' align='center'>";										// Not the user => black
				}
				print $ptr["email"] . '</td>'; print "<td class='bodycopy' align='center'>";
				print $ptr["facility"] . '</td>'; print "<td class='bodycopy' align='center' nowrap>";
				print $ptr["category"] . '</td>'; print "<td class='bodycopy' align='left'>";
				print $ptr["description"] . '</td>'; 
				print '<td class="asccnoprint"><a href="doTicket.php?action=Show&id=' . $ptr["id"] . '">' . '<u>Show</u>' . '</a></td>';
				// Note immediately below: Users can edit only their own unseen tickets. Only techs can edit tickets that have been seen.
				if ($facrole == 'tech' or ($ptr["status"] == 'unseen' and $facrole != 'manager' and $theUser == $ptr["email"])) {
					print '<td class="asccnoprint"><a href="doTicket.php?action=Edit&id=' . $ptr["id"] . '">' . '<u>Edit</u>' . '</a></td>'; } else {
					print '<td>&nbsp;</td>';
				}
				print '</tr>';
			}
			?>

	</tbody>
</table>
<br>
<table border="0" cellpadding="2" cellspacing="5" width="100%">
	<tbody>
		<tr>
		<?php 
		if ($facrole == 'manager' or $facrole == 'tech') { // Allow managers & techs only to deal with Categories & Devices
			print <<<Links
				<th width="33%" align="left"><a href="listfCategories.php" target="_self" class="bodycopyLink"><u>List Categories</u></a></th>
				<th width="34%" align="center"><a href="listfDevices.php" target="_self" class="bodycopyLink"><u>List Facilities</u></a></th>
Links;
			} else {
			print '<th width="33%>&nbsp;</th><th width="34%>&nbsp;</th>';
			}
			?>
			<th align="right" width="33%"><a href="doTicket.php?action=New" target="_self" class="bodycopyLink"><u>New Ticket</u></a></th>
		</tr>
	</tbody>
</table>
<!-- InstanceEndEditable -->
<?php

include('../Templates/pageTemplateBottom.php');

?>
