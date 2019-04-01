<?php
require_once('config.php');
include 'sql_db/db_sql.php';
	
define('DB_WAITING',microtime());
include_once('layout.php');
require_once('api/esi-login.class.php');

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
			#var_dump($v['balance']);
				
			$i = $v['amount'];
			$q = $v['context'];
			$p = $v['reason'];
				
			$tc+=$i;
			$obj = array(c1=>$i,c2=>$q,c3=>$p,c4=>$v['stamp']);
			$tx[$ix] = $obj;
				
			$holder = "all";
			if($obj['c4'] > $sy){$holder = "year";}
			if($obj['c4'] > $sm){$holder = "month";}
			if($obj['c4'] > $sw){$holder = "week";}
			if($obj['c4'] >= $sd ){ $holder = "day";}
			
			$tr[$ix]='<tr class="'.$holder.'">';
			$tr[$ix].='<td class="menubox" align="'.$f[0].'">'.$v['ref_type'].'</td>';
			$tr[$ix].='<td class="menubox" align="'.$f[1].'">'.date('Y-m-d H:i:s', $v['stamp']).'</td>';
				
			$cf = "color:#000";
			if($v['amount'] < 0){ $cf = "color:#e00";}
			$tr[$ix].='<td  class="menubox" align="'.$f[2].'"><a style="'.$cf.'">'.number_format($v['amount'],2,'.','').'</a></td>';
			$tr[$ix].='<td class="menubox" align="'.$f[3].'"><span class="sid">'.$v['context'].'</span></td>';
			$tr[$ix].='<td class="menubox" align="'.$f[4].'"><span class="sid">'.$v['reason'].'</span></td>';
			$tr[$ix].='</tr>';
			$ix+=1;
		}
		$avg=0;
		$td = array(obj => $tr,data => $tx,tq => $tq,tc => $tc,avg => $avg,mode => $m,type => $n);
	}
	return $td;
}
function table_data($item,$type){
	var_dump($item);
	var_dump($type);
	
	global $db;
	$list = array();
	//var_dump(strtotime("now"));
	$d1 = strtotime($GLOBALS['eveonline']['db']['all']);
	
	switch($type){
		case 1:
		case 0:
			$sql = "SELECT * FROM eve_journalcache".$_GET['id']." WHERE ref_type = '".$_GET['q']."' ORDER BY stamp DESC";
			if(!($result = $db->sql_query($sql))){	message_die(CRITICAL_ERROR,'db_report: journalcache not found');}
			while(($row = $db->sql_fetchrow($result))){ 
				$list[] = $row;
			}
			break;
		case 2:
			$sql = "SELECT id FROM eve_oauth WHERE id != ''";
			if(!($result = $db->sql_query($sql))){	message_die(CRITICAL_ERROR,'db_report: oauth error');}
			while(($row = $db->sql_fetchrow($result))){ 
				$chr_id[] = $row;
			}
			$ix = 0;
			foreach($chr_id as $x){
				$sql = "SELECT * FROM eve_xactioncache".$x['id']." WHERE type_id = ".$item;
				#print $sql."<br>";
				if(!($result = $db->sql_query($sql))){	message_die(CRITICAL_ERROR,'db_report: xactioncache not found');}
				while(($row = $db->sql_fetchrow($result))){ 
					$obj[] = $row;
				}
				$list[$ix] = array('id'=>$x['id'],'obj'=>$obj);
				$obj = NULL;
				$ix++;
			}
			break;
	}
	return $list;
}

$output = new layout("");	
if((isset($_GET['q']))&&(isset($_GET['id']))){

	$menu = $output->menu_infotypes();
	$all = $output->journal_type_data();
	
	#var_dump($d1['type'].$d2['type']);
	
}else{
	if(isset($_GET['q'])){
		$book = table_data($_GET['q'],2);
		
		$ix = 0;
		foreach($book as $x){
			#var_dump($x);
			if((is_array($x['obj']))&&($x['obj'][0]!="")){
				foreach($x['obj'] as $v){
					#var_dump($v);
					$list[$ix] = $v;
					$ix++;
				}
			}
		}
		$all = $list;
		$d3=table_account($all,$th_format,"sell");
	}
}

?>
<html><head>
<?php print '<title>'.$d3['type'].$d1['type'].$d2['type'].'</title>'; ?>
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
	overflow: scroll;
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

	#var_dump($all);

?>	

