<?php
require_once("scripts/login.php");
require_once("scripts/error.php");
require_once("scripts/start.php");
require_once("scripts/directory.php");

startPage("favourites", "add_to_fav");

$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
if($connection->connect_errno) {
	echo "<div class='main'>";
	$error = getErrorString("Could not connect to the database.", $connection->connect_errno);
	echo $error;
	echo "</div>";
	$connection->close();
	die();
}

// Get all directories whose dirId are present in the favourites table
$sql = "SELECT * FROM directories WHERE dirId IN (SELECT dirId FROM favourites) ORDER BY dirName ASC";
$result = $connection->query($sql);

echo "<div class='main'>";
echo "<div class='frame favourite-head-frame'>";
echo "<div class='container favourite-head-container'>";
echo "<p class='favourite-title'>Favourites</p>";
echo "</div>";
echo "</div>";
echo "<div class='row-flex'>";
if(!$result) {
	$error = getErrorString("Query unsuccessful", $connection->errno);
	$connection->close();
	die($error);
}

$rows = $result->num_rows;

if($rows > 0) {
	while($row = $result->fetch_assoc()) {
		$dirId = $row['dirId'];
		$dirName = $row['dirName'];
		$dirCover = $row['dirCover'];
		$coverPath = $relative_directory . $dirName . "/" . htmlspecialchars($dirCover, ENT_QUOTES);
		// Check if the dirId is in bookmarks
		$isBmk = false;
		$bmkStmnt = $connection->prepare("SELECT * FROM bookmarks WHERE dirId=(?)");
		$bmkStmnt->bind_param("i", $dirId);
		$bmkStmnt->execute();
		$bmks = $bmkStmnt->get_result();
		if($bmks->num_rows > 0) {
			$isBmk = true;
		}
		$bmks->close();
		$bmkStmnt->close();
		if(!$dir_exists) {
			$coverPath = "";
		}
		$data = array(
			'dir' => $dirId
		);
		$url = "info.php?" . http_build_query($data);
		$dirName = htmlspecialchars($dirName, ENT_QUOTES);
		$div = <<<EOF
		<div class="img-card">
		<a id='$dirId-fav' class='favourite yes'><i class='far fa-heart'></i><i class='fas fa-heart'></i></a>
		<a id='$dirId-bmk' class='bookmark bmk-no'><i class='far fa-bookmark'></i><i class='fas fa-bookmark'></i></a>
		<a href='$url'>
			<div class="img-container">
				<img src="$coverPath">
			</div>
			<div class="img-data">
				<p class="img-data-text">$dirName</p>
			</div>
		</a>
		</div>
EOF;
		if($isBmk) {
			$div = str_replace("bookmark bmk-no", "bookmark bmk-yes", $div);
		}
		echo $div;
	}
}
else {
	echo "<div class='frame' style='width: 500px;'>";
	echo "<div class='container'>";
	$error = getErrorString("No directory starting with '$letter'", 0);
	echo $error;
	echo "</div>";
	echo "</div>";
}
// Close the row-flex div
echo "</div>";
// Close the main div
echo "</div>";

$result->close();
$connection->close();
