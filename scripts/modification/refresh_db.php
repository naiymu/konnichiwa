<?php
require_once("../add.php");

$json = file_get_contents("data.json");
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
		echo "Added: $dirName";
	}
	else {
		$error = $result['error'];
		echo "ERRROR: $dirName => $error";
	}
}
