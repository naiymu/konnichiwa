<?php
require_once("../add.php");

$fileName = "data.json";

if(count($argv) > 1) {
	$fileName = $argv[1];
}

if(!file_exists($fileName)) {
	echo "File '$fileName' not found\n";
	die();
}

$json = file_get_contents($fileName);
$data = json_decode($json);
// The directories in json file
$directories =  $data->directories;

foreach ($directories as $dirObj) {
	$dirName = $dirObj->dirName;
	$dirCover = $dirObj->dirCover;
	$authors = $dirObj->authors;
	$tags = $dirObj->tags;
	$result = addToDB($dirName, $dirCover, $authors, $tags);
	// Check if adding to database was successful or not
	// Removing the true will not work because the addToDB method
	// in add.php returns error message if it fails
	if($result === true) {
		echo "Added: $dirName\n";
	}
	else {
		$error = $result['error'];
		echo "ERROR: $dirName => $error\n";
	}
}
