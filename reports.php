<!DOCTYPE html>
<?php
require_once('config.php');
include 'sql_db/db_sql.php';
	
define('DB_WAITING',microtime());
include_once('layout.php');
require_once('api/esi-login.class.php');

function table_data($item,$type){
	#var_dump($item);
	#var_dump($type);
	
	global $db;
	$list = array();
	//var_dump(strtotime("now"));
	$d1 = strtotime($GLOBALS['eveonline']['db']['all']);
	$sql;
	switch($type){
		case 1:
		case 0:
			$sql = "SELECT * FROM eve_xactioncache".$_GET['id']." WHERE type_id = ".$item." AND is_buy = ".$type." ORDER BY stamp DESC";
			if(!($result = $db->sql_query($sql))){	message_die(CRITICAL_ERROR,'db_report: xactioncache not found');}
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

function table_account($d,$f,$m=false){
	global $db;
	
	$sd = strtotime("-1 day");
	$sw = strtotime("-1 week");
	$sm = strtotime("-1 month");
	$sy = strtotime("-1 year");
	
	$i;$b=0;
	$td;$n;$tr=array();
	$tq=0;$tc=0;$avg=0;$ix=0;
	if(count($d) > 0){
		#var_dump($m);
		switch($m){
		case 'all':
		case 'buy':
		case 'sell':
			$i = $_GET['q'];
			$sql = "SELECT name FROM eve_itemtypes WHERE id='".$i."'";
			
			break;
		}
		#print $sql."<br>";
		if(!($result = $db->sql_query($sql))){	die("error: table `".$_GET['id']."_itemcache` not found.");}
		if(!($row = $db->sql_fetchrow($result))){ die("error: table `".$_GET['id']."_itemcache` no row.");}
		$n = $row['name'];
		#var_dump($n);
		
		foreach($d as $v){
				#var_dump($v);
				
				$q=$v['quantity'];
				$p=$v['unit_price'];
				if($v['is_buy']){ $c=$q*$p*-1;}else{ $c=$q*$p; }
				$tq+=$q;$tc+=$c;
				if($q > $b){ $b = $q;}			
				$obj = array(
					c1 => $q,
					c2 => $p,
					c3 => $c,
					c4 => $v['stamp']
				);
				$tx[$ix]=$obj;
				
				$holder = "all";
				if($obj['c4'] > $sy){$holder = "year";}
				if($obj['c4'] > $sm){$holder = "month";}
				if($obj['c4'] > $sw){$holder = "week";}
				if($obj['c4'] >= $sd ){ $holder = "day";}
				
				$tr[$ix]='<tr class="'.$holder.'">';
				$tr[$ix].='<td align="'.$f[0].'">'.$q.'</td>';
				$tr[$ix].='<td align="'.$f[1].'">'.number_format($p,2,'.','').'</td>';
				
				$cf = "";
				if($v['is_buy'] == '1'){$cf = "color:#f00";}
				$tr[$ix].='<td align="'.$f[2].'"><a style="'.$cf.'">'.number_format($c,2,'.','').'</a></td>';
				
				$station = $v['location_id'];
				/*
				$sql = "SELECT name FROM ".$_GET['id']."_stationcache WHERE id='".$station."'";
				if(!($result = $db->sql_query($sql))){	die("error: table `".$_GET['id']."_stationcache` not found.");}
				if(($row = $db->sql_fetchrow($result))){ $station = $row['name']; }
				*/
				$tr[$ix].='<td align="'.$f[3].'">'.$station.'</td>';
				
				$tr[$ix].='<td align="'.$f[4].'">'.date('Y-m-d H:i:s', $v['stamp']).'</td>';
				$tr[$ix].='<td align="'.$f[5].'">'.$v['client_id'].'</td></tr>';
				$ix+=1;
			}
			$avg=$tc/$tq;
			$td = array(obj => $tr,data => $tx,tq => $tq,tc => $tc,avg => $avg,mode => $m,type => $n,id => $i,big => $b);
			
			switch($m){
			case 'buy':
				/*
				$sql = "SELECT * FROM wallet_items WHERE id = '".$i."'";
				if(!($result = $db->sql_query($sql))){	message_die(CRITICAL_ERROR,'error: table `wallet_items` not found.');}
				if(!($row = $db->sql_fetchrow($result))){
					$sql = "INSERT INTO wallet_items (name,id,buy,sell,buy_x,sell_x) VALUES ('".$n."','".$i."',".$avg.",0.00,".$tq.",0.00)";
					if(!($result = $db->sql_query($sql))){	message_die(CRITICAL_ERROR,'error: table `wallet_items` insert failed for buy.');}
				}else{
					$sql = "UPDATE wallet_items SET buy=".$avg.", buy_x=".$tq." WHERE id='".$i."'";
					if(!($result = $db->sql_query($sql))){	message_die(CRITICAL_ERROR,'error: table `wallet_items` update failed for buy.');}
				}
				*/
				break;
			case 'sell':
				/*
				$sql = "SELECT * FROM wallet_items WHERE id = '".$i."'";
				if(!($result = $db->sql_query($sql))){	message_die(CRITICAL_ERROR,'error: table `wallet_items` not found.');}
				if(!($row = $db->sql_fetchrow($result))){
					$sql = "INSERT INTO `wallet_items` (name,id,buy,sell,buy_x,sell_x) VALUES ('".$n."','".$i."',0.00,".$avg.",0.00,".$tq.")";
					if(!($result = $db->sql_query($sql))){	message_die(CRITICAL_ERROR,'error: table `wallet_items` insert failed for sell.');}
				}else{
					$sql = "UPDATE wallet_items SET sell=".$avg.", sell_x=".$tq." WHERE id='".$i."'";
					if(!($result = $db->sql_query($sql))){	message_die(CRITICAL_ERROR,'error: table `wallet_items` update failed for sell.');}
				}
				*/
				break;
			}
		
	}
	return $td;
}

function graph_presort($x){
	#var_dump($x);
	$ix = 0;
	$q;$p;$d;$t;$i;
	if(count($x) > 0){
		foreach($x as $v){
			$q[$ix] = $v['c1'];
			$p[$ix] = $v['c2'];
			$c[$ix] = $v['c3'];
			$t[$ix] = $v['c4'];
			$i[$ix] = $ix;
			$ix++;
		}
	}
	$v = array(
		t => $t,
		i => $i,
		q => $q,
		p => $p,
		c => $c
	);
	#var_dump($v);
	return $v;
}	

function graph_shellSort($s){
	
	$a1 = $s['t'];
	$a2 = $s['i'];
	
	$n=sizeof($a1);
	$t=ceil(log($n,2));
	$d[1] = 1;
	for ($i = 2; $i <= $t; $i++) {
		$d[$i] = 2 * $d[$i - 1] + 1;
	}
	$d = array_reverse($d);
	$z = NULL;
	foreach ($d as $curIncrement) {	
		
		for ($i = $curIncrement; $i < $n; $i++) {
			$x = $a1[$i];
			$x1 = $a2[$i];
			$j = $i - $curIncrement;
			while ($j >= 0 && $x < $a1[$j]) {
				$a1[$j + $curIncrement] = $a1[$j];
				$a2[$j + $curIncrement] = $a2[$j];
				$j = $j - $curIncrement;
			}
			$a1[$j + $curIncrement] = $x;
			$a2[$j + $curIncrement] = $x1;
		}
	}
	return $a2;
}

function graph_display($data,$b1,$show=false){
	$b = 0;
	if($data!=NULL){
		$ex = 86400/24*$b1;$ez=0;$ey = array();
		$z1 = graph_presort($data);
		$iz=count($z1['t'])-1;$ix=0;
		$zx = graph_shellSort($z1);
		$dx = intval(strtotime("-12 weeks")/86400)*86400-3600;	//$z1['t'][$zx[0]];
		$dy = intval(strtotime("now")/86400)*86400-3600;        //$z1['t'][$zx[$iz]]+$ex;
		$tx = $dx;
		
		while($tx <= $dy){
			#print "<a style='color:red'>".date('Y-m-d H:i:s', $tx)."</a>";
			
			$tq = 0;
			while(($z1['t'][$zx[$ix]] <= $tx + 86400)&&($ix <= $iz)){
				$tq += $z1['q'][$zx[$ix]];
				#print $z0['q'][$zx[$ix]]."";
				$ix++;
			}
			#print $tq."<br>";
			if($tq >= $b){ $b = $tq;}
			$ey[$ez] = array(
				arg1 => $tq,
				arg2 => $tx
			);
			$tx+=$ex;
			$ez++;
		}
		
		if($show){
			$d1 = intval( ($dy - $dx) / ($ex*14) )+1;
			$d2 = 0;
			while($d2 < $d1){
				if($d2 == $d1 - 1){
					print '<a style="font:8px tahoma,sans-serif normal;padding-right:15px">'.date("m-d-Y",$dx+($ex*14*$d2)).'</a><br>';
				}else{
					print '<a style="font:8px tahoma,sans-serif normal;padding-right:15px">'.date("m-d-Y",$dx+($ex*14*$d2)).'</a>';
				}
				$d2++;
			}
				
			//$ey = array_reverse($ey);	
			$ix = 0;
			foreach($ey as $v){
				#var_dump($v);
				$cvl = "#f00";
				$wx = 42 / (($b*0.99)+0.01);
				if($ix > 0){print '<span title="'.$v['arg1'].'" style="margin-top:3px;float:left;height:'.(($v['arg1']*$wx)).'px;width:4px;display:inline;background:'.$cvl.'"></span>';}
				$ix++;
			}
		}else{
			return $b;
		}
	}
}



$output = new layout("");	
if((isset($_GET['q']))&&(isset($_GET['id']))){

	//$menu = $output->menu_items();
	//$all = $output->transaction_data();
	
	$buy = table_data($_GET['q'],1);
	$sell = table_data($_GET['q'],0);
	$d1=table_account($buy,$th_format,"buy");
	$d2=table_account($sell,$th_format,"sell");
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
	font: 12px tahoma,sans-serif normal;
	color: #000;
	text-decoration: none;
}
a.info:link, a.info:visited, a.info:active, a.info:hover{
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

.menubox {
	font:12px tahoma, san-serif normal;padding-top:3px
}
</style>
</head>
<body>
<?php

#var_dump($all);

?>	

<table>
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
			</td><td class="menubox" style="padding:5px 0 0 20px"><a href="http://localhost/">home</a><br><?php if(isset($_GET['id'])){ print '<a href="http://localhost/transactions.php?id='.$_GET['id'].'">top</a>';} ?></td></tr></tr>
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
	</td><td style="padding:0 0 10px 5px">
		
		
<?php 
	
	
	
	
	$th_list = array("units","price","cost","station","date","merchant");
	$th_width = array(90,90,90,120,185,80);
	$th_format = array("right","right","right","left","left","left");
	if($buy != NULL){
		$d1=table_account($buy,$th_format,"buy");
		#var_dump($d1['data']);
		#graph_display($d1['data'],12,true);
		#print '<br><br><br><br>';
		$output->table_display($d1,$th_list,$th_width,$th_format,0);}
	if($sell != NULL){
		$d2=table_account($sell,$th_format,"sell");
		#graph_display($d2['data'],12,true);
		#print '<br><br><br><br>';
		$output->table_display($d2,$th_list,$th_width,$th_format,0);		
	}
	if($all != NULL){
		$d3=table_account($all,$th_format,"all");
		#var_dump($d3['type']);
		#graph_display($d2['data'],12,true);
		#print '<br><br><br><br>';
		$output->table_display($d3,$th_list,$th_width,$th_format,0);		
	}

?>	
		
	</td></tr>
	</table>

<script type="text/javascript">
	
	jQuery(document).ready(function($){
		//$("#table_item").tablesorter( {sortList: [[0,0]]} );
		//$("#table_all").tablesorter( {sortList: [[0,0]]} );
		//$("#table_sell").tablesorter( {sortList: [[0,0]]} );
		//$("#table_buy").tablesorter( {sortList: [[0,0]]} );
		//$("#item-container>tr").show();
	});
	
</script>	
		
<script type="text/javascript">
	
	$("#items-container>tr").show();
	
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
	$("#sell-container>tr").show();
	$("#buy-container>tr").show();
	$("#all-container>tr").hide();
	
	$("#cb_any").prop("checked",true);
	$("#cb_type").prop("checked",false);
	$("#cb_day").prop("checked",false);
	$("#cb_week" ).prop( "checked",false);
	$("#cb_month" ).prop( "checked",false );
	$("#cb_year" ).prop( "checked",false );
	$("#cb_all" ).prop( "checked",true );
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

				
				$("#table_buy tr.day").hide();
				$("#table_sell tr.day").hide();
				$("#table_all tr.day").hide();
				$("#table_buy tr.week").hide();
				$("#table_sell tr.week").hide();
				$("#table_all tr.week").hide();
				$("#table_buy tr.month").hide();
				$("#table_sell tr.month").hide();
				$("#table_all tr.month").hide();
				$("#table_buy tr.year").hide();
				$("#table_sell tr.year").hide();
				$("#table_all tr.year").hide();
				$("#table_buy tr.all").hide();
				$("#table_sell tr.all").hide();
				$("#table_all tr.all").hide();
				
				if($("#cb_day").is(":checked")){
					$("#table_buy tr.day").show();
					$("#table_sell tr.day").show();
					$("#table_all tr.day").show();
				}
				if($("#cb_week").is(":checked")){
					$("#table_buy tr.day").show();
					$("#table_sell tr.day").show();
					$("#table_all tr.day").show();
					$("#table_buy tr.week").show();
					$("#table_sell tr.week").show();
					$("#table_all tr.week").show();
				}
				if($("#cb_month").is(":checked")){
					$("#table_buy tr.day").show();
					$("#table_sell tr.day").show();
					$("#table_all tr.day").show();
					$("#table_buy tr.week").show();
					$("#table_sell tr.week").show();
					$("#table_all tr.week").show();
					$("#table_buy tr.month").show();
					$("#table_sell tr.month").show();
					$("#table_all tr.month").show();
				}
				if($("#cb_year").is(":checked")){
					$("#table_buy tr.day").show();
					$("#table_sell tr.day").show();
					$("#table_all tr.day").show();
					$("#table_buy tr.week").show();
					$("#table_sell tr.week").show();
					$("#table_all tr.week").show();
					$("#table_buy tr.month").show();
					$("#table_sell tr.month").show();
					$("#table_all tr.month").show();
					$("#table_buy tr.year").show();
					$("#table_sell tr.year").show();
					$("#table_all tr.year").show();
				}
				if($("#cb_all").is(":checked")){
					$("#table_buy tr.day").show();
					$("#table_sell tr.day").show();
					$("#table_all tr.day").show();
					$("#table_buy tr.week").show();
					$("#table_sell tr.week").show();
					$("#table_all tr.week").show();
					$("#table_buy tr.month").show();
					$("#table_sell tr.month").show();
					$("#table_all tr.month").show();
					$("#table_buy tr.year").show();
					$("#table_sell tr.year").show();
					$("#table_all tr.year").show();
					$("#table_buy tr.all").show();
					$("#table_sell tr.all").show();
					$("#table_all tr.all").show();
				}
				
			}
			if(($(this).val()=="type")||($(this).val()=="any")){
				$("#cb_type").prop("checked",false);
				$("#cb_any" ).prop( "checked", false );
				$(this).prop("checked",true);
			}
			return;
		}/*
		if(($(this).val()=="all")){
			$("#cb_day").prop("checked",true);
			$("#cb_week" ).prop( "checked", false );
			$("#cb_month" ).prop( "checked", false );
			$("#cb_year" ).prop( "checked", false );
			$(this).prop("checked",false);
		}*/
		if(($(this).val()=="type")||($(this).val()=="any")){
			$("#cb_type").prop("checked",true);
			$("#cb_any" ).prop( "checked",true);
			$(this).prop("checked",false);
			
		}
	});

	jQuery(document).ready(function($){
		
		$("#table_items").tablesorter( {sortList: [[0,0]]} );
		$("#items-container>tr").show();

		
		$("#table_all").tablesorter( {sortList: [[4,1]]} );
		$("#table_buy").tablesorter( {sortList: [[4,1]]} );
		$("#table_sell").tablesorter( {sortList: [[4,1]]} );
		$("#table_item").tablesorter( {sortList: [[0,0]]} );
		$("#item-container>tr").show();
		$("#buy-container").show();
		$("#sell-container").show();
		
		$("#table_buy tr.day").show();
		$("#table_sell tr.day").show();
		$("#table_all tr.day").show();
		$("#table_buy tr.week").show();
		$("#table_sell tr.week").show();
		$("#table_all tr.week").show();
		$("#table_buy tr.month").show();
		$("#table_sell tr.month").show();
		$("#table_all tr.month").show();
		$("#table_buy tr.year").show();
		$("#table_sell tr.year").show();
		$("#table_all tr.year").show();
		$("#table_buy tr.all").show();
		$("#table_sell tr.all").show();
		$("#table_all tr.all").show();
		
	});
</script>

</body>
</html>