<?php
if ($_SERVER['SERVER_NAME'] == 'asccintranet.bajzek.com') {
	$sender= 'thomas@bajzek.com';
	$recipient = 'tom@bajzek.com';
	$domain= '@bajzek.com';
} elseif ($_SERVER['SERVER_NAME'] == 'intranet.asccinc.com') {
	$sender= 'tickets@asccnet.com';
	$recipient= 'facilityTechs@asccinc.com';
	$domain= '@asccinc.com';
} else {				// This is the case of localhost: MAMP or XAMPP
	$recipient= 'steve@bajzek.com';
	$sender= 'steve@bajzek.com';
	$domain= '@bajzek.com';
}
?>