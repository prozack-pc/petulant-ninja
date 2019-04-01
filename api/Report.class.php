<?php
/**
 * EVE-ONLINE:  Master Report Class
 * Remastered for Eve Online by Rick Johnson
 *
 * Original Header Info --
 * Master class for the battle.net WoW armory
 * @author Thomas Andersen <acoon@acoon.dk>
 * @copyright Copyright (c) 2011, Thomas Andersen, http://sourceforge.net/projects/wowarmoryapi
 * @version 3.5.1
 * 
 */
 
class Report {
	
	private $region;
	private $stub;
	private $reportData;
	#private $fields;
	#private $cache;
		
   	function __construct($stub, $region, $etag) {
		#var_dump($etag);
		if(strlen($region)==0){
			$region = $this->region;
		}
		$this->region = $region;
   		$this->stub = $stub;
		$jsonConnect = new jsonConnect($etag);
		$this->reportData = $jsonConnect->getReport($this->region, $this->stub);
	}
	
	
	/**
   	 * Test if report is valid and loaded.
   	 * @return Returns TRUE if valid, else FALSE
   	 */
   	public function isValid(){
   		if($this->reportData){
			return TRUE;
		}else{
			return FALSE;
		}
	}
   	
	
	/**
   	 * Test if guild is valid and loaded.
   	 * @return Returns TRUE if valid, else FALSE
   	 */
   	public function testReport(){
   		if(!($this->reportData)){
   			return FALSE;
   		}
   		return TRUE;
   	}
   	
   	/**
   	 * Extract all guild data
   	 * @return A large array with all raw information
   	 */
	public function getData() {
	
		return $this->reportData;
   	}
	
	public function set_fields($x){
		$this->fields = $x;
	}
	public function get_fields(){
		return $this->fields;
	}
	
}


?>
