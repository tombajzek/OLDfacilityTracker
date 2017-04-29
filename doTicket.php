<?php
// doticket operates on one selected ticket, to display or update it. Delete is handled without use of a form.
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

// Note that we depend on $REMOTE_USER to know who is logged-in and what that person's role (and privilege level) is
$REMOTE_USER= $_SERVER['REMOTE_USER'];

// First we must retrieve the survey data 
$hostname= 'localhost';
$linkid = mysql_connect($hostname, $username, $password) or die("Connect error- " . mysql_errno() . ": " . mysql_error());
mysql_select_db($database,$linkid);							// Now define the DB to use with this connection

// Now set up the userAttributes DB
$alinkid = mysql_connect($hostname, $username, $password,true) or die("Connect error userAttributes- " . mysql_errno() . ": " . mysql_error());
mysql_select_db('userAttributes',$alinkid);						// Now define the DB to use with this connection

//initialize arguments
if (!isset($_REQUEST['id'])) {$_REQUEST['id']='';}
if (!isset($_REQUEST['action'])) {$_REQUEST['action']='';}
if (!isset($_REQUEST['description'])) {$_REQUEST['description']='';}
if (!isset($_REQUEST['category'])) {$_REQUEST['category']='';}
if (!isset($_REQUEST['email'])) {$_REQUEST['email']='';}
if (!isset($_REQUEST['status'])) {$_REQUEST['status']='';}
if (!isset($_REQUEST['devstatus'])) {$_REQUEST['devstatus']='';}
if (!isset($_REQUEST['severity'])) {$_REQUEST['severity']='';}
if (!isset($_REQUEST['facility'])) {$_REQUEST['facility']='';}
if (!isset($_REQUEST['responder'])) {$_REQUEST['responder']='';}

// Next we fetch arguments properly
if (isset($_REQUEST['id'])) {$id= $_REQUEST['id'];}
if (isset($_REQUEST['action'])) {$action= $_REQUEST['action'];}
if (isset($_REQUEST['description'])) {$description= $_REQUEST['description'];}
if (isset($_REQUEST['category'])) {$category= $_REQUEST['category'];}
if (isset($_REQUEST['email'])) {$email= $_REQUEST['email'];}
if (isset($_REQUEST['status'])) {$status= $_REQUEST['status'];}
if (isset($_REQUEST['devstatus'])) {$devstatus= $_REQUEST['devstatus'];}
if (isset($_REQUEST['severity'])) {$severity= $_REQUEST['severity'];}
if (isset($_REQUEST['facility'])) {$facility= $_REQUEST['facility'];}
if (isset($_REQUEST['responder'])) {$responder= $_REQUEST['responder'];}

//define variables for page template
$templatePath="../";
$pageTitle='ASCC Facility Tracker - '.$action . 'Ticket';
$pageTitleLong='ASCC Facility Tracker - '. $action . ' Ticket';
$needDatatables = false;
$needPrototype = false;
$needMore = false;
$needDatatablesEditor = false;
$needJQDatePicker = false;

// First we need to get the selected ticket data
$qry= 'select * from ftickets where id = "' . $id . '"';
$qresult= mysql_query($qry,$linkid) or die("Can't retrieve tickets");
$ptr= mysql_fetch_assoc($qresult);	
//print "thisticketcategory=" . $thisticketcategory;

// Get the list of categories
$cqry= 'select * from fcategories order by name';
$cqresult= mysql_query($cqry,$linkid) or die("Can't retrieve categories");
//print mysql_num_rows($cqresult);

// Get the list of devices
$dqry= 'select * from fdevices order by name';
$dqresult= mysql_query($dqry,$linkid) or die("Can't retrieve facilities");

// New get the messages associated with this ticket, unless we are creating a new ticket
if ($action != 'New') {
$mqry= "select * from fmessages where ticket = " . $id . " order by datetime desc";
$mqresult= mysql_query($mqry,$linkid) or die("Can't retrieve messages");
}

