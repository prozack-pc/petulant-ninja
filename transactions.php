<!doctype html>
<?php
	require_once('config.php');
	include 'sql_db/db_sql.php';
	
	define('DB_WAITING',microtime());
	
	include_once('layout.php');
	require_once('api/esi-login.class.php');
	
	$output = new layout("");
	$refresh = new esi_login("");
	
	function character_init(){
		global $db;
		global $refresh;
		
		if(!(isset($_GET['id']))){	die('illegal direction - character.php?id={character_id}');}
		$sql = "SELECT * FROM eve_oauth WHERE id = '".$_GET['id']."'";
		if(!($result = $db->sql_query($sql))){	die('db_oauth:  error!!');}
		if(!($row = $db->sql_fetchrow($result))){  die('db_oauth:  invalid id'); }
		
		$owner = $row['owner_hash'];
		$char_name = $row['name'];
		$char_id = $_GET['id'];
		$etag = $row['wallet_tag'];
	
		$sql = "SELECT * FROM eve_authkey WHERE id='".$owner."'";
		if(!($result = $db->sql_query($sql))){	die('db_authkey:  error!!');}
		if(!($row = $db->sql_fetchrow($result))){  die('db_authkey:  invalid id'); }
	
		$id = $row['id'];
		$token = $row['token'];
		$value = $row['value'];
		$last = $row['last'];
	
		$refresh->setRegion('https://login.eveonline.com/oauth/token/');
		$refresh->setStub("?grant_type=refresh_token&refresh_token=".$token);
		$refresh->setReportCacheTTL(1200);
		$report = $refresh->getReport($refresh->getStub(),$refresh->getRegion());
		$obj = json_decode($report->getData(),1);
	
		
		$sql="UPDATE eve_authkey SET value='".$obj['access_token']."',last='".$obj['access_token']."' WHERE id='".$owner."'";
		if(!($result = $db->sql_query($sql))){	die('db_oauth:  error!!');}
		$old = md5('https://esi.evetech.net/latest/characters/'.$char_id.'/wallet/transactions/'.$last);
		$new = md5('https://esi.evetech.net/latest/characters/'.$char_id.'/wallet/transactions/'.$obj['access_token']);
		$sql = "SELECT * FROM eve_reports WHERE ObjectID = '".$old."'";
		if(!($result = $db->sql_query($sql))){	die('db_reports:  error!!');}
		if(($row = $db->sql_fetchrow($result))){  
			$sql = "UPDATE eve_reports SET ObjectID='".$new."' WHERE ObjectID='".$old."'";
			if(!($result = $db->sql_query($sql))){	/*die('db_reports: update1 error!!');*/}
			$sql = "UPDATE eve_authkey SET last='".$obj['access_token']."' WHERE id='".$owner."'";
			if(!($result = $db->sql_query($sql))){	/*die('db_reports: update2 error!!');*/}
		}
		return array('id'=>$char_id,'name'=>$char_name,'data'=>$obj,'ttl'=>$GLOBALS['eveonline']['db']['cacheTTL'], 'etag'=>$etag);
	}

	function character_wallet($char_id,$x,$tag=""){
		global $refresh;
		$refresh->set_etag($tag);
		$refresh->setRegion('https://esi.evetech.net/latest/characters/'.$char_id.'/wallet/transactions/');
		$refresh->setStub($x);
		$refresh->setReportCacheTTL(3600 * 1);
		
		$xaction = $refresh->getReport($refresh->getStub(),$refresh->getRegion());
		$obj = json_decode($xaction->getData(),1);
		return array('data'=>$obj,'prototype'=>$xaction,'ttl'=>$GLOBALS['eveonline']['db']['cacheTTL'],'etag'=>$GLOBALS['eveonline']['db']['etag']);
	}
	
	function db_update($d){
		global $db;
		if(!(isset($_GET['id']))){ die('illegal id.'); }
		
		$sql = "CREATE TABLE IF NOT EXISTS eve_xactioncache".$_GET['id']." (
			`client_id` int(12) NOT NULL,
			`date` varchar(20) NOT NULL,
			`stamp` int(16) NOT NULL,
			`is_buy` tinyint(1) NOT NULL,
			`is_personal` tinyint(1) NOT NULL,
			`journal_ref_id` double NOT NULL,
			`location_id` double NOT NULL,
			`quantity` double NOT NULL,
			`transaction_id` double NOT NULL,
			`type_id` int(8) NOT NULL,
			`unit_price` double NOT NULL,
			PRIMARY KEY (`transaction_id`)
		)	ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		if(!($result = $db->sql_query($sql))){die('db_xactioncache failed init');}
		
		$sql = "CREATE TABLE IF NOT EXISTS eve_itemscache".$_GET['id']." (
			`id` varchar(8) NOT NULL,
			`type` varchar(8) NOT NULL,
			`name` varchar(64) NOT NULL,
			PRIMARY KEY (`id`,`type`)
		)	ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		if(!($result = $db->sql_query($sql))){die('db_itemscache failed init');}
		
		$sql = "CREATE TABLE IF NOT EXISTS eve_stationscache".$_GET['id']." (
			`id` varchar(8) NOT NULL,
			`name` varchar(8) NOT NULL,
			PRIMARY KEY (`id`)
		)	ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		if(!($result = $db->sql_query($sql))){die('db_stationscache failed init');}		
		
		$sql = "SELECT wallet_tag FROM eve_oauth WHERE id = '".$_GET['id']."'";
		if(!($result = $db->sql_query($sql))){	die('db_report:  oauth error!!');}
		if(!($row = $db->sql_fetchrow($result))){ die('db_report: no row!!');}
		$sql = "UPDATE eve_oauth SET wallet_tag = '".$d['etag']."' WHERE id = '".$_GET['id']."'";
		if(!($result = $db->sql_query($sql))){	die('db_update:  oauth error!!');}
		
		if((is_array($d['data']))&&($d['data'][0]!="")){
			foreach($d['data'] as $x){
				
				$sql = "SELECT * FROM eve_itemscache".$_GET['id']." WHERE id = '".$x['type_id']."'";
				if(!($result = $db->sql_query($sql))){	die('db_report:  itemscache error!!');}
				if(!($row = $db->sql_fetchrow($result))){  
					$sql = "SELECT * FROM eve_itemtypes WHERE id='".$x['type_id']."'";
					if(!($result = $db->sql_query($sql))){	die('db_report:  itemtypes error!! browse to http://localhost/utility/items.php');}
					if(($item = $db->sql_fetchrow($result))){ 
						$sql = "INSERT INTO eve_itemscache".$_GET['id']." (id,type,name) VALUES ('".$item['id']."','".$item['type']."','".$item['name']."')";
						if(!($result = $db->sql_query($sql))){	die('db_update:  itemscache error!!');}
					}
				}
				$stamp = strtotime($x['date']);
				$x['is_buy'] ? 1 : 0;
				$x['is_personal'] ? 1 : 0;
				
				$sql = "SELECT * FROM eve_xactioncache".$_GET['id']." WHERE transaction_id = ".$x['transaction_id'];
				if(!($result = $db->sql_query($sql))){	die('db_report:  xactioncache error!!');}
				if(!($row = $db->sql_fetchrow($result))){  
					$sql = "INSERT INTO eve_xactioncache".$_GET['id']." (client_id,date,stamp,is_buy,is_personal,journal_ref_id,location_id,quantity,transaction_id,type_id,unit_price) VALUES (".$x['client_id'].",'".$x['date']."',".$stamp.",".$x['is_buy'].",".$x['is_personal'].",".$x['journal_ref_id'].",".$x['location_id'].",".$x['quantity'].",".$x['transaction_id'].",".$x['type_id'].",".$x['unit_price'].")";
					if(!($result = $db->sql_query($sql))){	die('db_update:  xactioncache error!!');}
				}else{
					break;
				}
			}
		}
	}
	
	function table_account($d,$f,$m=false){
		global $output;
		global $db;
		
		$sd = strtotime("-1 day");
		$sw = strtotime("-1 week");
		$sm = strtotime("-1 month");
		$sy = strtotime("-1 year");
		$i;$b=0;
		$td;$n;$tr=array();
		$tq=0;$tc=0;$avg=0;$ix=0;
		if(count($d) > 0){
			$uk_sptr = 1;
			$station_list = array();
			$station_list[0] = '999999999999';
			$uk_iptr = 1;
			$item_list = array();
			$item_list[0] = '999999999999';
		
			foreach($d as $v){
				#var_dump($v);
				$i = $v['type_id'];
				$q = $v['quantity'];
				$p = $v['unit_price'];
				$c = $q * $p;
				if($v['is_buy']=='1'){	$c = $c * -1;}
				$tc+=$c;
				$obj = array(c1=>$q,c2=>$p,c3=>$c,c4=>$v['stamp']);
				$tx[$ix] = $obj;
				
				$holder = "all";
				if($obj['c4'] > $sy){$holder = "year";}
				if($obj['c4'] > $sm){$holder = "month";}
				if($obj['c4'] > $sw){$holder = "week";}
				if($obj['c4'] >= $sd ){ $holder = "day";}
				
				$tr[$ix]='<tr class="'.$holder.'">';
				$tr[$ix].='<td style="padding:0 5px" align="'.$f[0].'">'.$q.'</td>';
				$tr[$ix].='<td style="padding:0 5px" align="'.$f[1].'">'.$p.'</td>';
				
				$cf = "color:#000";
				if($v['is_buy']=='1'){ $cf = "color:#f00";}
				$tr[$ix].='<td  style="padding:0 5px" align="'.$f[2].'"><a style="'.$cf.'">'.number_format($c,2,'.','').'</a></td>';
				
				// checking itemscache
				$name = 'unknown type name';
				$sql = "SELECT name FROM eve_itemscache".$_GET['id']." WHERE id = '".$i."'";
				if(!($result = $db->sql_query($sql))){	die("db_report: itemscache error.");}
				if($row = $db->sql_fetchrow($result)) {
					$name = $row['name'];
				}else{
					if($output->seek_ptrIndex($i,$item_list)==false){
						$item_list[$uk_iptr] = $i;
						$uk_iptr++;
					}
				}
				$tr[$ix].='<td style="padding:0 5px" align="'.$f[3].'"><span class="tid"><a class="info" href="reports.php?q='.$v['type_id'].'&id='.$_GET['id'].'">'.$name.'</a></span></div></td>';
				
				// checking stationscache -- not implemented!!
				
				$name = $v['location_id'];
				$sql = "SELECT name FROM eve_stationscache".$_GET['id']." WHERE id = '".$v['location_id']."'";
				if(!($result = $db->sql_query($sql))){	die("error: table `".$_GET['id']."_stationcache` - not found.");}
				if($row = $db->sql_fetchrow($result)) {
					$name = $row['name'];
				}else{
					if($output->seek_ptrIndex($v['location_id'],$station_list)==false){
						$station_list[$uk_sptr] = $v['location_id'];
						$uk_sptr++;
					}
				}
				$tr[$ix].='<td style="padding:0 5px" align="'.$f[4].'"><span class="sid">'.$name.'</span></td>';
				$tr[$ix].='<td style="padding:0 5px" align="'.$f[5].'">'.date('Y-m-d H:i:s', $v['stamp']).'</td>';
				$tr[$ix].='<td style="padding:0 5px" align="'.$f[6].'">'.$v['client_id'].'</td>';
				$tr[$ix].='</tr>';
				$ix+=1;
			}
			$avg=0;
			$td = array(obj => $tr,data => $tx,tq => $tq,tc => $tc,avg => $avg,mode => $m,type => $n);
		}
		return $td;
	}
	
	$d = character_init();
	#var_dump($d);
	$o = character_wallet($d['id'],$d['data']['access_token'],$d['etag']);
	#var_dump($o);
	
	#print 'cache-expire:  '.$o['ttl'].'sec.<br>';
	db_update($o);
	
	
