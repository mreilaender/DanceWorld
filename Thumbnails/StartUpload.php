<?php
include 'Thumbnail.php';
error_reporting ( E_ALL );
$target_dir = "Bilder/Fotos/"; // Picture directory
$thum_dir = "Bilder/Thumbnails/"; // Thumbnail directory
$temp_file = "Bilder/tmp/";//Temporary directory, where files will be uploaded in full size (e.g. 4k), but will be cleared after finishing convertation
$nextNumber = false; // Jakub Kopec file number system, foto 1 (1.png) has a thumbnail with the exact same name (1.png) directories listed above
$supported_extensions = array (
		1 => "jpeg",
		2 => "jpg",
		3 => "png",
		4 => "gif" 
);
echo "Trennzeichen: " . PATH_SEPARATOR . "<br/>";
checkNextNumber ();
if (isset ( $_POST ['upload'] )) {
	$tmp = "LOG:<br/>";
	if ($tmp = checkFile() == true) {
		if($tmp = uploadFile() == true)
			echo "Success!";
		else
			echo "uploadFile() failed!";
	} else {
		echo "checkFile() failed! LOG:<br/>" . $tmp;
	}
}
function checkNextNumber() {
	$dir = opendir ( $GLOBALS ["target_dir"] );
	while ( $datei = readdir ( $dir ) ) {
		$tmp = explode ( '.', $datei );
		if ($GLOBALS ['nextNumber'] < $tmp [0])
			$GLOBALS ['nextNumber'] = $tmp [0];
	}
	if($GLOBALS['nextNumber'] == false)
		$GLOBALS['nextNumber'] = 1;
	else
		++ $GLOBALS ['nextNumber'];
}
function checkFile() {
	$func_info = "";
	if ($GLOBALS ['nextNumber'] == false) {
		$func_info .= "nextNumber hasn't been set yet<br/>\n";
		return $func_info;
	}
	
	$GLOBALS ["target_dir"] .= $GLOBALS ['nextNumber'] . ".jpg"; // Adding Filenamne to path = /path/to/file.asd
	$GLOBALS ["thum_dir"] .= $GLOBALS ['nextNumber'] . ".jpg"; // Adding Filenamne to path = /path/to/file.asd
	$target_ext = strtolower(pathinfo ( $GLOBALS ["target_dir"], PATHINFO_EXTENSION ));
	
	// Check if supported file extension, listed above as a global
	$supported = false;
	$func_info .= "Checking if file extension is supported<br/>\n";
	foreach ( $GLOBALS ["supported_extensions"] as $key => $value ) {
		if ($target_ext == $value) {
			$func_info .= "Supported file extension<br/>\n";
			$supported = true;
		}
	}
	if (! $supported) {
		$func_info .= "Unsupported File extension<br/>\n";
		echo "Unsupported File Extension, file extension: " . $target_ext;
		return $func_info;
	}
	return true;
}
/**
 * checkFile has to be called first or $GLOBALS['target_dir'] has to be set to a path like that: path/to/file.jpg (JPG, JPEG, GIF or PNG)
 * @return boolean|string
 */
function uploadFile() {
	$func_info = "";
	
	// Uploading file
	$func_info .= "Uploading file<br/>\n";
	if (move_uploaded_file ( $_FILES ["file_upload"] ["tmp_name"], __DIR__ . "/" . $GLOBALS['temp_file'] . basename($_FILES['file_upload']['name']) )) {
		$func_info .= "File has been successfully uploaded<br/>\n";
	} else {
		$func_info .= "Some error occurred<br/>\n";
		return false;
	}
	
	//Converting to FullHD
	$func_info .= "Converting to FullHD";
	$tmp = resizePropotional($GLOBALS['temp_file']. basename($_FILES['file_upload']['name']), "1920", "1080", $GLOBALS['target_dir']);
	if($tmp) {
		$func_info .= "Convertation to FullHD successful<br/>\n";
	} else {
		$func_info .= "Convertation to FullHD failed, function (resizePropotional) log:<br/>\n------------------ LOG ----------------------<br/>\n" . $tmp . "<br/>\n------------------ END LOG -------------------";
		return false;
	}
	
	//Creating Thumbnail
	$func_info .= "Making Thumbnail<br/>\n";
	//TODO Adjust thumbnail measures
	$tmp = resizePropotional($GLOBALS['temp_file']. basename($_FILES['file_upload']['name']), "500", "300", $GLOBALS['thum_dir']);
	if($tmp) {
		$func_info .= "Thumbnail successfully created";
	} else {
		$func_info .= "Couldn't create Thumbnail, function log (resizePropotional) log:<br/>\n------------------ LOG ----------------------<br/>\n" . $tmp . "<br/>\n------------------ END LOG -------------------";
		return $func_info;
	}
	return true;
}
?>