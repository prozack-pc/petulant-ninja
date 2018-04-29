<?php
require_once('config.php');
include 'include/user_config.php';

define('DB_WAITING',microtime());
include_once('include/profile.php');
include_once('include/character.php');

$profile = new profile();

$entity = new character();
$menu = menu_items($entity->get_raw());

function char_process(){
	global $entity;
	print $entity->get_raw();
}

function user_script(){
	global $profile;
	print $profile->fn_javascript();
}

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<html><head>
<title>API Key Transactions || Add/Remove -- Eve Online</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="frontend.css">
<script src="jquery/jquery-1.11.0.min.js"></script>
<script src="jquery/jquery.tables.min.js"></script>
<script>
	
	function key_calc(form){
		if((ck_code(form.vc.value))||(ck_key(form.kc.value))) {
			document.getElementById('update').innerHTML = '<a style="color:#d27">error - invalid data.</a>';
		}else{
			document.location = 'include/update.php?kc='+form.kc.value+'&vc='+form.vc.value;
		}
	}
	function key_destroy(form){
		if(ck_ptr(form.pt.value)) {
			document.getElementById('destroy').innerHTML = '<a style="color:#d27">error - invalid data.</a>';
		}else{
			document.location = 'include/update.php?remove='+form.pt.value;
		}
	}
	function ck_key(v){
		if((isNaN(v))||(parseInt(v)<999999)) return true;
		return false;
	}
	function ck_char(v){
		if((isNaN(v))||(parseInt(v)<9999999)) return true;
		return false;
	}
	function ck_code(v){
		if((isNaN(v))&&(v.length == 64)) return false;
		return true;
	}
	
</script>
<?php user_script();?> 
</head>
<body>

<table width="100%">
<tr><td align="left">
	<table>
	<tr height="90" valign="top">
	<td  colspan="2" style="background:#222">
	<span class="hdrmenu">
		<a class="info" style="margin-left:5px" href="#">API Keys</a><br>
		
	</span><span class="hdrmenu">
		<a class="item" style="margin-left:5px" href="index.php">Entity Browser</a><br>
		<a class="item" style="margin-left:5px" href="stations.php">Station Info</a><br>
		
	</span>
	
	
	</td></tr>
	<tr valign="top"><td width="280" align="left">
		
	<?php
		/*
		print '<span style="border:1px solid white;display:block;padding-right:5px;margin:8px 0 10px 0;background:#333">
		<form id="format" method="post">
			<table>
			<tr valign="top"><td>
				<table cellpadding="0" cellspacing="0">
				<tr><td><input id="cb_table" type="checkbox" name="fd[]" value="table"></td><td style="padding-top:3px">table</td></tr>
				<tr><td><input id="cb_graph" type="checkbox" name="fd[]" value="graph"></td><td style="padding-top:3px">graph</td></tr>
				
				</table>
			</td><td>
				<table cellpadding="0" cellspacing="0">
				<tr><td><input id="cb_day" type="checkbox" name="fd[]" value="day"></td><td style="padding-top:3px">day</td></tr>
				<tr><td><input id="cb_week" type="checkbox" name="fd[]" value="week"></td><td style="padding-top:3px">week</td></tr>
				<tr><td><input id="cb_month" type="checkbox" name="fd[]" value="month"></td><td style="padding-top:3px">month</td></tr>
				<tr><td><input id="cb_year" type="checkbox" name="fd[]" value="year"></td><td style="padding-top:3px">year</td></tr>
				<tr><td><input id="cb_all" type="checkbox" name="fd[]" value="all"></td><td style="padding-top:3px">all</td></tr>
				</table>
			</td><td>
				<table cellpadding="0" cellspacing="0">
				<tr><td><input id="cb_any" type="checkbox" name="fd[]" value="any"></td><td style="padding-top:3px">any</td></tr>
				<tr><td><input id="cb_type" type="checkbox" name="fd[]" value="type"></td><td style="padding-top:3px">type</td></tr>
				</table>
			</td></tr>
			</table>
		</form>
		</span>';
		
		*/
		
	?>
	<span class="menubox">

<?php
	if($cacheTTL > 0){
		print 'using cache<br>Time-to-Live: '.$cacheTTL.' sec.<br><br>';
	}else{
		print 'requesting data.<br>Time-to-Live: 0 sec.<br><br>';
	}
	print '
<input type="text" id="kw_search" value="" /> &nbsp;Find
';
	$th_list = array("name");
	$th_width = array(240);
	$th_format = array("left");
	$d0=table_menu($menu,$th_format,"item");
	table_display($d0,$th_list,$th_width,$th_format,0);
	
?>
		
		</span>
	</td><td style="padding:0 0 10px 5px">
		
		<span style="border:0px solid white;display:block">
		
		<table>
		<tr valign="top"><td style="padding:6px 0px 10px 0px">
		<span style="display:block;width:600px;text-align:justify">
		Supply the form with a keyID and vCode for the entity you wish to process.
		Click on the code listed in the table below to validate each API key individually.
		To remove an entity, click on it's name located below the vCode.
		Cache-time applied to each request.<br><br>
		<br></span>
		
<?php 
	
function user_process(){
	global $profile;
	global $menu;
	
	#var_dump($menu);
	return $profile->user_showdata($profile->get_raw());
}
print user_process();



?>
		<span style="padding-left:20px;display:block"><br><br>
		<form name="info" action="" method="GET">
		<input id="form" type="text" size="16" name="kc" value="keyID" style="margin:5px 5px 0 0"><a class="fa">keyID:</a><br>
		<input id="form" type="text" size="80" name="vc" value="vCode" style="margin:5px 5px 0 0"><a class="fa">vCode:</a><br><br>
		<span style="width:200px;height:50px;display:block">
			<input id="button" type="button" name="b1" value="add" onClick="key_calc(this.form)"><br><br>
			<div id="update">&nbsp;</div>
		</span><br><br>
		<input id="form" type="text" size="10" name="pt" value="#" style="margin:5px 5px 0 0"><a class="fa">record to delete.</a><br><br>
		<input id="button" type="button" name="b2" value="delete" onClick="key_destroy(this.form)"><br><br>
		<div id="destroy">&nbsp;</div><br><br>
	
		</form>
		</span>
		
		</td></tr>
		</table>
		</span>
		
	</td><td>
		<span style="border:0px solid white;display:block;padding:5px 5px 10px 5px;margin-top:42px">

		<table>
		<tr><td>
<?php

?>		
		</td></tr>
		</table>
		</span>
		<span style="border:0px solid white;display:block;padding:5px 5px 10px 5px;margin-top:65px">

		<table>
		<tr><td>

		</td></tr>
		</table>
		</span>
		
	</td></tr>
	</table>
</td></tr>
<table>	

<script type="text/javascript">
	$("#item-container>tr").show();
	
	$("#kw_search").keyup(function() {
		if( $(this).val() != "") {
			$("#item-container>tr").hide();
			$("#table_item td:contains-ci(\'" + $(this).val() + "\')").parent("tr").show();
			
		}else{
			$("#item-container>tr").hide();
			$("div.wb-holder").show();
		}
	});
	$.extend($.expr[":"], {
		"contains-ci": function(elem, i, match, array) {
			return (elem.textContent || elem.innerText || $(elem).text() || "").toLowerCase().indexOf((match[3] || "").toLowerCase()) >= 0;
		}
	});
	
	jQuery(document).ready(function($){
		$("#table_item").tablesorter( {sortList: [[0,0]]} );
		$("#item-container>tr").show();
	});
</script>	
	



</body>
</html>
