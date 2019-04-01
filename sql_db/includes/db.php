<?php
/***************************************************************************
 *                                 db.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *
 *   $Id: db.php 5283 2005-10-30 15:17:14Z acydburn $
 *
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

include 'mysql.php';

// Make the database connection.
$db = new sql_db($db_host, $db_user, $db_passwd, $db_name, false);
if(!$db->db_connect_id){
	die("error: could not connect to the database.<br>Check the config.php file and provide it with the correct values.");
}

?>