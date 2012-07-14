<?php

class BusAPI
{
	private $dbh;

	public function __construct() {
	
		$this->dbh = new DBConn();
	}
	
	public function getclosestnode($lat, $lon) {
	
		$sql = <<<SQL
SELECT pid, name, SQRT(POW((lat - :lat), 2) + POW((lon - :lon), 2)) AS dist
FROM place ORDER BY dist LIMIT 1
SQL;
	
		$res = $this->dbh->query($sql, array(':lat' => $lat, ':lon' => $lon));
		$return = false;

		if($res && ($row = $res->fetch()) != false) {
			$return = $row;
		}
		
		return $return;
	}
	
	public function getnodebyname($name) {
	
		$sql = <<<SQL
SELECT pid, name FROM place WHERE name LIKE :name LIMIT 1
SQL;
	
		$res = $this->dbh->query($sql, array(':name' => "%$name%"));
		$return = false;

		if($res && ($row = $res->fetch()) != false) {
			$return = $row;
		}
		
		return $return;
	}
	
	public function getbus($fromnode, $tonode) {
	
		$resarray = array();
	
		$resarray['title'] = "Buses from {$fromnode['name']} to {$tonode['name']}";
	
		foreach($this->getlinks($fromnode['pid'], $tonode['pid']) as $b) {
	
			$link = array();
		
			$link['nobuses'] 	= (int)$b['type'];
			$link['totaldist'] 	= $b['dist1'] + $b['dist2'] + $b['dist3'];
		
			if($b['busid1'] != 0) {
		
				/* bus 1, source deets */
				$bus = $this->getbusdetails($b['busid1']);
			
				$link['inst'][] = "Take the <strong>{$bus['routeno']}"
				."</strong> ({$bus['from']} - {$bus['to']}) bus at <strong>"
				."{$fromnode['name']}</strong>";
			}
		
			if($b['busid2'] == 0) {
			
				$link['inst'][] = "Get down at <strong>{$tonode['name']}</strong>"
				."<div class=\"dist\">" . ($b['dist1'] / 1000) . " km</div>";
			}
			else {
		
				/* changeover point 1 deets */	
				$link['inst'][] = "Get down at <strong>"
				. $this->getplacename($b['changeid1']) . "</strong>"
				. "<div class=\"dist\">" . ($b['dist1'] / 1000) . " km</div>";
		
				/* bus 2 deets */
				$bus = $this->getbusdetails($b['busid2']);
			
				$link['inst'][] = "Take the <strong>{$bus['routeno']}"
				."</strong> ({$bus['from']} - {$bus['to']}) bus";
			
				if($b['busid3'] == 0) {
			
					$link['inst'][] = "Get down at <strong>{$tonode['name']}</strong>"
					."<div class=\"dist\">" . ($b['dist2'] / 1000) . " km</div>";
				}
				else {
		
					/* changeover point 2 deets */	
					$link['inst'][] = "Get down at <strong>"
					.$this->getplacename($b['changeid2']) . "</strong>"
					."<div class=\"dist\">" . ($b['dist2'] / 1000) . " km</div>";
		
					/* bus 3 deets */
					$bus = $this->getbusdetails($b['busid3']);
			
					$link['inst'][] = "Take the <strong>{$bus['routeno']}"
					."</strong> ({$bus['from']} - {$bus['to']}) bus";
				
				
					$link['inst'][] = "Get down at <strong>{$tonode['name']}</strong>"
					."<div class=\"dist\">" . ($b['dist3'] / 1000) . " km</div>";
				}
			}
		
			$resarray['links'][] = $link;
		}
	
		/* echo all deets */
		return json_encode($resarray);

	}
	
	public function getlinks($fromnode, $tonode) {
	
		/* comparison function */
		function compareroutes($routea, $routeb) {
		
			if($routea['type'] != $routeb['type']) {
			
				return $routea['type'] > $routeb['type'];
			}
			else {
			
				return ($routea['dist1'] + $routea['dist2'] + $routea['dist3']) > 
					($routeb['dist1'] + $routeb['dist2'] + $routeb['dist3']);
			}
		}
	
		$all = array();
		
		$a = $this->findonebus($fromnode, $tonode);
		$b = $this->findtwobus($fromnode, $tonode);
		$c = $this->findthreebus($fromnode, $tonode);
		
		$all = array_merge($a, $b, $c);
		
		uasort($all, 'compareroutes');
		
		return $all;
	}
	
