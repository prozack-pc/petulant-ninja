<?php
require_once('../config.php');
include '../sql_db/db_sql.php';

if(isset($_GET['code'])){
	require_once('../api/esi-login.class.php');
	
	$connect = new esi_login($_GET['code']);
	#var_dump($connect);
	$data = $connect->getReport($connect->getStub(),$connect->getRegion());
	$json = $data->getData();
	$dx = json_decode($json,true);
	
	if(!(isset($dx['access_token']))){ die('access_token error!!');}
	$connect->setRegion("https://login.eveonline.com/oauth/verify/");
	$connect->setStub($dx['access_token']);
	
	#var_dump($connect);
	
	$test = $connect->getReport($connect->getStub(),$connect->getRegion());
	
	#var_dump($test->getData());
}	


?>
<!doctype="html">
<html><head>
<title>oAuth2.0 || SSO Callback</title>
</head>
<body>
<?php

?>
</body>
</html>
