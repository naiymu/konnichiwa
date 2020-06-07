<?php
require_once("scripts/login.php");
require_once("scripts/error.php");
require_once("scripts/start.php");
require_once("scripts/directory.php");

function showCards($result, $connection) {
	// For checking if dir exists on not
	require("scripts/directory.php");
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
}

startPage("tags", "add_to_fav");

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
$tagId = -1;
$tag = null;
$listBullet = "fa-angle-right";

if(isset($_POST['tags']) && !empty($_POST['tags'])) {
	$tags = $_POST['tags'];
	$tagNum = count($tags);
	// Join the tags with '&' for the heading
	$tagNames = implode(" & ", $tags);
	$tagNames = ucwords($tagNames);
	echo "<div class='frame tag-head-frame'>";
	echo "<div class='container tag-head-container'>";
	echo "<p class='tag-title'>$tagNames</p>";
	echo "</div>";
	echo "</div>";
	echo "<div class='row-flex'>";
	// Get the right number of placeholders
	$placeholders = implode(", ", array_fill(0, $tagNum, "?"));
	$stmnt = $connection->prepare("
		SELECT DISTINCT d.*
		FROM dir_tag_link ln, directories d, tags t
		WHERE ln.tagId = t.tagId
		AND (t.tag IN ($placeholders))
		AND d.dirId = ln.dirId
		GROUP BY d.dirId
		HAVING COUNT(d.dirId)=(?)
	");
	// Add the count to end of tag array so that it is used for
	// the "i" type later on in bind_param
	$tags = array_merge($tags, array($tagNum));
	// The type string "sssss...si"
	$types = str_repeat("s", $tagNum)."i";
	// Merge the types string and tags ["sss...i", tag1, tag2, tag3, ...]
	$args = array_merge(array($types), $tags);
	// $stmnt->bind_param("sss...i", tag1, tag2, tag3, ...)
	// The three dots is the php splat operator
	// It unpacks the array and sends it as arguments to the function
	$stmnt->bind_param(...$args);
	$stmnt->execute();
	$result = $stmnt->get_result();
	if($result->num_rows > 0) {
		showCards($result, $connection);
	}
	else {
		$error = getErrorString("Nothing found for the given query", 404);
		echo $error;
		$result->close();
		$stmnt->close();
		$connection->close();
		die();
	}
	echo "</div>";
	$result->close();
	$stmnt->close();
	$connection->close();
	exit();
}

if(isset($_GET['tag'])) {
	$tagId = trim($_GET['tag']);
}
else {
	$error = getErrorString("Looks like you are accessing this file directly<br>You might want to browse the list first", 400);
	echo $error;
	echo "</div>";
	$connection->close();
	die();
}

if($tagId === "" || $tagId < 0) {
	$error = getErrorString("No tag id provided", 400);
	echo $error;
	echo "</div>";
	$connection->close();
	die();
}

$stmnt = $connection->prepare("SELECT tag FROM tags WHERE tagId=(?)");
$stmnt->bind_param("i", $tagId);
$stmnt->execute();
$result = $stmnt->get_result();
if($result->num_rows > 0) {
	$row = $result->fetch_assoc();
	$tag = $row['tag'];
	$result->close();
	$stmnt->close();
}
else {
	$error = getErrorString("No such page exists on this site", 404);
	echo $error;
	$connection->close();
	die();
}

$tagName = ucwords($tag);
echo "<a href=''><div class='frame tag-head-frame'>";
echo "<div class='container tag-head-container'>";
echo "<p class='tag-title'>$tagName</p>";
echo "</div>";
echo "</div></a>";


echo "<div class='row-flex'>";
$stmnt = $connection->prepare("
	SELECT DISTINCT *
	FROM directories d
		INNER JOIN dir_tag_link ln ON ln.dirId = d.dirId
		INNER JOIN tags t ON t.tagId = ln.tagId
	WHERE t.tagId=(?)
");
$stmnt->bind_param("i", $tagId);
$stmnt->execute();
$result = $stmnt->get_result();
if($result->num_rows > 0) {
	showCards($result, $connection);
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
