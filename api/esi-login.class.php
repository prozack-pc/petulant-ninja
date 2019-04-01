<?php
error_reporting(E_ALL ^ E_NOTICE);

require_once('CacheControl.class.php');
require_once('SafePDO.class.php');
require_once('jsonConnect.class.php');
require_once('Report.class.php');

class esi_login {
	
	private $stub;
	private $region;
	private $cacheEnabled = TRUE;
	private $excludeFields = FALSE;
	private $raw = "";
	private $direct = false;
	private $etag = "";
	private $userdata = array();
			
	/**
	 * This will load the main report class. 
	 * No connections are made until a get function is called after the cache expires. 
	 */
   	function __construct($x) {
   		
		$this->region = "https://login.eveonline.com/oauth/token/";
		$this->stub = "?grant_type=authorization_code&code=$x";
		
		$GLOBALS['eveonline']['cachestatus'] = TRUE;
		$GLOBALS['eveonline']['reportsTTL'] = 1200;
   		$GLOBALS['eveonline']['UTF8'] = FALSE;
   		$GLOBALS['eveonline']['debug']['emblem'] = FALSE;
   		$GLOBALS['eveonline']['locale'] = FALSE;
	}
	
	public function getReport($x,$region=""){
   		if (strlen($region) == 0) {
   			$region = $this->region;
   		}
		$report = new Report($x,$region,$this->etag);
		 
		if($report->IsValid()){	
			if($this->db_init()){
				$data = $report->getData();
				$obj = json_decode($data,true);
				switch($this->region){
				case "https://login.eveonline.com/oauth/token/":
					$this->userdata = $obj;
					break;
				case "https://login.eveonline.com/oauth/verify/":
					#var_dump($obj);
					$this->db_parseEntry($obj);
					$this->db_parseKey($obj);
					$this->redirect("");
					break;
				default:
					
					break;
				}
				
			}
		}else{
			#print 'null report<br>';
		}
		return $report;
	}
   	private function db_init(){
		global $db;
		
		$sql = "CREATE TABLE IF NOT EXISTS eve_oauth (
			id varchar(12) NOT NULL,
			name varchar(128) NOT NULL,
			expire varchar(32) NOT NULL,
			scope varchar(128) NOT NULL,
			token_type varchar(32) NOT NULL,
			owner_hash varchar(64) NOT NULL,
			wallet_tag varchar(64) NOT NULL,
			journal_tag varchar(64) NOT NULL,
			PRIMARY KEY (`id`,`owner_hash`)
		)	ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		if(!($result = $db->sql_query($sql))){return FALSE;}
		
		$sql = "CREATE TABLE IF NOT EXISTS eve_authkey (
			id varchar(64) NOT NULL,
			token varchar(128) NOT NULL,
			value varchar(128) NOT NULL,
			last varchar(128) NOT NULL,
			PRIMARY KEY (`id`)
		)	ENGINE=MyISAM DEFAULT CHARSET=utf8;";
		if(!($result = $db->sql_query($sql))){die('eerror create db');}
		return true;
	}
	public function db_parseKey($x){
		global $db;
		global $dx;
		if(!(isset($x['CharacterOwnerHash']))){ die('no owner hash!!');}		
		$sql = "SELECT * FROM eve_authkey WHERE id='".$x['CharacterOwnerHash']."'";
		if(!($result = $db->sql_query($sql))){	die('db_parseKey:  error!!');}
		if(!($row = $db->sql_fetchrow($result))){
			$sql = "INSERT INTO eve_authkey (id, token, value, last) VALUES ('".$x['CharacterOwnerHash']."','".$dx['refresh_token']."','".$dx['access_token']."','".$dx['access_token']."')";
			if(!($result = $db->sql_query($sql))){	die('insert failed.');}
		}else{
			$sql = "UPDATE eve_authkey SET token='".$dx['refresh_token']."' WHERE id='".$x['CharacterOwnerHash']."'";
			if(!($result = $db->sql_query($sql))){	die('db_parseKey:  update error!!');}
		}
		return $x['CharacterOwnerHash'];
	}
	public function db_parseEntry($x){
		global $db;
		if(!(isset($x['CharacterOwnerHash']))){ die('no character id!!');}		
		$sql = "SELECT * FROM eve_oauth WHERE id='".$x['CharacterID']."'";
		if(!($result = $db->sql_query($sql))){	die('db_parseEntry:  error!!');}
		if(!($row = $db->sql_fetchrow($result))){
			$sql = "INSERT INTO eve_oauth (id, name, expire, scope, token_type, owner_hash, wallet_tag, journal_tag) VALUES ('".$x['CharacterID']."','".$x['CharacterName']."','".$x['ExpiresOn']."','".$x['Scopes']."','".$x['TokenType']."','".$x['CharacterOwnerHash']."','','')";
			if(!($result = $db->sql_query($sql))){	die('insert failed.');}
		}
	}
	public function setReportCacheTTL($seconds){
   		$GLOBALS['eveonline']['reportsTTL'] = $seconds;
   	}
	public function set_etag($x){
		$this->etag = $x;
	}
	public function get_etag(){
		return $this->etag;
	}
	public function getRegion(){
		return $this->region;
	}		
	public function getStub(){
		return $this->stub;
	}
	public function setStub($x){
		$this->stub = $x;
	}
	public function setRegion($x){
		$this->region = $x;
	}
	public function setReturn($x){
		$this->direct = $x;
	}
	public function getCntr(){
		return $this->cntr;
	}
	public function getRaw(){
		return $this->raw;
	}
	
	private function redirect($x){
		header('Location: http://localhost/index.php');
		
	}
	
}