// Get the info about the logged-in user
$uqry= "select * from roles where email = '$REMOTE_USER'";
// $uqry= "select * from users where email = 'tom@bajzek.com'";
$uqresult= mysql_query($uqry,$alinkid) or die("Can't retrieve user info");
$uptr= mysql_fetch_assoc($uqresult);
$facrole= $uptr['facRole'];														// Get the role of the logged-in user

function makeCategoryList($result,$tptr,$act) {
	print '<select name="category" id="category" onchange= "javascript:checkNewCategory(' . "'show'" . ') "> ' ;
	if ($act == 'Create') { 											// Choose category if new, otherwise select current value
		print "<option value='' selected>Choose a category</option>";
	} else {
		print "<option value=''>Choose a category</option>";
	}	
	while ($cptr= mysql_fetch_assoc($result)) {
		if ($cptr['name'] == $tptr['category']) {
			echo '<option value="' . $cptr['name'] . '" selected>' . $cptr['name'] . '</option>';
		} else {
			echo '<option value="' . $cptr['name'] . '">' . $cptr['name'] . '</option>';
		}
	}
	
	print '<option value= "New Category">New Category</option>';
	print '</select>';
	print '<script> var thisticketcategory = "' . $tptr['category'] . '"</script>';
}

// NOTE: ON Dec 2, 2009, at Lynn's request, Facilities, known in the code as 'fdevices' have been eliminated, and this field now is known as '% Completed', although the field name remains as 'facility'
// The links to 'List Facilities', listfdevices.php, and dofDevices.php have been eliminated as it makes no sense to enter a table of completion %

// NOTE: On Dec 11, 2009, again at Lynn's request the above changes were mostly reversed, except for some column headings, which have been changed to '% Completed / Facility Info'

// The links previouosly eliminated have been restored

function makeDeviceList($result,$tptr,$act) {
	print '<select name="facility" id="facility" onchange= "javascript:checkNewDevice(' . "'show'" . ') "> ' ; // 
	if ($act == 'Create') { 											// Choose device if new, otherwise select current value
		print "<option value='' selected>Choose a facility</option>";
	} else {
		print "<option value=''>Choose a facility</option>";
	}	
	while ($dptr= mysql_fetch_assoc($result)) {
		if ($dptr['name'] == $tptr['facility']) {
			echo '<option value="' . $dptr['name'] . '" selected>' . $dptr['name'] . '</option>';
		} else {
			echo '<option value="' . $dptr['name'] . '">' . $dptr['name'] . '</option>';
		}
	}
	print '<option value= "New Facility">New Facility</option>';
	print '</select>';
	print '<script> var thisticketdevice = "' . $tptr['facility'] . '"</script>';
}

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
<style type="text/css">
	#create {
		padding: 2px;  
	}
	#newCategory {
	width: 160px; /* 160 + 18 + 2 = 180 */
	border-style: groove;
	border-width: medium;
	padding: 10px 10px 10px 10px;
	display: none;
	/*	
	padding: 2px;
	position: absolute;
	top: 307px;
	right: 210px;
	height: 22px;
	*/
	float: right;
	/* the width of the sidebar considers the sidebar padding
	and because of a calculation bug in IE5 Mac, also the
	borders on the links in the navbar */
	}
	#newDevice {
	width: 160px; /* 160 + 18 + 2 = 180 */
	border-style: groove;
	border-width: medium;
	padding: 10px 10px 10px 10px;
	display: none;
	/*	
	padding: 2px;
	position: absolute;
	top: 307px;
	right: 210px;
	height: 22px;
	*/
	float: right;
	/* the width of the sidebar considers the sidebar padding
	and because of a calculation bug in IE5 Mac, also the
	borders on the links in the navbar */
	}
	#newMessage {
	width: 160px; /* 160 + 18 + 2 = 180 */
	border-style: groove;
	border-width: medium;
	padding: 10px 10px 10px 10px;
	display: none;
	/*	
	padding: 2px;
	position: absolute;
	top: 307px;
	right: 210px;
	height: 22px;
	*/
	float: right;
	/* the width of the sidebar considers the sidebar padding
	and because of a calculation bug in IE5 Mac, also the
	borders on the links in the navbar */
	}
