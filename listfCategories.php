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
$pageTitle='ASCC List Categories';
$pageTitleLong='ASCC List Categories';
$needDatatables = false;
$needPrototype = false;
$needMore = false;
$needDatatablesEditor = false;
$needJQDatePicker = false;

// First we must retrieve the survey data 
$hostname= 'localhost';
$linkid = mysql_connect($hostname, $username, $password) or die("Connect error- " . mysql_errno() . ": " . mysql_error());
mysql_select_db($database,$linkid);							// Now define the DB to use with this connection

// First we need to get the category data
$qry= 'select * from fcategories order by name';
$qresult= mysql_query($qry,$linkid) or die("Can't retrieve categories");

// Now fetch the arguments properly
if(!isset($_REQUEST['action'])) {$_REQUEST['action']='';}
$action=	$_REQUEST['action'];

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
                     <th width="64%" align="right" scope="col">Category Name</th>
                     <th width="12%" align="left" scope="col">&nbsp;</th>
                     <th width="12%" align="left" scope="col">&nbsp;</th>
                     <th width="12%" align="left" scope="col">&nbsp;</th>
                   </tr>
<?php

// Now that we have the data, we loop through to display one per line
while ($ptr= mysql_fetch_assoc($qresult)) {
	if ($ptr['name'] != ' New Category') {				// Don't present the New Category selection for Show or Edit
		print '<tr>';
		print "<td class='bodycopy' align='right'>";
		print $ptr['name'] . '</td>';
		print '<td class="bodycopylinksmall"><a href="doCategory.php?action=Show&id=' . $ptr['id'] . '">' . '<u>Show</u>' . '</a></td>';
		print '<td class="bodycopylinksmall"><a href="doCategory.php?action=Edit&id=' . $ptr['id'] . '">' . '<u>Edit</u>' . '</a></td>';
//		print '<td class="bodycopylinksmall"><a href="processCategory.php?action=Destroy&id=' . $ptr[id] . '" onClick="destroyOK()">' . '<u>Destroy</u>' . '</a></td>';
		print <<<confirmDelete
		<td class="bodycopylinksmall">
		<a href="processCategory.php?action=Destroy&id=$ptr[id]" onclick="return confirm('Click OK to delete or click Cancel')"><u>Destroy</u></a>
		</td> 
confirmDelete;
		print '</tr>';
	}
}
?>
                 </table>
				   <table width="100%" border="0" cellspacing="2" cellpadding="2">
                     <tr>
                       <th width="33%" scope="col"><a href="listfDevices.php" target="_self" class="bodycopyLink"><u>List Facilities</u></a></th>
                       <th width="34%" scope="col"><a href="listfTickets.php" target="_self" class="bodycopyLink"><u>List Tickets</u></a></th>
                       <th width="33%" scope="col"><a href="doCategory.php?action=New" target="_self" class="bodycopyLink"><u>New Category</u></a></th>
                     </tr>
                   </table>              
      <!-- InstanceEndEditable -->
<?php 

include('../Templates/pageTemplateBottom.php');

 ?>