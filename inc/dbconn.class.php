<?php

/*
	database connectivity
*/

class DBConn
{
	private $db;
	
	public function __construct($conn = true) {
	
		if ($conn) {
			$this->connect();
		}
	}

	function connect() {
	
		$dsn = 'mysql:host='.DBHOST.';dbname='.DBNAME;

	    try {
			$this->db = new PDO($dsn, DBUSER, DBPWD);
			$this->db->exec("set names utf8");
		} 
		catch (PDOException $e) {
			print "Database connection failed: {$e->getMessage()} <br/>";
			die();    
		}
	}
	
	function begin()	{ $this->db->beginTransaction(); }
	function commit()	{ $this->db->commit(); }
	function rollback()	{ $this->db->rollback(); }

	function query($sql, $params) {		
		
		$statement = $this->db->prepare($sql);
	    $result = $statement->execute($params);
		
		return $result ? $statement : $result;
	}
}

?>
