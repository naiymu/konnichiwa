<?php
require_once("scripts/login.php");
require_once("scripts/error.php");
require_once("scripts/start.php");
require_once("scripts/directory.php");

startPage("authors", "add_to_fav");

$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
if($connection->connect_errno) {
	echo "<div class='main'>";
	$error = getErrorString("Could not connect to the database.", $connection->connect_errno);
	echo $error;
	echo "</div>";
	$connection->close();
	die();
}

echo "<div class='main column-flex'>";

$authorId = -1;
$author = null;
$listBullet = "fa-angle-right";

if(isset($_GET['author'])) {
	$authorId = trim($_GET['author']);
}
else {
	$error = getErrorString("Looks like you are accessing this file directly<br>You might want to browse the list first", 400);
	echo $error;
	echo "</div>";
	$connection->close();
	die();
}

if($authorId === "" || $authorId < 0) {
	$error = getErrorString("No directory provided", 400);
	echo $error;
	echo "</div>";
	$connection->close();
	die();
}

$stmnt = $connection->prepare("SELECT author FROM authors WHERE authorId=(?)");
$stmnt->bind_param("i", $authorId);
$stmnt->execute();
$result = $stmnt->get_result();
if($result->num_rows > 0) {
	$row = $result->fetch_assoc();
	$author = $row['author'];
	$result->close();
	$stmnt->close();
}
else {
	$error = getErrorString("No such page exists on this site", 404);
	echo $error;
	$connection->close();
	die();
}

$authorName = ucwords($author);
echo "<a href=''><div class='frame author-head-frame'>";
echo "<div class='container author-head-container'>";
echo "<p class='author-title'>$authorName</p>";
echo "</div>";
echo "</div></a>";


echo "<div class='row-flex'>";
$stmnt = $connection->prepare("
	SELECT DISTINCT *
	FROM directories d
		INNER JOIN dir_author_link ln ON ln.dirId = d.dirId
		INNER JOIN authors t ON t.authorId = ln.authorId
	WHERE t.authorId=(?)
");
$stmnt->bind_param("i", $authorId);
$stmnt->execute();
$result = $stmnt->get_result();
if($result->num_rows > 0) {
	while($row = $result->fetch_assoc()) {
	$dirName = $row['dirName'];
	$dirCover = $row['dirCover'];
	$dirId = $row['dirId'];
	$isFav = false;
	// Check if the dirId is in favourites
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
	$coverPath = $relative_directory . $dirName . "/" . htmlspecialchars($dirCover, ENT_QUOTES);
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
	$result->close();
	$stmnt->close();
}
else {
	$error = getErrorString("No such page exists on this site", 404);
	echo $error;
	$connection->close();
	die();
}

echo "</div>";
echo "</div>";

$connection->close();