</style>

<?php
include($templatePath . 'Templates/jsInclude.php');
?>


<script language="JavaScript" type="text/JavaScript">
function doNewCategory(action)
{
	// alert("doNewCategory called for " + action);
	var elem, vis;
	if (action == 'show')
	{
	  if( document.getElementById ) // this is the way the standards work
	  elem = document.getElementById("newCategory");
	  else if( document.all ) // this is the way old msie versions work
	  elem = document.all("newCategory");
	  else if( document.layers ) // this is the way nn4 works
	  elem = document.layers("newCategory");
	  vis = elem.style;
	  vis.display = 'block';
	}
	 else 
	{
	  if( document.getElementById ) // this is the way the standards work
	  elem = document.getElementById("newCategory");
	  else if( document.all ) // this is the way old msie versions work
	  elem = document.all("newCategory");
	  else if( document.layers ) // this is the way nn4 works
	  elem = document.layers("newCategory");
	  // document.newCategoryForm.name=""; //reset the new category box
	  vis = elem.style;
	  vis.display = 'none';
	}
}


function doNewDevice(action)
{
	// alert("doNewDevice called for " + action);
	var elem, vis;
	if (action == 'show')
	{
	  if( document.getElementById ) // this is the way the standards work
	  elem = document.getElementById("newDevice");
	  else if( document.all ) // this is the way old msie versions work
	  elem = document.all("newDevice");
	  else if( document.layers ) // this is the way nn4 works
	  elem = document.layers("newDevice");
	  vis = elem.style;
	  vis.display = 'block';
	}
	 else 
	{
	  if( document.getElementById ) // this is the way the standards work
	  elem = document.getElementById("newDevice");
	  else if( document.all ) // this is the way old msie versions work
	  elem = document.all("newDevice");
	  else if( document.layers ) // this is the way nn4 works
	  elem = document.layers("newDevice");
	  // document.newDeviceForm.name=""; //reset the new device box
	  vis = elem.style;
	  vis.display = 'none';
	}
}

function doNewMessage(action)
{
//	alert("doNewMessage called for " + action);
	var elem, vis;
	if (action == 'show')
	{
	  if( document.getElementById ) // this is the way the standards work
	  elem = document.getElementById("newMessage");
	  else if( document.all ) // this is the way old msie versions work
	  elem = document.all("newMessage");
	  else if( document.layers ) // this is the way nn4 works
	  elem = document.layers("newMessage");
	  vis = elem.style;
	  vis.display = 'block';
	}
	 else 
	{
	  if( document.getElementById ) // this is the way the standards work
	  elem = document.getElementById("newMessage");
	  else if( document.all ) // this is the way old msie versions work
	  elem = document.all("newMessage");
	  else if( document.layers ) // this is the way nn4 works
	  elem = document.layers("newMessage");
	  // document.newDeviceForm.name=""; //reset the new device box
	  vis = elem.style;
	  vis.display = 'none';
	}
}

function checkNewCategory(act)
{
		// alert("checkNewCategory called for category= " + category);
	if (document.form1.category.selectedIndex == document.form1.category.length-1)
	{
		doNewCategory('show');
	} else {
		thisticketcategory = document.form1.category.getValue();
	}
}

