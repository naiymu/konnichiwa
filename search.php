<?php
require_once("scripts/directory.php");
require_once("scripts/login.php");
require_once("scripts/start.php");
require_once("scripts/error.php");

// Start the page with the home tag so that the navigation
// bar has something active (the home button)
startPage("home", "add_to_fav");

echo "<div class='main'>";

$search = "<<>>";
if(isset($_GET['q'])) {
	$q = trim($_GET['q']);
	$search = $q;
}
else {
	$error = getErrorString("No search query provided", 400);
	echo $error;
	die();
}
if($q === "") {
	$error = getErrorString("No search query provided", 400);
	echo $error;
	die();
}

$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
if($connection->connect_errno) {
	$error = getErrorString("Could not connect to the database.", $connection->connect_errno);
	echo $error;
	$connection->close();
	die();
}

$stmnt = $connection->prepare("SELECT * FROM directories WHERE dirName LIKE CONCAT('%', ?, '%')");
$stmnt->bind_param("s", $search);
$stmnt->execute();
$results = $stmnt->get_result();

echo "<div class='row-flex'>";
if(!$results) {
	echo "<div class='frame' style='width: 500px;'>";
	echo "<div class='container'>";
	$error = getErrorString("Query unsuccessful", $connection->errno);
	$stmnt->close();
	$connection->close();
	echo "</div>";
	echo "</div>";
	die($error);
}

$rows = $results->num_rows;

if($rows > 0) {
	while($row = $results->fetch_assoc()) {
		$dirName = $row['dirName'];
		$dirCover = $row['dirCover'];
		$dirId = $row['dirId'];
		// Check if the dirId is in favourites
		$isFav = false;
		$favStmnt = $connection->prepare("SELECT * FROM favourites WHERE dirId=(?)");
		$favStmnt->bind_param("i", $dirId);
		$favStmnt->execute();
		$favs = $favStmnt->get_result();
		if($favs->num_rows > 0) {
			$isFav = true;
		}
		$favs->close();
		$favStmnt->close();
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
		$coverPath = $relative_directory . $dirName . "/" . $dirCover;
		if(!$dir_exists) {
			$coverPath = "";
		}
		$urlData = array(
			'dir' => $dirId,
		);
		$url = "/info.php?" . http_build_query($urlData);
		$dirName = htmlspecialchars($dirName, ENT_QUOTES);
		$div = <<<EOF
		<div class="img-card">
		<a id='$dirId-fav' class='favourite no'><i class='far fa-heart'></i><i class='fas fa-heart'></i></a>
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
		if($isFav) {
			$div = str_replace("favourite no", "favourite yes", $div);
		}
		if($isBmk) {
			$div = str_replace("bookmark bmk-no", "bookmark bmk-yes", $div);
		}
		echo $div;
	}
}
else {
	echo "<div class='frame' style='width: 500px;'>";
	echo "<div class='container'>";
	$error = getErrorString("No results for '$search'", 0);
	echo $error;
	echo "</div>";
	echo "</div>";
}

$stmnt->close();
$connection->close();

echo "</div>";
// Closing main div
echo "</div>";
