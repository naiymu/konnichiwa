<?php
require_once("directory.php");
require_once("login.php");

$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);

$dirId = null;
$bookmark = null;
$defaultBmk = null;
$alreadyExists = false;
if(isset($_POST['dirId']) && isset($_POST['bookmark'])) {
	$dirId = trim($_POST['dirId']);
	$bookmark = trim($_POST['bookmark']);
}
else {
	$error = "No id or bookmark given";
	echo $error;
	throw new Exception($error);
}
if($dirId === "" || $bookmark === "") {
	$error = "Empty bookmark request";
	echo $error;
	throw new Exception($error);
}

$data = array(
	'dir' => $dirId,
	'page' => 1
);
$defaultBmk = "read.php?" . http_build_query($data);

if(strpos($bookmark, 'read.php') !== 0) {
	$bookmark = $defaultBmk;
}

$stmnt = $connection->prepare("SELECT * FROM bookmarks WHERE dirId=(?)");
$stmnt->bind_param("i", $dirId);
$stmnt->execute();
$result = $stmnt->get_result();
if($result->num_rows > 0) {
	$alreadyExists = true;
	$row = $result->fetch_assoc();
	$storedBmark = $row['bookmark'];
	// [*] If the stored bookmark matches the given bookmark:
	// The script is being called from the same page as the stored bookmark so
	// we need to remove it as well
	// -------------------------------------------------------------------------
	// [*] If the stored bookmark matches the given bookmark and the given
	// bookmark is the same as the default bookmark:
	// The script is being called either from the first page of the directory,
	// in which case it is the current bookmark, or from a page other than
	// read.php so we need to remove the bookmark
	// -------------------------------------------------------------------------
	// Note that if we do not check whether the current bookmark matches the
	// default bookmark or not in the second case, it will always be true if
	// the stored url is the first page and then even if the script is called
	// from a new page, the program wouldn't know and it will remove the
	// bookmark instead of updating it
	// -------------------------------------------------------------------------
	// [*] If the given bookmark matches the default bookmark:
	// This condition (obviously due to the OR operators) will only be checked
	// if the previous two conditions failed
	// So if the given bookmark is the default bookmark at this point when the
	// stored bookmark is neither the given bookmark nor the default bookmark
	// but there is a bookmark set so we definitely know that the script caller
	// is a page other than read.php so we remove the bookmark
	if($storedBmark === $bookmark || ($storedBmark===$defaultBmk && $bookmark===$defaultBmk) || $bookmark===$defaultBmk) {
		$remStmnt = $connection->prepare("DELETE FROM bookmarks WHERE dirId=(?)");
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
		echo "Removed bookmark";
		return true;
	}
}
$stmnt->close();

$stmnt = $connection->prepare("INSERT INTO bookmarks (dirId, bookmark) VALUES (?, ?) ON DUPLICATE KEY UPDATE bookmark=(?)");
$stmnt->bind_param("iss", $dirId, $bookmark, $bookmark);
$stmnt->execute();
if($stmnt->error) {
	$connError = $connection->error;
	$stmnt->close();
	$connection->close();
	$error = "Could not execute insert query";
	echo $error;
	throw new Exception($error . " : " . $connError);
}
echo $stmnt->error;
$stmnt->close();
$connection->close();
if($alreadyExists) {
	echo "Updated bookmark";
}
else {
	echo "Added bookmark";
}