function checkNewDevice(act)
{
	// alert("checkNewDevice called for facility= " + facility);
	if (document.form1.facility.selectedIndex == document.form1.facility.length-1)
	{
		doNewDevice('show');
	} else {
		thisticketdevice = document.form1.facility.getValue();
	}
}
</script>

	<div id="newCategory">
    	<!-- <form id="newCategoryForm" method="get" action="javascript:saveCategory(document.getElementById(newCategoryForm.submit))"> -->
        <!--form.element.getvalue -->
        <form id="newCategoryForm" method="get" >
    	<label class="bodycopyheading" for="name">New Category Name:</label>
      
        <input name="categoryname" id='categoryname' type="text" size="20" maxlength="50">
        <input name="action" type="hidden" value="Create">
        <input name="categorysubmit"  id="categorysubmit" type="button" value="submit" onclick="javascript:saveCategory('submit')">
        <input name="categorycancel" id="categorycancel" type="button" value="cancel" onclick="javascript:saveCategory('cancel',thisticketcategory)">
    	</form> 
	</div>

	<div id="newDevice">
    	<form id="newDeviceForm" method="get" action="javascript:saveDevice()">
    	<label class="bodycopyheading" for="devname">New Facility Name:</label>
        <input name="devname" id='devname' type="text" size="20" maxlength="50">
    	<label class="bodycopyheading" for="description">Description:</label>
        <input name="description" id='description' type="text" size="20" maxlength="50">
    	<label class="bodycopyheading" for="type">Facility status:</label>
         	<select id="devstatus" name="devstatus">
            <option value="usable">usable</option>
            <option value="unusable">unusable</option>
            <option value="intermittent">intermittent</option>
        	<option value="unknown">unknown</option>
        </select>
        <br />
        <input name="action" type="hidden" value="Create">
        <input name="devicesubmit"  id="devicesubmit" type="button" value="submit" onclick="javascript:saveDevice('Create')">
        <input name="devicecancel" id="devicecancel" type="button" value="cancel" onclick="javascript:saveDevice('cancel',thisticketdevice)">
    	</form> 
	</div>

	<div id="newMessage">
    	<form id="newMessageForm" method="get" action="javascript:saveMessage(<?php echo $id;?>)">
    	<label class="bodycopyheading" for="email">New Message Author:</label>
        <input name="email" id='email' type="hidden" value= "<?php echo $REMOTE_USER;?>"><?php echo $REMOTE_USER;?><br />
    	<label class="bodycopyheading" for="content">New Message Content:</label>
		<textarea id="content" name="content" cols=20 rows=8></textarea>
        <label class="bodycopyheading" for="resolved">Resolved?</label>
        <input name="resolved" id="resolved" type="checkbox" value="yes">
        <br />
        <input name="tid" type="hidden" value="<?php echo $id;?>">
        <input name="action" type="hidden" value="Create">
        <input name="messagesubmit" id="messagesubmit" type="button" value="submit" onclick="javascript:saveMessage('Create','<?php echo $id;?>')">
        <input name="messagecancel" id="messagecancel" type="button" value="cancel" onclick="javascript:saveMessage('cancel')">
    	</form> 
	</div>
    