	public function findonebus($from, $to) {

		$sql = <<<SQL
SELECT '1' AS type, s1.`bid` AS busid1, '0' AS busid2, '0' AS busid3,
'0' AS changeid1, '0' AS changeid2,
(s2.`distance` - s1.`distance`) AS dist1, '0' AS dist2, '0' AS dist3 
FROM `stop` AS s1 INNER JOIN `stop` AS s2
ON s1.`bid` = s2.`bid`
WHERE s1.`pid` = :from AND s2.`pid` = :to AND s2.`distance` > s1.`distance`
ORDER BY dist1 LIMIT 5;
SQL;
	
		$res = $this->dbh->query($sql, array(':from' => $from, ':to' => $to));
		$return = array();

		while($res && ($row = $res->fetch()) != false) {
			$return[] = $row;
		}

		return $return;
	}
	
	public function findtwobus($from, $to) {

		$sql = <<<SQL
SELECT '2' AS type, s1.`bid` AS busid1, s3.`bid` AS busid2, '0' AS busid3,
ch1.`changeid` AS changeid1, '0' AS changeid2, 
(s2.`distance` - s1.`distance`) AS dist1, 
(s4.`distance` - s3.`distance`) AS dist2, '0' AS dist3 
FROM `changeover` AS ch1, `stop` AS s1 INNER JOIN `stop` AS s2
ON s1.`bid` = s2.`bid`
INNER JOIN `stop` AS s3
ON s2.`pid` = s3.`pid`
INNER JOIN `stop` AS s4
ON s3.`bid` = s4.`bid`  
WHERE s1.`pid` = :from AND s2.`pid` = ch1.`changeid` AND s4.`pid` = :to 
AND s2.`distance` > s1.`distance` AND s4.`distance` > s3.`distance` AND
s1.`bid` <> s3.`bid`
ORDER BY (dist1 + dist2)  LIMIT 5;
SQL;
	
		$res = $this->dbh->query($sql, array(':from' => $from, ':to' => $to));
		$return = array();

		if($res && ($row = $res->fetch()) != false) {
			$return[] = $row;
		}

		return $return;
	}

	public function findthreebus($from, $to) {

		$sql = <<<SQL
SELECT '3' AS type, s1.`bid` AS busid1, s3.`bid` AS busid2, s5.`bid` AS busid3, 
ch1.`changeid` as changeid1, ch2.`changeid` as changeid2, 
(s2.`distance` - s1.`distance`) as dist1, 
(s4.`distance` - s3.`distance`) as dist2, 
(s6.`distance` - s5.`distance`) as dist3
FROM `changeover` AS ch1, `changeover` AS ch2, `stop` AS s1 
INNER JOIN `stop` AS s2
ON s1.`bid` = s2.`bid`
INNER JOIN `stop` AS s3
ON s2.`pid` = s3.`pid`
INNER JOIN `stop` AS s4
ON s3.`bid` = s4.`bid`
INNER JOIN `stop` AS s5
ON s4.`pid` = s5.`pid`
INNER JOIN `stop` AS s6
ON s5.`bid` = s6.`bid`  
WHERE s1.`pid` = :from AND s2.`pid` = ch1.`changeid` AND s4.`pid` = ch2.`changeid` 
AND s6.`pid` = :to AND s2.`distance` > s1.`distance` AND s4.`distance` > s3.`distance` 
AND s6.`distance` > s5.`distance` AND s1.`bid` <> s3.`bid` AND s3.`bid` <> s5.`bid`
AND s1.`bid` <> s5.`bid` ORDER BY (dist1 + dist2 + dist3) LIMIT 5;
SQL;

		$res = $this->dbh->query($sql, array(':from' => $from, ':to' => $to));
		$return = array();

		while($res && ($row = $res->fetch()) != false)
		{
			$return[] = $row;
		}

		return $return;
	}
	
	public function getbusdetails($busid) {
	
		$res	= $this->dbh->query("SELECT `routeno`, `from`, `to` FROM bus "
						."WHERE busid = :id", array(':id' => $busid));
		$return = false;

		if($res && ($row = $res->fetch()) != false)
		{
			$return['routeno']	= $row[0];
			$return['from']		= $row[1];
			$return['to']		= $row[2];
		}

		return $return;	
	}
	
	public function getplacename($pid) {
	
		$res	= $this->dbh->query("SELECT name FROM place WHERE pid = :id", 
						array(':id' => $pid));
		$return = false;

		if($res && ($row = $res->fetch()) != false)
		{
			$return = $row[0];
		}

		return $return;
	}
}

?>

