<?php
require_once("scripts/login.php");
require_once("scripts/error.php");
require_once("scripts/start.php");
require_once("scripts/directory.php");

startPage("home", "add_to_fav");

$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
if($connection->connect_errno) {
	echo "<div class='main'>";
	$error = getErrorString("Could not connect to the database.", $connection->connect_errno);
	echo $error;
	echo "</div>";
	$connection->close();
	die();
}

echo "<div class='main'>";

$dirId = -1;
$dirName = null;
$dirCover = null;
$bmkUrl = null;
$authors = [];
$tags = array();
$containsSubdir = false;
$listBullet = "fa-angle-right";

if(isset($_GET['dir'])) {
	$dirId = trim($_GET['dir']);
}
else {
	$error = getErrorString("Looks like you are accessing this file directly<br>You might want to browse the list first", 400);
	echo $error;
	echo "</div>";
	$connection->close();
	die();
}

if($dirId === "" || $dirId < 0) {
	$error = getErrorString("No directory provided", 400);
	echo $error;
	echo "</div>";
	$connection->close();
	die();
}

$stmnt = $connection->prepare("SELECT * FROM directories WHERE dirId=(?)");
$stmnt->bind_param("i", $dirId);
$stmnt->execute();
$result = $stmnt->get_result();
if($result->num_rows > 0) {
	$row = $result->fetch_assoc();
	$dirName = $row['dirName'];
	$dirCover = $row['dirCover'];
	$result->close();
	$stmnt->close();
}
else {
	$error = getErrorString("No such page exists on this site", 404);
	echo $error;
	$connection->close();
	die();
}

$getTags = $connection->prepare("
	SELECT *
	FROM tags t
		INNER JOIN dir_tag_link ln ON ln.tagId = t.tagId
		INNER JOIN directories d ON ln.dirId = d.dirId
	WHERE d.dirId=(?)
	ORDER BY t.tag ASC
");
$getTags->bind_param("i", $dirId);
$getTags->execute();
$result = $getTags->get_result();
if($result->num_rows > 0) {
	while($row = $result->fetch_assoc()) {
		$tag = $row['tag'];
		$tagId = $row['tagId'];
		$tags[$tagId] = $tag;
	}
}

$getAuthors = $connection->prepare("
	SELECT *
	FROM authors a
		INNER JOIN dir_author_link ln ON ln.authorId = a.authorId
		INNER JOIN directories d ON ln.dirId = d.dirId
	WHERE d.dirId=(?)
	ORDER BY a.author ASC
");
$getAuthors->bind_param("i", $dirId);
$getAuthors->execute();
$result = $getAuthors->get_result();
if($result->num_rows > 0) {
	while($row = $result->fetch_assoc()) {
		$author = $row['author'];
		$authorId = $row['authorId'];
		$authors[$authorId] = $author;
	}
}

echo "<div class='frame'>";
echo "<div class='container'>";

$result->close();
$getAuthors->close();

$dirPath = $image_directory . $dirName;
if(is_dir($dirPath)) {
	$files = scandir($dirPath);
	// Remove all hidden files and the . and .. directories
	$files = array_filter($files, function($a){return ($a[0]!=".");});
	foreach ($files as $file) {
		$filePath = $dirPath . "/" . $file;
		if(is_dir($filePath)) {
			$containsSubdir = true;
		}
	}
}

$imgUrl = $relative_directory . $dirName . "/" . htmlspecialchars($dirCover, ENT_QUOTES);
$data = array(
	'dir' => $dirId,
	'page' => 1
);
$url = "read.php?" . http_build_query($data);

$dirName = htmlspecialchars($dirName, ENT_QUOTES);
echo "<div class='info-container'>";
echo "<div class='row-flex'>";
echo "<a class='doujin-title' href='$url'>$dirName</a>";
$data = array(
	'dir' => $dirId
);
$editDirUrl = "editdir.php?" . http_build_query($data);
echo "<a href='$editDirUrl' class='doujin-tag edit-btn' title='Edit directory info'><i class='fa fa-edit'></i></a>";
echo "</div>";

$heart = "<a id='$dirId-fav' class='favourite no info-heart'><i class='far fa-heart'></i><i class='fas fa-heart'></i></a>";
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
if($isFav) {
	$heart = str_replace("favourite no", "favourite yes", $heart);
}
echo $heart;

$bookmark = "<a id='$dirId-bmk' class='bookmark bmk-no info-bmk'><i class='far fa-bookmark'></i><i class='fas fa-bookmark'></i></a>";
$isBmk = false;
// Check if the dirId is in bookmarks
$bmkStmnt = $connection->prepare("SELECT * FROM bookmarks WHERE dirId=(?)");
$bmkStmnt->bind_param("i", $dirId);
$bmkStmnt->execute();
$bmks = $bmkStmnt->get_result();
if($bmks->num_rows > 0) {
	$row = $bmks->fetch_assoc();
	$bmkUrl = $row['bookmark'];
	$isBmk = true;
}
$bmks->close();
$bmkStmnt->close();
if($isBmk) {
	$bookmark = str_replace("bookmark bmk-no", "bookmark bmk-yes", $bookmark);
}
echo $bookmark;

$first = true;
echo "<div class='info-artists'>";
foreach ($authors as $authorId => $author) {
	$data = array(
		'author' => $authorId
	);
	$authorUrl = "byauthor.php?" . http_build_query($data);
	echo "<a href='$authorUrl' class='info-artist'>$author</a>";
}
$data = array(
	'dir' => $dirId
);
$editAuthorsUrl = "editauthors.php?" . http_build_query($data);
echo "<a href='$editAuthorsUrl' class='doujin-tag edit-btn' title='Edit authors'><i class='fa fa-edit'></i></a>";
echo "</div>";

echo "<div class='doujin-tags'>";
foreach ($tags as $tagId => $tag) {
	$data = array(
		'tag' => $tagId
	);
	$tagUrl = "bytag.php?" . http_build_query($data);
	echo "<a href='$tagUrl' class='doujin-tag'>" . ucwords($tag) . "</a>";
}
$data = array(
	'dir' => $dirId
);
$editTagsUrl = "edittags.php?" . http_build_query($data);
echo "<a href='$editTagsUrl' class='doujin-tag edit-btn' title='Edit tags'><i class='fa fa-edit'></i></a>";
echo "</div>";

if($dir_exists) {
	echo "<a href='$url'><img class='preview' src='$imgUrl'>";
}
echo "<div class='info-read-button'>";
echo "<a href='$url'><button><i class='fa fa-book-open'></i>Read</button></a>";
if($isBmk) {
	echo "<a href='$bmkUrl'><button><i class='fa fa-bookmark'></i>Continue</button></a>";
}
if($containsSubdir) {
	$data = array(
		'dir' => $dirId,
		'subdir' => true
	);
	$subdirUrl = "read.php?" . http_build_query($data);
	echo "<a href='$subdirUrl'><button><i class='fa fa-folder'></i>Subdirectory</button></a>";
}
echo "</div>";

echo "</div>";
echo "</div>";
echo "</div>";
$connection->close();