<table width="150%">
<tr><td align="left">
	<table>
	<tr valign="top"><td width="240" align="left">

	<span style="border:1px solid #666;display:block;padding-right:5px;margin:8px 0 10px 0;background:#ccc">
		<form id="format" method="post">
			<table>
			<tr valign="top"><td>
				<table cellpadding="0" cellspacing="0">
				<tr><td><input id="cb_table" type="checkbox" name="fd[]" value="table"></td><td class="menubox">table</td></tr>
				<tr><td><input id="cb_graph" type="checkbox" name="fd[]" value="graph"></td><td class="menubox">graph</td></tr>
				
				</table>
			</td><td>
				<table cellpadding="0" cellspacing="0">
				<tr><td><input id="cb_day" type="checkbox" name="fd[]" value="day"></td><td class="menubox">day</td></tr>
				<tr><td><input id="cb_week" type="checkbox" name="fd[]" value="week"></td><td class="menubox">week</td></tr>
				<tr><td><input id="cb_month" type="checkbox" name="fd[]" value="month"></td><td class="menubox">month</td></tr>
				<tr><td><input id="cb_year" type="checkbox" name="fd[]" value="year"></td><td class="menubox">year</td></tr>
				<tr><td><input id="cb_all" type="checkbox" name="fd[]" value="all"></td><td class="menubox">all</td></tr>
				</table>
			</td><td>
				<table cellpadding="0" cellspacing="0">
				<tr><td><input id="cb_any" type="checkbox" name="fd[]" value="any"></td><td class="menubox">any</td></tr>
				<tr><td><input id="cb_type" type="checkbox" name="fd[]" value="type"></td><td class="menubox">type</td></tr>
				</table>
			</td><td class="menubox" style="padding:5px 0 0 20px"><a href="http://localhost/">home</a><br><?php print '<a href="http://localhost/journal.php?id='.$_GET['id'].'">top</a>';?></td></tr></tr>
			</table>
		</form>
	</span>

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
	$d0=$output->table_menu($menu,$th_format,"types");
	$output->table_display($d0,$th_list,$th_width,$th_format,0);
	
	
?>
		
		</span>
	</td><td style="padding:0 0 10px 20px">
						
<?php 

	switch($_GET['q']){
	case 'bounty_prizes':
	
		$th_list = array("ref-type","date","amount","context","ship-count");
		$th_width = array(180,120,120,180,500);
		$th_format = array("left","center","right","center","left");
		break;
	default:
		$th_list = array("ref-type","date","amount","context","description");
		$th_width = array(180,180,90,220,220);
		$th_format = array("left","left","right","right","right");
		break;
	}
	if($all != NULL){
		$d3=table_account($all,$th_format,"other");
		
		var_dump($d3['tc']);
		$output->table_display($d3,$th_list,$th_width,$th_format,0);		
	}
?>	
	</td>
	</tr></table>
		
		

<script type="text/javascript">

	jQuery(document).ready(function($){
		$("#table_types").tablesorter( {sortList: [[0,0]]} );
		
		$("#types-container>tr").show();
		
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
	});
</script>	
		
<script type="text/javascript">
	
	$("#types-container>tr").show();
	$("#all-container>tr").show();
	$("#other-container>tr").show();
	
	$("#kw_search").keyup(function() {
		if( $(this).val() != "") {
			$("#types-container>tr").hide();
			$("#table_types td:contains-ci(\'" + $(this).val() + "\')").parent("tr").show();
			
		}else{
			$("#types-container>tr").hide();
			$("div.wb-holder").show();
		}
	});
	$.extend($.expr[":"], {
		"contains-ci": function(elem, i, match, array) {
			return (elem.textContent || elem.innerText || $(elem).text() || "").toLowerCase().indexOf((match[3] || "").toLowerCase()) >= 0;
		}
	});
	
	$("#types-container>tr").show();
	$("#all-container>tr").show();
	$("#other-container>tr").show();
	
	$("#cb_any").prop("checked",true);
	$("#cb_type").prop("checked",false);
	$("#cb_day").prop("checked",false);
	$("#cb_week" ).prop( "checked",false);
	$("#cb_month" ).prop( "checked",false );
	$("#cb_year" ).prop( "checked",false );
	$("#cb_all").prop("checked",true);
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
		$("#types-container>tr").show();

		$("#table_all").tablesorter( {sortList: [[2,1]]} );
		$("#table_other").tablesorter( {sortList: [[5,1]]} );
		$("#table_items").tablesorter( {sortList: [[0,0]]} );
		$("#types-container>tr").show();
		
		
	});
</script>


</body>
</html>
