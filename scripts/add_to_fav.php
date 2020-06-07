<?php
require_once("directory.php");
require_once("login.php");

$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);

$dirId = null;
if(isset($_POST['dirId'])) {
	$dirId = trim($_POST['dirId']);
}
else {
	$error = "No directory provided";
	echo $error;
	throw new Exception($error);
}
if($dirId === "") {
	$error = "Empty directory request";
	echo $error;
	throw new Exception($error);
}

$stmnt = $connection->prepare("SELECT * FROM favourites WHERE dirId=(?)");
$stmnt->bind_param("i", $dirId);
$stmnt->execute();
$result = $stmnt->get_result();
if($result->num_rows > 0) {
	$remStmnt = $connection->prepare("DELETE FROM favourites WHERE dirId=(?)");
	$remStmnt->bind_param("i", $dirId);
	if(!$remStmnt->execute()) {
		$connError = $connection->error;
		$remStmnt->close();
		$stmnt->close();
		$connection->close();
		$error = "Could not execute delete query";
		echo $error;
		throw new Exception($error . " : " . $connError);
	}
	$remStmnt->close();
	$stmnt->close();
	$connection->close();
	echo "Removed from favourites";
	return true;
}
$stmnt->close();

$stmnt = $connection->prepare("INSERT INTO favourites (dirId) VALUES (?)");
$stmnt->bind_param("i", $dirId);
$stmnt->execute();
if($stmnt->error) {
	$connError = $connection->error;
	$stmnt->close();
	$connection->close();
	$error = "Could not execute insert query";
	echo $error;
	throw new Exception($error . " : " . $connError);
}
$stmnt->close();
$connection->close();
echo "Added to favourites";
