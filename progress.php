<?php
session_start();

if($_POST['prog'] || $_POST['prog'] == "") {
	$key = ini_get("session.upload_progress.prefix") . $_POST['prog'];
	if(!empty($_SESSION[$key])) {
		$current = $_SESSION[$key]["bytes_processed"];
		$total = $_SESSION[$key]["content_length"];
		if($current < $total) {
			$retn = ceil(($current/$total) * 100);
		} else {
			$retn = 100;
		}
		$retn .= "%";
		$ret = "{\"progress\":\"$retn\"}";
		echo $ret;
	} else {
		echo "{\"progress\":\"Unknown\"}";
	}			
} else {
	echo "{\"error\":\"No prog value sent.\"}";
}

?>