?>
<html><head>
<?php print '<title>'.$d['name'].'</title>'; ?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<script src="jquery/jquery-1.11.0.min.js"></script>
<script src="jquery/jquery.tables.min.js"></script>
<script src="jquery/jquery.freeow.min.js"></script>

<style>
body {
	background-color:#fff;
	font:12px tahoma,sans-serif normal;
	color: #000;
	text-decoration: none;
}
a.info:link, a.info:visited, a.info:active, a.info:hover{
	font:12px tahoma,san-serif normal;
	text-decoration:none;
	color:#55a;
}
a.info:hover {
	color:#888;
}
.sid, .tid {
	white-space: nowrap; 
	overflow: hidden;
	text-overflow: ellipsis;
}
.header { 
    font:12px tahoma, san-serif normal;padding:5px 0px; }
.menubox {
	font:12px tahoma, san-serif normal;padding-top:0px
}
</style>

</head>
<body>
<?php

	$menu = $output->menu_items();
	$all = $output->transaction_data();

?>	

<table>
<tr><td align="left">
	<table>
	<tr valign="top"><td width="280" align="left">

	<span style="border:1px solid #666;display:block;padding-right:5px;margin:8px 0 10px 0;background:#ccc">
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
			</td><td class="menubox" style="padding:5px 0 0 20px"><a href="http://localhost/">home</a><br><?php print '<a href="http://localhost/transactions.php?id='.$_GET['id'].'">top</a>';?></td></tr>
			</table>
		</form>
		</span>
