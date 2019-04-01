<?php
error_reporting(E_ALL ^ E_NOTICE);


#--[ MYSQL CONFIG ]-------------------------------------------
// do not change this line
$GLOBALS['eveonline']['db']['driver'] = 'mysql'; 

#--[ YOU MUST modify this section with DB params ]--

$db_user   = 	'root';		
$db_passwd = 	'';		
$db_name   = 	'eveonline_db';		
$db_host   = 	'localhost';

$GLOBALS['eveonline']['db']['hostname'] = $db_host;
$GLOBALS['eveonline']['db']['dbname'] = $db_name;
$GLOBALS['eveonline']['db']['username'] = $db_user;
$GLOBALS['eveonline']['db']['password'] = $db_passwd;

#--[ visit eve online developers portal ]------------------------------------
#--[ needs multiple user solution over web-friendly environments ]-----------

$GLOBALS['eveonline']['db']['base64'] = "< base64 encoded secret >";
$GLOBALS['eveonline']['db']['id'] = "< your client id >";

$table_prefix = '';


?>