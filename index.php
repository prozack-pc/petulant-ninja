<!DOCTYPE html>
<?php 
	require_once('config.php');
	include 'sql_db/db_sql.php';

	$sql = "SELECT * FROM eve_oauth WHERE id != ''";
	if(!($result = $db->sql_query($sql))){	die('db_oauth:  error!!');}
	while(($row = $db->sql_fetchrow($result))){ 
		$char[] = $row;
	}
?>
<html><head>
<title>oAuth2.0 || SSO Login</title>
<style>
body {
	background-color:#fff;
	font: 12px tahoma,sans-serif normal;
	color: #000;
	text-decoration: none;
}
a.char_type:link, a.char_type:visited, a.char_type:active, a.char_type:hover {
	text-decoration:none;
	color:#000;
}
a.char_type:hover{
	color:#666;
}

.icon {
	margin-top:3px;
	border:2px solid #888;
}
</style>
</head>
<body>

<?php
	
	if($char != ""){
		foreach($char as $x){
			$name = $x['name'];
			$id = $x['id'];
			$scope = $x['scope'];
	
			print '<span style="display:block;height:64px;width:640px;padding:5px;background:#ccc;margin-bottom:5px">
				<span style="float:left;margin-right:5px">
				<img src="https://imageserver.eveonline.com/Character/'.$id.'_256.jpg" width="64" align="top">
				</span>'.$name.' --[char_id: '.$id.']<br>'.$scope.'<br>
				<a href="transactions.php?id='.$id.'" class="char_type"><img src="image/ui/wallet_icon" class="icon"></a>
				<a href="journal.php?id='.$id.'" class="char_type"><img src="image/ui/journal_icon" class="icon"></a>
				</span>';
		}
	}
	$url = "https://login.eveonline.com/oauth/authorize/?response_type=code&redirect_uri=http://localhost/callback/&client_id=03722074ff084a4497e8780b14bf9ca0&scope=esi-wallet.read_character_wallet.v1";
	print '<a href="'.$url.'"><img src="image/ui/eve-sso-login-white-large.png"></a>';
	
?>
</body>
</html>