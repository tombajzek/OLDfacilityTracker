<?PHP
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

//define title for page template
$templatePath="../";
$pageTitle='ASCC Faciity Tracker - List Facilities';
$pageTitleLong='ASCC Facility Tracker - List Facilities';
$needDatatables = false;
$needPrototype = false;
$needMore = false;
$needDatatablesEditor = false;
$needJQDatePicker = false;

// First we must retrieve the survey data 
$hostname= 'localhost';
$linkid = mysql_connect($hostname, $username, $password) or die("Connect error- " . mysql_errno() . ": " . mysql_error());
mysql_select_db($database,$linkid);							// Now define the DB to use with this connection

// First we need to get the device data
$qry= 'select * from fdevices order by name';
$qresult= mysql_query($qry,$linkid) or die("Can't retrieve facilities");

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
    <br />
   <table width="100%" border="0" cellspacing="5" cellpadding="5">
                   <tr class="bodycopyheading">
                     <th width="30%" align="right" scope="col"><div align="right">Facility Name</div></th>
                     <th width= "*" align="right" scope="col"><div align="left">Description</div></th>
                     <th width="30%" align="left" scope="col">Condition</th>
                     <th width="5%" align="left" scope="col">&nbsp;</th>
                     <th width="5%" align="left" scope="col">&nbsp;</th>
                     <th width="5%" align="left" scope="col">&nbsp;</th>
                   </tr>
<?php
// Now that we have the data, we loop through to display one per line
while ($ptr= mysql_fetch_assoc($qresult)) {
	print '<tr>';
		print "<td class='bodycopy' align='right'>";
		print $ptr['name'] . '</td>';
		print "<td class='bodycopy' align='left'>";
		print $ptr['description'] . '</td>';
		print "<td class='bodycopy' align='left'>";
		print $ptr['devstatus'] . '</td>';		
		print '<td class="bodycopylinksmall"><a href="doDevice.php?action=Show&id=' . $ptr['id'] . '">' . '<u>Show</u>' . '</a></td>';
		print '<td class="bodycopylinksmall"><a href="doDevice.php?action=Edit&id=' . $ptr['id'] . '">' . '<u>Edit</u>' . '</a></td>';
  		print '<td class="bodycopylinksmall"><a href="processDevice.php?action=Destroy&id=' . $ptr['id'] . "\" onClick=\"return confirm('Click OK to delete or click Cancel')\">" . '<u>Destroy</u>' . '</a></td>';
//	print <<<confirmDelete
//	<td class="bodycopylinksmall">
//	<a href="processDevice.php?action=Destroy&id=$ptr['id']" onclick="return confirm('Click OK to delete or click Cancel')"><u>Destroy</u></a>
//	</td> 
//confirmDelete;
		print '</tr>';
}
?>
                 </table>
	  <p>&nbsp;</p>
	  <table width="100%" border="0" cellspacing="2" cellpadding="2">
        <tr>
          <th width="33%" scope="col"><div align="left" class="bodycopyLink"><a href="listfCategories.php" target="_self"><u>List Categories</u></a></div></th>
          <th width="34%" scope="col"><div align="center" class="bodycopyLink"><a href="listfTickets.php" target="_self"><u>List Tickets</u></a></div></th>
          <th width="33%" scope="col"><a href="doDevice.php?action=New" class="bodycopyLink"><u>New Facility</u></a></th>
        </tr>
      </table>
	  <p><u></u></p>
    <!-- InstanceEndEditable -->
<?php 

include('../Templates/pageTemplateBottom.php');

 ?>