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
FROM bus_place ORDER BY dist LIMIT 1
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
SELECT pid, name FROM bus_place WHERE name LIKE :name LIMIT 1
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
	
		$resarray['from']		= $fromnode['name'];
		$resarray['to']			= $tonode['name'];
		$resarray['permalink']	= $fromnode['pid'] . "_" . $tonode['pid'];
	
		foreach($this->getlinks($fromnode['pid'], $tonode['pid']) as $b) {
	
			$link = array();
		
			$link['nobuses'] 	= (int)$b['type'];
			$link['totaldist'] 	= $b['dist1'] + $b['dist2'] + $b['dist3'];
		
			if($b['busid1'] != 0) {
		
				/* bus 1, source deets */
				$bus = $this->getbusdetails($b['busid1']);
				
				$goff = ($b['busid2'] == 0) ? $tonode['name'] : 
							$this->getplacename($b['changeid1']);
				
				$link['inst'][] = array(
					'route'		=> $bus['routeno'],
					'busfrom'	=> $bus['from'],
					'busto'		=> $bus['to'],
					'geton'		=> $fromnode['name'],
					'getoff'	=> $goff,
					'distance'	=> ($b['dist1'] / 1000));
					
			
				if($b['busid2'] != 0) {
			
					$bus = $this->getbusdetails($b['busid2']);
				
					$goff = ($b['busid3'] == 0) ? $tonode['name'] : 
								$this->getplacename($b['changeid2']);
				
					$link['inst'][] = array(
						'route'		=> $bus['routeno'],
						'busfrom'	=> $bus['from'],
						'busto'		=> $bus['to'],
						'geton'		=> $fromnode['name'],
						'getoff'	=> $goff,
						'distance'	=> ($b['dist2'] / 1000));
						
					if($b['busid3'] != 0) {
					
						$bus = $this->getbusdetails($b['busid3']);
				
						$link['inst'][] = array(
							'route'		=> $bus['routeno'],
							'busfrom'	=> $bus['from'],
							'busto'		=> $bus['to'],
							'geton'		=> $fromnode['name'],
							'getoff'	=> $tonode['name'],
							'distance'	=> ($b['dist3'] / 1000));
							
					}
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
FROM `bus_stop` AS s1 INNER JOIN `bus_stop` AS s2
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
FROM `bus_changeover` AS ch1, `bus_bus` AS b1, `bus_bus` AS b2,
`bus_stop` AS s1 INNER JOIN `bus_stop` AS s2
ON s1.`bid` = s2.`bid`
INNER JOIN `bus_stop` AS s3
ON s2.`pid` = s3.`pid`
INNER JOIN `bus_stop` AS s4
ON s3.`bid` = s4.`bid`  
WHERE s1.`pid` = :from AND s2.`pid` = ch1.`changeid` AND s4.`pid` = :to 
AND s2.`distance` > s1.`distance` AND s4.`distance` > s3.`distance` 
AND b1.`busid` = s1.`bid` AND b2.`busid` = s3.`bid` AND
b1.similarity <> b2.similarity AND s1.`bid` <> s3.`bid` 
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
SELECT '3' AS type, s1.`bid` AS busid1, s3.`bid` AS busid2, 
s5.`bid` AS busid3, ch1.`changeid` as changeid1, ch2.`changeid` as changeid2, 
(s2.`distance` - s1.`distance`) as dist1, 
(s4.`distance` - s3.`distance`) as dist2, 
(s6.`distance` - s5.`distance`) as dist3 
FROM `bus_changeover` AS ch1, `bus_changeover` AS ch2, 
`bus_bus` AS b1, `bus_bus` AS b2, `bus_bus` AS b3, 
`bus_stop` AS s1 INNER JOIN `bus_stop` AS s2 ON s1.`bid` = s2.`bid`
INNER JOIN `bus_stop` AS s3 ON s2.`pid` = s3.`pid`
INNER JOIN `bus_stop` AS s4 ON s3.`bid` = s4.`bid`
INNER JOIN `bus_stop` AS s5 ON s4.`pid` = s5.`pid`
INNER JOIN `bus_stop` AS s6 ON s5.`bid` = s6.`bid`  
WHERE s1.`pid` = :from AND s2.`pid` = ch1.`changeid` AND s4.`pid` = ch2.`changeid` 
AND s6.`pid` = :to AND s2.`distance` > s1.`distance` AND s4.`distance` > s3.`distance` 
AND s6.`distance` > s5.`distance` AND b1.`busid` = s1.`bid` AND b2.`busid` = s3.`bid` AND 
b3.`busid` = s5.`bid` AND b1.`similarity` <> b2.`similarity`
AND b2.`similarity` <> b3.`similarity` AND b1.`similarity` <> b3.`similarity`
ORDER BY (dist1 + dist2 + dist3) LIMIT 5
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
	
		$res	= $this->dbh->query("SELECT `routeno`, `from`, `to` FROM bus_bus "
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
	
		$res	= $this->dbh->query("SELECT name FROM bus_place WHERE pid = :id", 
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

