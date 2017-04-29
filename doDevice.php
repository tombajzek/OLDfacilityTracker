<?php
// doDevice operates on one selected device, to display or update it. Delete is handled without use of a form.
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
$pageTitle='ASCC Facility Tracker - '.$action . 'Facility';
$pageTitleLong='ASCC Facility Tracker - '.$action . 'Facility';
$needDatatables = false;
$needPrototype = false;
$needMore = false;
$needDatatablesEditor = false;
$needJQDatePicker = false;

// First we need to get the selected device data
$qry= 'select * from fdevices where id = "' . $id . '" order by name';
$qresult= mysql_query($qry,$linkid) or die("Can't retrieve facilities");
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
     <form name="form1" method="post" action="processDevice.php">
				<?php
				// First we must determine the function to be performed
				if ($action == 'New') {				// Display empty form for New
				?>
                   <br />
                   <table width="100%" border="0" cellspacing="5" cellpadding="5">
                     <tr>
                       <th width="36%" class="bodycopyheading" scope="col"><div align="right"><span class="bodycopyheading">Facility name:</span></div></th>
         <th width="64%" scope="col"><div align="left"><span class="bodycopyheading">
                         <input name="devname" type="text" id="name" size="40" maxlength="50">
                       </span></div></th>
                     </tr>
                     <tr>
                       <td class="bodycopyheading"><div align="right">Description:</div></td>
                       <td><div align="left">
                         <label>
                         <input name="description" type="text" id="description" size="40" maxlength="50">
                         </label>
                       </div></td>
                     </tr>
                     <tr>
                       <td class="bodycopyheading"><div align="right">Condition:</div></td>
                       <td><div align="left">
                         <label>
                         <select name="devstatus" id="devstatus">
                         	<option "up">usable</option>
                            <option "down">unusable</option>
                            <option "intermittent">intermittent</option>                            
                         	<option "unknown" selected>unknown</option>
                         </select>
                         </label>
                       </div></td>
                     </tr>
                   </table>
<p>
                     <input type="submit" name="action" id="action" value="Create">
                   </p>
				   <p>
				     <?php
				}
				if ($action == 'Edit') {				// Display populated form for Edit, with an Update button
				?>
				   </p>
				   <table width="100%" border="0" cellspacing="5" cellpadding="5">
                     <tr>
                       <th width="36%" class="bodycopyheading" scope="col"><div align="right">Facility name:</div></th>
           <th width="64%" scope="col"><div align="left"><span class="bodycopyheading">
                           <input name="devname" type="text" id="name" size="40" maxlength="50" value="<?php echo $ptr['name'];?>">
                       </span></div></th>
                     </tr>
                     <tr>
                       <td class="bodycopyheading"><div align="right">Description:</div></td>
                       <td><div align="left">
                           <label>
                           <input name="description" type="text" id="description" size="40" maxlength="50" value="<?php echo $ptr['description'];?>">
                           </label>
                       </div></td>
                     </tr>
                     <tr>
                       <td class="bodycopyheading"><div align="right">Condition:</div></td>
                       <td><div align="left">
                          <select name="devstatus">
                           	<option <?php if ($ptr['devstatus'] == 'usable') {echo 'selected';}?> value='usable'>usable</option>
                           	<option <?php if ($ptr['devstatus'] == 'unusable') {echo 'selected';}?> value='unusable'>unusable</option>
                           	<option <?php if ($ptr['devstatus'] == 'intermittent') {echo 'selected';}?> value='intermittent'>intermittent</option>
                           	<option <?php if ($ptr['devstatus'] == 'unknown') {echo 'selected';}?> value='unknown'>unknown</option>
                           </select>
                       </div></td>
                     </tr>
                   </table>
<p>
                     <input type="hidden" name="id" id="id" value="<?php echo $ptr['id'];?>">
                     <input type="submit" name="action" id="action" value="Update">
                   </p>
				   <p>
				     <?php					
				}
				if ($action == 'Show') {				// Display populated form for Show, but without an pdate button
				?>
				   </p>
				   <table width="100%" border="0" cellspacing="5" cellpadding="5">
                     <tr>
                       <th width="36%" class="bodycopyheading" scope="col"><div align="right">Facility name:</div></th>
                   <th width="64%" class="bodycopy" scope="col"><div align="left">
                   		<label>
                           <?php echo $ptr['name'];?>                        </label>
                       </div></th>
                     </tr>
                     <tr>
                       <td class="bodycopyheading"><div align="right">Description:</div></td>
                       <td class="bodycopy"><div align="left">
                           <label>
                           <?php echo $ptr['description'];?>                           </label>
                       </div></td>
                     </tr>
                     <tr>
                       <td class="bodycopyheading"><div align="right">Condition:</div></td>
                       <td class="bodycopy"><div align="left">
                           <label>
                           <?php echo $ptr['devstatus'];?>                           </label>
                       </div></td>
                     </tr>
                   </table>
	    <p>                   </p>
				<?php					
				}
				?>
                 </form>
                 <div align="right"><a href="listfDevices.php" target="_self" class="bodycopyLink"><u>List Facilities</u></a>                  </div>
      <!-- InstanceEndEditable -->
<?php 
include('../Templates/pageTemplateBottom.php');
 ?>