<?php		
	
?>

		<span class="menubox">

<?php
	if($o['ttl'] > 0){
		print 'using cache<br>Time-to-Live: '.$o['ttl'].' sec.<br><br>';
	}else{
		print 'requesting data.<br>Time-to-Live: 0 sec.<br><br>';
	}
	print '
<input type="text" id="kw_search" value="" /> &nbsp;Find
';
	$th_list = array("items");
	$th_width = array(240);
	$th_format = array("left");
	$d0=$output->table_menu($menu,$th_format,"items");
	$output->table_display($d0,$th_list,$th_width,$th_format,0);
	
?>
		
		</span>
	</td><td style="padding:0 0 10px 20px">
		
		
<?php 
	
	$th_list = array("units","price","cost","type","station","date","merchant");
	$th_width = array(70,90,120,290,70,185,70);
	$th_format = array("right","right","right","left","right","center","right");
	if($all != NULL){
		$d3=table_account($all,$th_format,"other");
		$output->table_display($d3,$th_list,$th_width,$th_format,0);
	}
?>	
		
	</td></tr>
	</table>
		
<script type="text/javascript">	
	jQuery(document).ready(function($){
		$("#table_item").tablesorter( {sortList: [[0,0]]} );
		//$("#table_all").tablesorter( {sortList: [[5,1]]} );
		//$("#table_other").tablesorter( {sortList: [[5,1]]} );
		$("#item-container>tr").show();
		//$("#all-container>tr").show();
		//$("#other-container>tr").show();
	});
