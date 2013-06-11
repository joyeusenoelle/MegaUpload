<?php
session_start();
$pathparts = pathinfo(__FILE__,PATHINFO_DIRNAME);
$pathbits = explode("/",$pathparts);
$i = 1;
$pathchunk = "";
while($pathbits[$i] != "wp-content") {
	$pathchunk .= "/" . $pathbits[$i];
	$i++;
}
define('ABSPATH',$pathchunk.'/');
define('DEFDIR',"wp-content/uploads/");

function assembleFN($initial,$extension) {
	if($extension != "") {
		$fullname = $initial . "." . $extension;
	} else {
		$fullname = $initial;
	}
	return $fullname;
}

if($_POST) {// && isset($_POST['check']) && isset($_POST['checkval'])) {
/*	$check = "check/" . $_POST['check'];
	if(file_exists($check)) {
		$checkval = file_get_contents($check);
		if($_POST['checkval'] != $checkval) {
			unlink($check);
			die("{\"error\":\"Check file's contents are not correct.\"}");
		}
	} else {
		die("{\"error\":\"Check file doesn't exist.\"}");
	}
	unset($check, $checkval); */
	if(!$_FILES) {
		die("{\"error\":\"No file submitted.\"}");
	} else {
		if($_FILES["file"]["error"][0] > 0) {
			$errtmp = print_r($_FILES["file"]["error"][0],true);
			die("{\"error\":\"File error: " . $errtmp . "\"}");
		} else {

			// THIS IS WHERE THE ACTUAL WORK GETS DONE
			if($_POST["filename"] && $_POST["filename"] != "") {
				$filebits = explode(".",$_POST["filename"]);
			} else {
				$filebits = explode(".",$_FILES["file"]["name"][0]);
			}
			if($_POST["directory"] && $_POST["directory"] != "") {
				$dir = ABSPATH . $_POST["directory"];
				if(substr($dir,-1) != "/") {
					$dir .= "/";
				}
			} else {
				$dir = ABSPATH . DEFDIR;
			}
			if($filebits[1]) {
				$extension = end($filebits);
				$filename = "";
				$j = sizeof($filebits) - 1;
				for($i=0;$i<$j;$i++) {
					$filename .= $filebits[$i];
				}
			} else {
				$extension = "";
				$filename = $filebits[0];
			}
			$fullname = assembleFN($filename,$extension);
			$j = 0;
			$dirfullname = $dir . $fullname;
			while(file_exists($dirfullname)) {
				$j++;
				$fntemp = $filename . "_" . $j;
				$fullname = assembleFN($fntemp,$extension);
				$dirfullname = $dir . $fullname;
			}
			move_uploaded_file($_FILES["file"]["tmp_name"][0],$dirfullname); 
			$returnval = "{\"success\":\"File uploaded to $dirfullname .\"}";
			echo $returnval;
			
			// END ACTUAL WORK

		}
	}
		
} else {
	// No data from $_POST or no check file or no check value
	die("{\"error\":\"You're not supposed to be here.\"}");
}

?>