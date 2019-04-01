<?php

class CacheControl {
	
	private $db;
	
	private $tables = array('reports');
	private $tblpre = 'eve_';
	private $reportsTTL = 3600;
	public $cacheTTL = '';
		
   	function __construct() {
		$this->reportsTTL = $GLOBALS['eveonline']['reportsTTL'];
		$this->openDatabase();
		$this->initTables();
   	}
   	
   	function __destruct() {
   		$this->db = null;
   	}
   	
   	private function reportInsert($objectID,$dataarray,$table){
   		
				
	   			#$sql = "REPLACE INTO ".$this->tblpre.$table." (ObjectID, id, title, description, points,Timestamp)
	   			#VALUES ('$objectID',".$data['id'].",'".$data['title']."','".$data['description']."','".$data['points']."','".time()."')";
	   			//$title = $data['title'];
	   			//$description = $data['description'];
	   			$sql = "REPLACE INTO ".$this->tblpre.$table." (ObjectID, data, Timestamp)
	   			VALUES ('$objectID',".$objectID.",'".$dataarray."','".time()."')";
	   			#print $sql."<br />";
	   			$sth = $this->db->prepare($sql);
		   		$sth->bindParam(':title', $title, PDO::PARAM_STR);
		   		$sth->bindParam(':description', $description, PDO::PARAM_STR);
		   		#print "Title: ".$data['title']." Description:".$data['description']."<br />";
		   		$sth->execute();
   			
   		
   	}
   	
   	public function genericInsert($objectID,$data, $table){
   		$sql = "DELETE FROM ".$this->tblpre.$table." WHERE ObjectID = '".$objectID."'";
   		$this->db->prepare($sql)->execute();
   		
	   	$splitdata = $this->dataBreak($data);
	   	foreach ($splitdata as $part => $datapart){
	   		$sql = "REPLACE INTO ".$this->tblpre.$table." (ObjectID,Part,Timestamp,Data) VALUES ('$objectID',".$part.",'".time()."',:data)";
			$sth = $this->db->prepare($sql);
			$sth->bindParam(':data', $datapart, PDO::PARAM_STR);
			$sth->execute();
	   	}
   		unset($splitdata);
   	}
   	
   	/**
   	 * Since there is a limit in MySQL on how much data there can be in one row its being split up a bit.
   	 * @param String $data
   	 * @return An array with the data
   	 */
   	private function dataBreak($data){
   		$maxsize = 100000;
   		for ($position = 0; $position < strlen($data); $position += $maxsize){
   			$sub = substr($data, $position, $maxsize);
   			$returndata[] = $sub;
   		}
   		return $returndata; 
   	}
	
   	public function cachestatusTTL(){
		return $this->cacheTTL;
	}
   	public function checkCache($objectID,$table,$fields){
   		$sql = "SELECT Timestamp FROM ".$this->tblpre.$table." WHERE ObjectID = '".$objectID."' LIMIT 1";
   		$timestamp = $this->reportsTTL;
		
   		$sth = $this->db->query($sql);
   		if ($row = $sth->fetch()){
			if ($row['Timestamp']+$timestamp > time()){
				
				$this->cacheTTL = (($row['Timestamp']+$timestamp)-time());
				return TRUE;
			}
   		}
		return FALSE;
   	}

   	
   	/**
   	 * Get the data from cache.
   	 * @param String $objectID
   	 * @param String $table
   	 */
   	public function getData($objectID,$table,$id_list=FALSE){
   		/*
		if (preg_match('/^reports$/i', $table)){
   			$data = $this->getReportData($id_list);
   			return $data;
   		}
		*/
   		$sql = "SELECT Data FROM ".$this->tblpre.$table." WHERE ObjectID = '".$objectID."' ORDER BY Part";
   		if($sth = $this->db->query($sql)) {
   			$returndata = '';
			while ($row = $sth->fetch()){
				
				//var_dump($row['Data']);
   				$returndata .= $row['Data'];
			}
			//var_dump($returndata);
			
   			return $returndata;
		} else {
		  die("Error:" . $sql);
		}
		return false;
   	}

   	/**
   	 * Get the data from cache.
   	 * @param String $objectID
   	 * @param String $table
   	 */
   	public function getReportData($id_list){
		$query = "SELECT * FROM ".$this->tblpre."reports WHERE Part in (0)";
   		if($sth = $this->db->query($query)) {
   			$row = $sth->fetchAll(PDO::FETCH_ASSOC);
   			return $row;
		} else {
		  die("Error:" . $query);
		}
		return false;
   	}
   	
   	
   	private function openDatabase(){
   		$this->db = new SafePDO();
   	}
   	
   	private function initTables(){
   		foreach ($this->tables as $tablename){
	   		$this->createTable($tablename);
			
   		}
   	}
   	
   	private function createTable($tablename){
   		$tablename = $this->tblpre.$tablename;
   		$statement = "CREATE TABLE IF NOT EXISTS $tablename (
					  `ObjectID` varchar(50) NOT NULL,
					  `Data` longblob NOT NULL,
					  `Part` int(11) NOT NULL,
					  `Timestamp` varchar(75) NOT NULL,
					  PRIMARY KEY (`ObjectID`,`Part`)
					) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
   		$sth = $this->db->prepare($statement);
   		$sth->execute();
		
		//print "using table: ".$tablename."<br>";
   	}
}



?>