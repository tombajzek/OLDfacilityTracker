<?php
// doCategory operates on one selected category, to display or update it. Delete is handled without use of a form.
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

// First we must retrieve the survey data 
$hostname= 'localhost';
$linkid = mysql_connect($hostname, $username, $password) or die("Connect error- " . mysql_errno() . ": " . mysql_error());
mysql_select_db($database,$linkid);							// Now define the DB to use with this connection

//initialize parameters
if(!isset($_REQUEST['id'])){
	$_REQUEST['id'] = '';
}
if(!isset($_REQUEST['action'])){
	$_REQUEST['action'] = '';
}

// Now get the parameters
$id=		$_REQUEST['id'];
$action=	$_REQUEST['action'];

//define title for page template
$templatePath="../";
$pageTitle='ASCC Facility Tracker - '.$action . 'Category';
$pageTitleLong='ASCC Facility Tracker - '.$action . 'Category';
$needDatatables = false;
$needPrototype = false;
$needMore = false;
$needDatatablesEditor = false;
$needJQDatePicker = false;

// Now we need to get the selected category data
$qry= 'select * from fcategories where id = "' . $id . '" order by name';
$qresult= mysql_query($qry,$linkid) or die("Can't retrieve categories");
$ptr= mysql_fetch_assoc($qresult);							// Get the specified record

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
                 
                 <form name="form1" method="post" action="processCategory.php">
                   <?php
				// First we must determine the function to be performed
				if ($action == 'New') {				// Display empty form for New
				?>
                   <br />
                   <table width="100%" border="0" cellspacing="5" cellpadding="2">
                     <tr>
                       <th width="36%" scope="col"><div align="right"><span class="bodycopyheading">Category name:</span></div></th>
                       <th width="64%" scope="col"><div align="left"><span class="bodycopyheading">
                           <input name="name" type="text" size="30" maxlength="50">
                       </span></div></th>
                     </tr>
                   </table>
                   <p class="bodycopyheading">&nbsp;</p>
                   <p>
                     <input type="submit" name="action" id="action" value="Create">
                   </p>
				   <p>
				     <?php
				}
				if ($action == 'Edit') {				// Display populated form for Edit, with an Update button
				?>
			       </p>
				   <table width="100%" border="0" cellpadding="2" cellspacing="5">
                     <tr>
                       <th width="36%" scope="col"><div align="right"><span class="bodycopyheading">Category name:</span></div></th>
                       <td width="64%" scope="col"><div align="left"><span class="bodycopy">
                           <input name="name" type="text" size="30" maxlength="50" value="<?php echo $ptr['name'];?>">
                       </span></div></td>
                     </tr>
                   </table>
				   <p>&nbsp;</p>
				   <p>
                     <input type="hidden" name="id" id="id" value="<?php echo $ptr['id'];?>">
                     <input type="submit" name="action" id="action" value="Update">
                   </p>
				<?php					
				}
				if ($action == 'Show') {				// Display populated form for Show, but without an pdate button
				?>
                	<br />
	                   <table width="100%" border="0" cellpadding="2" cellspacing="5">
                         <tr>
                           <th width="36%" scope="col"><div align="right"><span class="bodycopyheading">Category name:</span></div></th>
                           <th width="64%" scope="col"><div align="left"><span class="bodycopy">
                               <?php echo $ptr['name'];?>
                           </span></div></th>
                         </tr>
                       </table>
	                   <p class="bodycopy">&nbsp;</p>
				<?php					
				}
				?>
                 </form>
                 <div align="right"><a href="listfCategories.php" target="_self" class="bodycopyLink"><u>List Categories</u></a>                  </div>
      <!-- InstanceEndEditable -->
<?php 
include('../Templates/pageTemplateBottom.php');
 ?>