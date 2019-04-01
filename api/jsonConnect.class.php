<?php

class jsonConnect {
	
	private $region					= '';
	private $stub					= '';
	private $cacheEnabled 			= TRUE;
   	private $useKeys				= FALSE;
   	private $utf8					= '';
   	private $cache;
	private $etag					= '';
	public $cacheTTL				= '';
	
	private $rawdata;
	
	function __construct($tag="") {
		if($tag != ""){
			$this->etag = $tag;
			#var_dump($tag);
		}
		
		$this->cacheEnabled = $GLOBALS['eveonline']['cachestatus'];
		if ($this->cacheEnabled){
	   		$this->cache = new CacheControl();
		}
		if (isset($GLOBALS['eveonline']['keys']['private']) AND isset($GLOBALS['eveonline']['keys']['public'])){
			if (strlen($GLOBALS['eveonline']['keys']['private']) > 1 AND strlen($GLOBALS['eveonline']['keys']['public'] > 1)){
				$this->useKeys = TRUE;
			}
		}
		$this->utf8 = $GLOBALS['eveonline']['UTF8'];
   	}
	
   	public function getReport($region, $stub) {
		$this->region = $region;
		$this->stub = $stub;
		$etag = $this->etag;
				
		switch($region){
		case 'https://login.eveonline.com/oauth/token/':
			$code = $GLOBALS['eveonline']['db']['base64'];
			$itemlist = array(
				"Authorization: Basic $code",
				"Content-Type: application/x-www-form-urlencoded",
				"Host: login.eveonline.com"
            );
			$data = $this->getData($region, $stub, $itemlist, "reports");
			return $data;
			break;
			
		case 'https://login.eveonline.com/oauth/verify/':
			if($stub ==""){ die('invalide stub');}
			$itemlist = array(
				"Authorization: Bearer ".$stub,
				"Host: login.eveonline.com"
			);
			$data = $this->getData($region, "", $itemlist, "reports");
			break;
		case 'https://esi.evetech.net/latest/characters/'.$_GET['id'].'/wallet/transactions/':
		case 'https://esi.evetech.net/latest/characters/'.$_GET['id'].'/wallet/journal/':
			if($stub ==""){ die('invalide stub');}
			$itemlist = array(
				"Authorization: Bearer ".$stub,
				"If-None-Match: ".$etag,
				"Host: esi.evetech.net"
			);
			$data = $this->getData($region, "", $itemlist, "reports");
			break;
		default:
			die('no matching region');
			break;
		
		}
		list($header,$response) = explode("\r\n\r\n",$data,2);
		$head = explode("\r\n",$header);
		foreach($head as $x){
			if(strpos($x,"Etag:")!== false){
				$GLOBALS['eveonline']['db']['etag'] = substr($x,6,strlen($x)-5);
			}
		}
		return $response;
	}
	
	private function getCacheTTL(){
		return $this->cache->cachestatusTTL();
	}
	
	private function getData($url, $fields, $region, $type = FALSE, $id_list = FALSE) {
		
		$objectID = md5($url.$fields);
		if($fields == ""){
			$objectID = md5($url.$this->stub);
		}
		if($type AND $this->cacheEnabled AND $this->cache->checkCache($objectID,$type,NULL)){
			$object= $this->cache->getData($objectID, $type, NULL);
			
			$GLOBALS['eveonline']['db']['cacheTTL'] = $this->getCacheTTL();
			#print "cached..";
			
			return $object;
			
   		}else{
			
			
			
			if($fields == ""){
				$ch = curl_init();
				$options = array(
					CURLOPT_URL => $url,
					CURLOPT_RETURNTRANSFER => true,         // return web page
					CURLOPT_HEADER         => true,        // don't return headers
					CURLOPT_FOLLOWLOCATION => false,         // follow redirects
				// CURLOPT_ENCODING       => "utf-8",           // handle all encodings
					CURLOPT_AUTOREFERER    => true,         // set referer on redirect
					CURLOPT_CONNECTTIMEOUT => 20,          // timeout on connect
					CURLOPT_TIMEOUT        => 20,          // timeout on response
					//CURLOPT_POST            => 1,            // i am sending post data
					//CURLOPT_POSTFIELDS     => "",    	// this are my post vars
					CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl
					CURLOPT_SSL_VERIFYPEER => false,        //
					CURLOPT_VERBOSE        => 1,
					CURLOPT_HTTPHEADER     => $region
				);
				curl_setopt_array($ch,$options);
				$object = curl_exec($ch);
				if($object == false){
					echo "<br>curl error.<br><br>";	
					var_dump(curl_getinfo($ch));
				}
			}else{
				$ch = curl_init($url.$fields);
				$options = array(
					CURLOPT_RETURNTRANSFER => true,         // return web page
					CURLOPT_HEADER         => false,        // don't return headers
					CURLOPT_FOLLOWLOCATION => false,         // follow redirects
				// CURLOPT_ENCODING       => "utf-8",           // handle all encodings
					CURLOPT_AUTOREFERER    => true,         // set referer on redirect
					CURLOPT_CONNECTTIMEOUT => 20,          // timeout on connect
					CURLOPT_TIMEOUT        => 20,          // timeout on response
					CURLOPT_POST            => 1,            // i am sending post data
					CURLOPT_POSTFIELDS     => $fields,    	// this are my post vars
					CURLOPT_SSL_VERIFYHOST => 0,            // don't verify ssl
					CURLOPT_SSL_VERIFYPEER => false,        //
					CURLOPT_VERBOSE        => 1,
					CURLOPT_HTTPHEADER     => $region
												
				);
				curl_setopt_array($ch,$options);
				$object = curl_exec($ch);
				if($object == false){
					echo "<br>curl error.<br><br>";	
					var_dump(curl_getinfo($ch));
				}
			}
			$curl_errno = curl_errno($ch);
			$curl_error = curl_error($ch);
			curl_close($ch);
			
			if(($this->cacheEnabled)&&($type == "reports")){
				$this->cache->genericInsert($objectID,$object,$type);
			}
		}
 		$this->rawdata = $object;
		$returndata = $object;
		
		return $returndata;
	}

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

}

?>