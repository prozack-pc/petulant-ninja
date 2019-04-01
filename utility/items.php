<!DOCTYPE html>
<?php

	require_once('../config.php');
	include '../sql_db/db_sql.php';

	$sql = "CREATE TABLE IF NOT EXISTS eve_itemtypes (
		id varchar(6) NOT NULL,
		type varchar(6) NOT NULL,
		name varchar(128) NOT NULL,
		PRIMARY KEY (`id`,`type`)
	)	ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	if(!($result = $db->sql_query($sql))){die('create error - eve_itemtypes');}
		
	$f = fopen("textfiles/itemtypes.txt", "r") or die('file not found.');
	while(!feof($f)) {
		$d = fgets($f);
		$id = intval(substr($d,0,11));
		$type = intval(substr($d,12,23));
		$name = str_replace("'","&#39;",rtrim(substr($d,24,strlen($d)-24)));
		
		
		$sql = "INSERT INTO eve_itemtypes (id,type,name) VALUES('".$id."','".$type."','".rtrim($name)."')";
		if(!($result = $db->sql_query($sql))){	die('insert failed.');}
		
	}
	header('Location: http://localhost/index.php');
?>
