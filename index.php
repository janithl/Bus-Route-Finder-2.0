<?php

if(isset($_GET['from']) && isset($_GET['to'])) {

	header('Content-type: application/json');

	include("./inc/config.inc.php");
	include("./inc/dbconn.class.php");
	include("./inc/bus.class.php");

	/*
		index.php - for routing
	*/

	$b		= new BusAPI();

	if(preg_match('/[0-9.,]+/', urldecode($_GET['from']))) {

		$sour	= explode(',', urldecode($_GET['from']));
		$fnode	= $b->getclosestnode($sour[0], $sour[1]);
	}
	else {

		$fnode	= $b->getnodebyname(urldecode($_GET['from']));
	}

	if(preg_match('/[0-9.,]+/', urldecode($_GET['to']))) {
	
		$dest	= explode(',', urldecode($_GET['to']));
		$tnode	= $b->getclosestnode($dest[0], $dest[1]);
	}
	else {

		$tnode	= $b->getnodebyname(urldecode($_GET['to']));
	}
		
	$cachefile	= "./cache/{$fnode['pid']}_{$tnode['pid']}.html";

	if(file_exists($cachefile)) {

		include($cachefile);
	}
	else {

		/* start output buffer */
		ob_start();
	
		/* deets */
		echo $b->getbus($fnode, $tnode);
		
		/* save contents of buffer into cache file */
		$fp = fopen($cachefile, 'w');
	
		if($fp) {
			fwrite($fp, ob_get_contents());
			fclose($fp);
		}

		/* send buffer contents to browser */
		ob_end_flush();
	}
}
else if(isset($_GET['id'])) {

	if(isset($_GET['client'])) {
	
		$scr = '<script type="text/javascript">populatebyid("'.$_GET['id'].'");</script></head>';
	
		$fh = fopen("./client.html", 'r');
		while (($data = fgets($fh, 2048)) !== false) {
		
			echo str_replace('</head>', $scr, $data);
		}
	
	}
	else {
	
		$id = explode('_', $_GET['id']);
	
		$cachefile	= "./cache/" . (int)$id[0] . "_" . (int)$id[1] . ".html";

		if(file_exists($cachefile)) {
	
			header('Content-type: application/json');
			include($cachefile);
		}
	}
}
else {

	include('./client.html');
}

?>
