<?php
require_once("scripts/directory.php");
require_once("scripts/login.php");
require_once("scripts/error.php");
require_once("scripts/start.php");

$listBullet = "fa-chevron-circle-right";

startPage("tags");

$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
if($connection->connect_errno) {
	echo "<div class='main'>";
	$error = getErrorString("Could not connect to the database.", $connection->connect_errno);
	echo $error;
	echo "</div>";
	exit();
}

$dirId = null;
$dirName = null;
$dirCover = null;
$infoUrl = null;
$attachedTags = [];

if(isset($_GET['dir'])) {
	$dirId = $_GET['dir'];
	$data = array(
		'dir' => $dirId
	);
	$infoUrl = "info.php?" . http_build_query($data);
	$stmnt = $connection->prepare("SELECT * FROM directories WHERE dirId=(?)");
	$stmnt->bind_param("i", $dirId);
	$stmnt->execute();
	$result = $stmnt->get_result();
	if($result->num_rows > 0) {
		$row = $result->fetch_assoc();
		$dirName = $row['dirName'];
		$dirCover = $row['dirCover'];
	}
	else {
		echo "<div class='main'>";
		$error = getErrorString("The directory id does not exist", 404);
		echo $error;
		echo "</div>";
		$result->close();
		$stmnt->close();
		$connection->close();
		die();
	}
	$result->close();
	$stmnt->close();
	if(isset($_POST['dirName']) && isset($_POST['dirCover'])) {
		$newDirName = trim($_POST['dirName']);
		$newDirCover = trim($_POST['dirCover']);
		$dirNameMsg = "Could not update directory name";
		$dirCoverMsg = "Could not update directory cover";
		if($newDirName !== $dirName) {
			$stmnt = $connection->prepare("UPDATE directories SET dirName=(?) WHERE dirId=(?)");
			$stmnt->bind_param("si", $newDirName, $dirId);
			if(!$stmnt->execute()) {
				$errno = $connection->errno;
				$dirNameMsg .= " $errno";
			}
			$stmnt->close();
		}
		if($newDirCover !== $dirCover) {
			$stmnt = $connection->prepare("UPDATE directories SET dirCover=(?) WHERE dirId=(?)");
			$stmnt->bind_param("si", $newDirCover, $dirId);
			if(!$stmnt->execute()) {
				$errno = $connection->errno;
				$dirNameMsg .= " $errno";
			}
			$stmnt->close();
		}
		$htmlDirName = htmlspecialchars($dirName);
		$htmlNewDirName = htmlspecialchars($newDirName);
		$htmlNewDirCover = htmlspecialchars($newDirCover);
		echo "
		<div class='main'>
		<div class='frame'>
		<div class='container'>
		<div class='frame-head'>
		<a href='$infoUrl'>$htmlDirName</a>
		<div class='underline active black'></div>
		</div>
		<img class='wide-image' src='/assets/bam.gif'>
		<div class='sole-message last'>
		<p>Bam! Updated \"$htmlDirName\"</p>
		</div>
		<div class='column-flex'>
			<div class='edit-dir-details last'>
			<p class='label wide inverse'>New name</p>
			<p class='edit-dir-detail'>$htmlNewDirName</p>
			</div>
			<div class='edit-dir-details'>
			<p class='label wide inverse'>New cover</p>
			<p class='edit-dir-detail'>$htmlNewDirCover</p>
			</div>
		</div>
		</div>
		</div>
		</div>
		";
		$connection->close();
		exit();
	}
}
else {
	echo "<div class='main'>";
	$error = getErrorString("Looks like you are accessing this file directly<br>You might want to browse the list first", 400);
	echo $error;
	echo "</div>";
	$connection->close();
	exit();
}

$dirName = htmlspecialchars($dirName, ENT_QUOTES);
$dirCover = htmlspecialchars($dirCover, ENT_QUOTES);
echo "<div class='main'>";
echo "<div class='frame' style='text-align: left;'>";
echo "<div class='container'>";
echo <<<EOF
<div class='frame-head'>
	<a href='$infoUrl'>$dirName</a>
	<div class='underline active black'></div>
</div>
<div class='edittags-head last'>
	<i class='fa fa-edit'></i>
	<p>Edit directory</p>
</div>
<form method='POST'>
	<div class='column-flex last'>
	<input type='text' class='textbox' name='dirName' placeholder='Directory Name' value='$dirName'></input>
	<input type='text' class='textbox' name='dirCover' placeholder='Directory Cover' value='$dirCover'></input>
	</div>
	<div class='row-flex'>
	<button type='submit'>Submit</button>
	<button type='reset'>Reset</button>
	</div>
</form>
EOF;

echo "</div>";
echo "</div>";

$connection->close();
