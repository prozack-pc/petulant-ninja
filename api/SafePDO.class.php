<?php
Class SafePDO extends PDO {
 /*
        public static function exception_handler($exception) {
            // Output the exception details
            die('Uncaught exception: '. $exception->getMessage());
        }
 */
        public function __construct() {
/*
            // Temporarily change the PHP exception handler while we . . .
            set_exception_handler(array(__CLASS__, 'exception_handler'));
*/
            // . . . create a PDO object
            $driver = $GLOBALS['eveonline']['db']['driver'];
            $host = $GLOBALS['eveonline']['db']['hostname'];
            $dbname = $GLOBALS['eveonline']['db']['dbname'];
            $username = $GLOBALS['eveonline']['db']['username'];
            $password = $GLOBALS['eveonline']['db']['password'];
            if (isset($GLOBALS['eveonline']['db']['port'])){
            	$port = $GLOBALS['eveonline']['db']['port'];
            	$dsn = $driver.':host='.$adress.';port='.$port.';dbname='.$dbname;
            } else {
            	$dsn = $driver.':host='.$host.';dbname='.$dbname;
            }
            parent::__construct($dsn, $username, $password);

            // Change the exception handler back to whatever it was before
            restore_exception_handler();
        }

}
?>