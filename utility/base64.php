<?php

	if((!(isset($_GET['id'])))||(!(isset($_GET['key'])))){
		die('base64.php?id=<client-id>&key=<secret-key>');
	}else{
		die(base64_encode($_GET['id'].':'.$_GET['key']));
	}


?>