<?php
// First we must determine the function to be performed
if ($action == 'New') {															// Display empty form for New
?>
<div id="create">
	<form name="form1" id="form1" method="post" action="processTicket.php">
	    <table width="100%" border="0" cellspacing="5" cellpadding="5">
	      <tr>
	        <td width="27%" class="bodycopyheading"><div align="right">Originator:</div></td>
	        <td width="73%" class="bodycopy"><div align="left">
	          <label>
	          <input name="email" type="text" id="email" size="40" maxlength="50" value="<?php echo $REMOTE_USER;?>">
	          </label>
	        </div></td>
	      </tr>
	      <tr>
	        <td class="bodycopyheading"><div align="right">Problem Category:</div></td>
	        <td><div id="categorylist" align="left">
				<?php
				makeCategoryList($cqresult,$ptr,'Create');				// Display the category list menu
				?>
	        </div></td>
	      </tr>
	      <tr>
	        <td class="bodycopyheading"><div align="right">% Completed / Facility Info:</div></td>
	        <td><div id="devicelist" align="left">
				<?php
				makeDeviceList($dqresult,$ptr,'Create');				// Display the device list menu
				?>
	        </div></td>
	      </tr>
	      <tr>
            <td class="bodycopyheading"><div align="right"> Condition:</div></td>
	        <td><div align="left">
                <label>
                <select name="devstatus">
                  <option value="usable">usable</option>
                  <option value="unusable">unusable</option>
                  <option value="intermittent">intermittent</option>
                  <option selected value="unknown">unknown</option>
                </select>
                </label>
            </div></td>
	        </tr>
	      <tr>
            <td class="bodycopyheading"><div align="right">Severity:</div></td>
	        <td><label>
              <select name="severity" id="severity1">
                <option value='critical'>critical</option>
                <option value='normal' selected>normal</option>
                <option value='when convenient'>when convenient</option>
              </select>
            </label></td>
	        </tr>
	      <tr>
	        <td class="bodycopyheading"><div align="right">Problem Description:</div></td>
	        <td><label>
          
	        <div align="left">
	              <textarea name="description" id="description" cols="40" rows="6"></textarea>
	            </label>
	        </div></td>
	      </tr>
	    </table>
	    <p>
			<input type="submit" name="action" id="action" value="Create">
		</p>
	</form>
</div>
<?php
}																		// End of form for New Ticket
if ($action == 'Edit') {												// Display populated form for Edit, with an Update button
?>
<div id="Edit">
<br />
<form name="form1" id="form1" method="post" action="processTicket.php">
	<table width="100%" border="0" cellspacing="5" cellpadding="5">
		<tr>
		  <th width="27%" class="bodycopyheading" scope="col"><div align="right">Ticket ID</div></th>
		  <td width="73%" class="bodycopy" scope="col"><div align="left">
		      <?php echo $ptr['id'];?>
		  </div></td>
		</tr>
		<tr>
		  <td class="bodycopyheading"><div align="right">Ticket creation:</div></td>
		  <td class="bodycopy"><div align="left">
		      <label>
		      <?php echo $ptr['tickettime'];?>                           </label>
		  </div></td>
		</tr>
		<tr>
		  <td class="bodycopyheading"><div align="right">Last Update:</div></td>
		  <td class="bodycopy"><div align="left">
		      <label>
		      <?php echo $ptr['updated'];?>                           </label>
		  </div></td>
		</tr>
        <tr>
          <td class="bodycopyheading"><div align="right">Status:</div></td>
          <td  class='bodycopy'><div align="left">

		<?php																	// Only techs get to change the status
			if ($facrole == 'tech') {
				print '<select name="status">';
				if ($ptr['status'] == 'project') {
					print '<option selected value="project">project</option>';
				} else {
					print '<option value="project">project</option>';
				}
					if ($ptr['status'] == 'unseen') {
					print '<option selected value="unseen">unseen</option>';
				} else {
					print '<option value="unseen">unseen</option>';
				}
				if ($ptr['status'] == 'open') {
					print '<option selected value="open">open</option>';
				} else {
					print '<option value="open">open</option>';				
				}
				if ($ptr['status'] == 'in progress') {
					print '<option selected value="in progress">in progress</option>';
				} else {
					print '<option value="in progress">in progress</option>';				
				}
				if ($ptr['status'] == 'on hold') {
					print '<option selected value="on hold">on hold</option>';
				} else {
					print '<option value="on hold">on hold</option>';
				}
				if ($ptr['status'] == 'closed') {
					print '<option selected value="closed">closed</option>';
				} else {
					print '<option value="closed">closed</option>';
				}			
				print '</select>';
			} else {
				print $ptr['status'];												// If not a tech, just display the status
			}
		?>            
          </div></td>
        </tr>
        <tr>
          <td class="bodycopyheading"><div align="right">Responder:</div></td>
          <td class="bodycopy"><div align="left">
              <label>
              <input name="responder" type="text" id="responder" size="40" maxlength="50" value="<?php echo $ptr['responder']; ?>">
              </label>
          </div></td>
        </tr>
        <tr>
          <td class="bodycopyheading"><div align="right">Originator:</div></td>
          <td class="bodycopy"><div align="left">
              <label>
              <input name="email" type="text" id="email2" size="40" maxlength="50" value="<?php echo $ptr['email']; ?>">
              </label>
          </div></td>
        </tr>
        <tr>
          <td class="bodycopyheading"><div align="right">Problem Category:</div></td>
          <td><div id="categorylist" align="left">
          	<?php
				makeCategoryList($cqresult,$ptr,'Update');			// Display the category list menu
			?>
          </div></td>
        </tr>
        <tr>
          <td class="bodycopyheading"><div align="right">% Completed /Facility Info:</div></td>
          <td><div id="devicelist" align="left">
			<?php
				makeDeviceList($dqresult,$ptr,'Update');				// Display the device list menu
			?>
          </div></td>
        </tr>
        <tr>
          <td class="bodycopyheading"><div align="right"> Condition:</div></td>
          <td><div align="left">
          		<label>
				<select name="devstatus">
  	        	    <option <?php if ($ptr['devstatus'] == 'usable') {echo 'selected';} ?> value="usable">usable</option>
  		            <option <?php if ($ptr['devstatus'] == 'unusable') {echo 'selected';} ?> value="unusable">unusable</option>
  	    	        <option <?php if ($ptr['devstatus'] == 'intermittent') {echo 'selected';} ?> value="intermittent">intermittent</option>
	 	            <option <?php if ($ptr['devstatus'] == 'unknown') {echo 'selected';} ?> value="unknown">unknown</option>
   		        </select>          		</label>
          </div></td>
        </tr>
        <tr>
          <td class="bodycopyheading"><div align="right">Severity:</div></td>
          <td><label>
            <select name="severity" id="severity2">
              <option <?php if ($ptr['severity'] == 'critical') {echo 'selected ';} ?>value='critical'>critical</option>
              <option <?php if ($ptr['severity'] == 'normal') {echo 'selected ';} ?>value='normal'>normal</option>
              <option <?php if ($ptr['severity'] == 'when convenient') {echo 'selected ';} ?>value='when convenient'>when convenient</option>
            </select>
          </label></td>
        </tr>
		<tr>
		  <td class="bodycopyheading"><div align="right">Problem Description:</div></td>
		  <td><label>
		    <textarea name="description" id="description3" cols="45" rows="6"><?php echo $ptr['description'];?></textarea>
		  </label></td>
		</tr>
	</table>
	<p>
		<input type="hidden" name="id" id="id" value="<?php echo $ptr['id'];?>">
		<input type="submit" name="action" id="action" value="Update">
	</p>
</form>
</div>
	<table width="100%" border="0" cellspacing="5" cellpadding="2">
		<tr>
			<td colspan=2 align="left" class="bodycopyheadingbig">Messages for this Ticket</td>
			<td align="right">
			<form action="javascript:doNewMessage('show')" method="get" target="_self">
			<input name="newMessage2" type="submit" id="newMessage2" value="new Message">
            </form>
			</td>
		</tr>
	</table>
    <div id="messagelist">
       	<table width="100%" border="0" cellspacing="5" cellpadding="2">
		<tr>
			<th width="20%" class="bodycopyheading" scope="col"><div align="left">When</div></th>
			<th width="20%" class="bodycopyheading" scope="col"><div align="left">Who</div></th>
			<th width="*%" class="bodycopyheading" scope="col"><div align="left">Content</div></th>
		</tr>

		<?php
			while ($mptr= mysql_fetch_assoc($mqresult)) {
				print '<tr>';
				print '<td class="bodycopy" align="left">';
				print $mptr[datetime] . '</td>';
				print '<td class="bodycopy" align="left">';
				print $mptr['email'] . '</td>';
//				print '<td class="bodycopy" align="left">';
//				print $mptr[content] . '</td>';
				if ($mptr[resolved] == 'yes') {
					print '<td class="bodycopyheading" align="left">';				
				} else {
					print '<td class="bodycopy" align="left">';
				}
				print $mptr[content] . '</td>';				
				print '</tr>';
			}
		?>

		</table>
    </div><?php					
}																		// End of form for Update Ticket
if ($action == 'Show') {												// Display populated form for Show, but without an Update button
?>
<table width="100%" border="0" cellspacing="5" cellpadding="5">
	<tr>
	  <th width="27%" class="bodycopyheading" scope="col"><div align="right">Ticket ID</div></th>
	  <td width="73%" class="bodycopy" scope="col"><div align="left"><span class="bodycopy"> <?php echo $ptr['id'];?> </span></div></td>
	</tr>
	<tr>
	  <td class="bodycopyheading"><div align="right">Ticket creation:</div></td>
	  <td class="bodycopy"><div align="left">
	      <label> <?php echo $ptr['tickettime'];?> </label>
	  </div></td>
	</tr>
	<tr>
	  <td class="bodycopyheading"><div align="right">Last Update:</div></td>
	  <td class="bodycopy"><div align="left">
	      <label> <?php echo $ptr['updated'];?> </label>
	  </div></td>
	</tr>
	<tr>
	  <td class="bodycopyheading"><div align="right">Status:</div></td>
	  <td class="bodycopy"><div align="left">
	      <label>
	      <?php echo $ptr['status'];?></label>
	  </div></td>
	</tr>
  <tr>
    <td class="bodycopyheading"><div align="right">Responder:</div></td>
    <td class="bodycopy"><div align="left">
        <label>
        <?php echo $ptr['responder'];?></label>
    </div></td>
  </tr>
  <tr>
    <td class="bodycopyheading"><div align="right">Originator:</div></td>
    <td class="bodycopy"><div align="left">
        <label>
        <?php echo $ptr['email'];?></label>
    </div></td>
  </tr>
  <tr>
    <td class="bodycopyheading"><div align="right">Problem Category:</div></td>
    <td class="bodycopy"><div align="left">
        <label>
        <?php echo $ptr['category'];?></label>
    </div></td>
  </tr>
  <tr>
    <td class="bodycopyheading"><div align="right">% Completed / Facility Info:</div></td>
    <td class="bodycopy"><label>
      <?php echo $ptr['facility'];?>
    </label></td>
  </tr>
  <tr>
    <td class="bodycopyheading"><div align="right"> Condition:</div></td>
    <td class="bodycopy"><label>
      <?php echo $ptr['devstatus'];?>
    </label></td>
  </tr>
  <tr>
    <td class="bodycopyheading"><div align="right">Severity:</div></td>
    <td class="bodycopy"><label>
      <?php echo $ptr['severity'];?>
    </label></td>
  </tr>
  <tr>
    <td class="bodycopyheading"><div align="right">Problem Description:</div></td>
    <td class="bodycopy"><label>
      <?php echo $ptr['description'];?>
    </label></td>
  </tr>
</table>
<table width="100%" border="0" cellspacing="5" cellpadding="2">
	<tr>
		<td colspan=2 align="left" class="bodycopyheadingbig">Messages for this Ticket</td>
		<td align="right">
			<form action="javascript:doNewMessage('show')" method="get" target="_self">
			<input name="newMessage2" type="submit" id="newMessage2" value="new Message">
            </form>
		</td>
	</tr>
</table>
<div id="messagelist">
<table width="100%" border="0" cellspacing="5" cellpadding="2">
	<tr>
		<th width="20%" class="bodycopyheading" scope="col"><div align="left">When</div></th>
		<th width="20%" class="bodycopyheading" scope="col"><div align="left">Who</div></th>
		<th width="*%" class="bodycopyheading" scope="col"><div align="left">Content</div></th>
	</tr>
<?php
	while ($mptr= mysql_fetch_assoc($mqresult)) {
		print '<tr>';
		print '<td class="bodycopy" align="left">';
		print $mptr[datetime] . '</td>';
		print '<td class="bodycopy" align="left">';
		print $mptr['email'] . '</td>';
				if ($mptr[resolved] == 'yes') {
					print '<td class="bodycopyheading" align="left">';				
				} else {
					print '<td class="bodycopy" align="left">';
				}
		print $mptr[content] . '</td>';
		print '</tr>';
	}
?>   
</table>	
</div>
	<?php																	// End of (optional) Message display				
}
?>

<div align="right"><a href="listfTickets.php" target="_self" class="bodycopyLink"><u>List tickets</u></a></div>
      <!-- InstanceEndEditable -->
<?php 

include('../Templates/pageTemplateBottom.php');

 ?>