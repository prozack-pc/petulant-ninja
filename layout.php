<?php
defined('DB_WAITING')||(header("HTTP/1.1 403 Forbidden")&die('403.14 - Directory listing denied.'));	

class layout {
	
	private $data = array();
	
	function __construct($dx){
		$this->data = $dx;
	}
	
	public function table_menu($d,$f,$m=false){
		$td;$tr=array();
		$ix=0;
		if(count($d) > 0){
			switch($m){
			case 'items':
				foreach($d as $v){
					$tr[$ix]='<tr><td><div class="wb-holder"><a class="info" href="reports.php?q='.$v['id'].'">'.$v['name'].'</a></div></td></tr>';
					$ix+=1;
				}
				$td = array(obj => $tr,tq => "",tc => "", avg => "",mode => $m,type => $n);
				break;
			case 'types':
				foreach($d as $v){
					$tr[$ix]='<tr><td><div class="wb-holder"><a class="info" href="itemization.php?q='.$v['name'].'&id='.$_GET['id'].'">'.$v['name'].'</a></div></td></tr>';
					$ix+=1;
				}
				$td = array(obj => $tr,tq => "",tc => "", avg => "",mode => $m,type => $n);
				break;
			}
			
		}
		return $td;
	}

	public function table_display($d,$i,$w,$f,$b=1){
		#var_dump($d['mode']);
		switch($d['mode']){
		case 'all':
		case 'buy':
		case 'sell':
		$big = graph_display($d['data'],24,0);
			print '
				<table><tr valign="top"><td style="padding-right:5px">
				<img src="https://imageserver.eveonline.com/Type/'.$d['id'].'_64.png" height="64" style="border:1px solid #666"></td><td width="250">
				<span style="font:10px tahoma,sans-serif normal"><b>'.$d['type'].'</b> -- '.$d['mode'].'<br>';
			print 'Total: '.number_format($d['tc'], 2, '.', ',').' / Quantity: '.number_format($d['tq'], 0, '.', ',').' = '.number_format($d['avg'], 2, '.', ',').' ea.';
			print '<br>graph-max = '.number_format($big, 0, '.', ',').' units</span></td><td style="padding-bottom:5px">'; 
			graph_display($d['data'],24,1);
			print '</td></tr><table>
			';
			break;
		default:
			
			break;
		}

		
		print '
		<table id="table_'.$d['mode'].'" class="tablesorter" border="'.$b.'">
		';
		print '<thead><tr>';
		for($zx = 0;$zx < count($i);$zx++){
			print '<th class="header" width="'.$w[$zx].'" align="'.$f[$zx].'"><b>'.$i[$zx].'</b></th>';
		}
		print '</tr></thead>
		';
		print '<tbody id="'.$d['mode'].'-container">';
		if(is_array($d['obj'])){
			foreach($d['obj'] as $t){ 
				print $t;
			}
		}
		print '</tbody></table>
		<br><br>
		';
		
	}
	
	public function menu_items(){
		global $db;
		$sql = "SELECT name, id FROM eve_itemscache".$_GET['id']." ORDER BY name ASC";
		if(!($result = $db->sql_query($sql))){	message_die(CRITICAL_ERROR,'error: table not found');}
		while(($row = $db->sql_fetchrow($result))){ 
			$list[] = $row;
		}
		return $list;
	}
	public function menu_infotypes(){
		global $db;
		$sql = "SELECT name FROM eve_infotypescache".$_GET['id']." ORDER BY name ASC";
		if(!($result = $db->sql_query($sql))){	message_die(CRITICAL_ERROR,'error: table not found');}
		while(($row = $db->sql_fetchrow($result))){ 
			$list[] = $row;
		}
		return $list;
	}
	public function journal_type_data(){
		global $db;
		#var_dump(strtotime("now"));
		$d1 = strtotime("-8 Weeks");
		$sql = "SELECT * FROM eve_journalcache".$_GET['id']." WHERE ref_type = '".$_GET['q']."' ORDER BY stamp DESC";
		if(!($result = $db->sql_query($sql))){	message_die(CRITICAL_ERROR,'error: table not found');}
		while(($row = $db->sql_fetchrow($result))){ 
			$list[] = $row;
		}
		#var_dump($list);
		return $list;
	}
	public function journal_data(){
		global $db;
		#var_dump(strtotime("now"));
		$d1 = strtotime("-8 Weeks");
		$sql = "SELECT * FROM eve_journalcache".$_GET['id']." WHERE stamp > ".$d1." ORDER BY stamp DESC";
		if(!($result = $db->sql_query($sql))){	message_die(CRITICAL_ERROR,'error: table not found');}
		while(($row = $db->sql_fetchrow($result))){ 
			$list[] = $row;
		}
		#var_dump($list);
		return $list;
	}
	public function transaction_data(){
		global $db;
		#var_dump(strtotime("now"));
		$d1 = strtotime("-8 Weeks");
		$sql = "SELECT * FROM eve_xactioncache".$_GET['id']." WHERE stamp > ".$d1." ORDER BY stamp DESC";
		if(!($result = $db->sql_query($sql))){	message_die(CRITICAL_ERROR,'error: table not found');}
		while(($row = $db->sql_fetchrow($result))){ 
			$list[] = $row;
		}
		#var_dump($list);
		return $list;
	}

	public function seek_ptrIndex($name,$source){
		$ix = 0;
		$iz = count($source);
		while($ix < $iz){
			if($source[$ix] == $name){
				return $ix;
			}
			$ix++;
		}
		return false;
	}

}



?>