</script>	
		
<script type="text/javascript">
	
	$("#items-container>tr").show();
	$("#all-container>tr").show();
	$("#other-container>tr").show();
	
	$("#kw_search").keyup(function() {
		if( $(this).val() != "") {
			$("#items-container>tr").hide();
			$("#table_items td:contains-ci(\'" + $(this).val() + "\')").parent("tr").show();
			
		}else{
			$("#items-container>tr").hide();
			$("div.wb-holder").show();
		}
	});
	$.extend($.expr[":"], {
		"contains-ci": function(elem, i, match, array) {
			return (elem.textContent || elem.innerText || $(elem).text() || "").toLowerCase().indexOf((match[3] || "").toLowerCase()) >= 0;
		}
	});
	
	$("#items-container>tr").show();
	$("#all-container>tr").show();
	$("#other-container>tr").show();
	
	$("#cb_any").prop("checked",true);
	$("#cb_type").prop("checked",false);
	$("#cb_day").prop("checked",false);
	$("#cb_week" ).prop( "checked",false);
	$("#cb_month" ).prop( "checked",true );
	$("#cb_year" ).prop( "checked",false );
	$("#cb_table").prop("checked",true);
	$("#cb_graph").prop("checked",false);
	
	$(':checkbox').change(function() {
		if($(this).is(":checked")) {
			if(($(this).val()=="day")||($(this).val()=="week")||($(this).val()=="month")||($(this).val()=="year")||($(this).val()=="all")){
				$("#cb_day").prop("checked",false);
				$("#cb_week" ).prop( "checked", false );
				$("#cb_month" ).prop( "checked", false );
				$("#cb_year" ).prop( "checked", false );
				$("#cb_all" ).prop( "checked", false );
				$(this).prop("checked",true);

				$("#table_all tr.day").hide();
				$("#table_other tr.day").hide();
				$("#table_all tr.week").hide();
				$("#table_other tr.week").hide();
				$("#table_all tr.month").hide();
				$("#table_other tr.month").hide();
				$("#table_all tr.year").hide();
				$("#table_other tr.year").hide();
				$("#table_all tr.all").hide();
				$("#table_other tr.all").hide();
				
				if($("#cb_day").is(":checked")){
					$("#table_all tr.day").show();
					$("#table_other tr.day").show();
				}
				if($("#cb_week").is(":checked")){
					$("#table_all tr.day").show();
					$("#table_other tr.day").show();
					$("#table_all tr.week").show();
					$("#table_other tr.week").show();
				}
				if($("#cb_month").is(":checked")){
					$("#table_all tr.day").show();
					$("#table_other tr.day").show();
					$("#table_all tr.week").show();
					$("#table_other tr.week").show();
					$("#table_all tr.month").show();
					$("#table_other tr.month").show();
				}
				if($("#cb_year").is(":checked")){
					$("#table_all tr.day").show();
					$("#table_other tr.day").show();
					$("#table_all tr.week").show();
					$("#table_other tr.week").show();
					$("#table_all tr.month").show();
					$("#table_other tr.month").show();
					$("#table_all tr.year").show();
					$("#table_other tr.year").show();
				}
				if($("#cb_all").is(":checked")){
					$("#table_all tr.day").show();
					$("#table_other tr.day").show();
					$("#table_all tr.week").show();
					$("#table_other tr.week").show();
					$("#table_all tr.month").show();
					$("#table_other tr.month").show();
					$("#table_all tr.year").show();
					$("#table_other tr.year").show();
					$("#table_all tr.all").show();
					$("#table_other tr.all").show();
				}
				
			}
			if(($(this).val()=="type")||($(this).val()=="any")){
				$("#cb_type").prop("checked",false);
				$("#cb_any" ).prop( "checked", false );
				$(this).prop("checked",true);
			}
			return;
		}
		if(($(this).val()=="all")){
			$("#cb_day").prop("checked",true);
			$("#cb_week" ).prop( "checked", false );
			$("#cb_month" ).prop( "checked", false );
			$("#cb_year" ).prop( "checked", false );
			$(this).prop("checked",false);
		}
		if(($(this).val()=="type")||($(this).val()=="any")){
			$("#cb_type").prop("checked",true);
			$("#cb_any" ).prop( "checked",true);
			$(this).prop("checked",false);
			
		}
	});

	jQuery(document).ready(function($){
		
		$("#table_items").tablesorter( {sortList: [[0,0]]} );
		$("#items-container>tr").show();

		$("#table_all").tablesorter( {sortList: [[5,1]]} );
		$("#table_other").tablesorter( {sortList: [[5,1]]} );
		$("#table_item").tablesorter( {sortList: [[0,0]]} );
		$("#item-container>tr").show();
		
		$("#table_all tr.day").show();
		$("#table_other tr.day").show();
		$("#table_all tr.week").show();
		$("#table_other tr.week").show();
		$("#table_all tr.month").show();
		$("#table_other tr.month").show();
		$("#table_all tr.year").hide();
		$("#table_other tr.year").hide();
		$("#table_all tr.all").hide();
		$("#table_other tr.all").hide();
		
	});
</script>

</body>
</html>
