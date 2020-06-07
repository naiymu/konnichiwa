<?php
require_once("scripts/error.php");
require_once("scripts/directory.php");
require_once("scripts/start.php");
require_once("scripts/login.php");

// Start the page with the home tag so that the navigation
// bar has something active (the home button)
startPage("home", "read", "add_to_fav");

$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
if($connection->connect_errno) {
	echo "<div class='main'>";
	$error = getErrorString("Could not connect to the database.", $connection->connect_errno);
	echo $error;
	echo "</div>";
	$connection->close();
	die();
}

function getUrl($dir, $page, $readingSubdir) {
	if($readingSubdir) {
		$data = array(
			'dir' => $dir,
			'page' => $page,
			'subdir' => true
		);
	}
	else {
		$data = array(
			'dir' => $dir,
			'page' => $page
		);
	}
	$url = "read.php?" . http_build_query($data);
	return $url;
}

echo "<div class='main column-flex'>";

$dirId = -1;
$dir = null;
$isBmk = false;
$bmkUrl = null;
$currentUrl = null;
$currentPage = 1;
$page = 0;
if(isset($_GET['dir'])) {
	$dirId = trim($_GET['dir']);
}
else {
	$error = getErrorString("Looks like you are accessing this file directly<br>You might want to browse the list first", 400);
	echo $error;
	$connection->close();
	die();
}
if(isset($_GET['page'])) {
	$currentPage = trim($_GET['page']);
	$page = $currentPage - 1;
}

if($dirId === "" || $dirId < 0) {
	$error = getErrorString("No such page exists on this site", 404);
	echo $error;
	$connection->close();
	die();
}

$stmnt = $connection->prepare("SELECT dirName FROM directories WHERE dirId=(?)");
$stmnt->bind_param("i", $dirId);
$stmnt->execute();
$result = $stmnt->get_result();
if($result->num_rows > 0) {
	$row = $result->fetch_assoc();
	$dir = $row['dirName'];
	$result->close();
	$stmnt->close();
}
else {
	$error = getErrorString("No such page exists on this site", 404);
	echo $error;
	$connection->close();
	die();
}

$dirPath = $image_directory . $dir;
$subdir = null;
$readingSubdir = false;

$files = scandir($dirPath);
// Remove all hidden files (files that start with a '.')
$files = array_filter($files, create_function('$a','return ($a[0]!=".");'));
natsort($files);
foreach ($files as $key => $file) {
	if(is_dir($dirPath . "/" . $file)){
		$subdir = $file;
		unset($files[$key]);
	}
}

// Check if subdir parameter is set and subdir is true
if(isset($_GET['subdir']) && $_GET['subdir']) {
	if($subdir !== null) {
		$readingSubdir = true;
		$dir = $dir . "/" . $subdir;
		$dirPath = $dirPath . "/" . $subdir;
		$files = scandir($dirPath);
		// Remove all hidden files (files that start with a '.')
		$files = array_filter($files, create_function('$a','return ($a[0]!=".");'));
		natsort($files);
	}
	else {
		$error = getErrorString("No subdirectory in the requested directory", "400");
		die($error);
	}
}

// Get a numeric indexed array
$files = array_values($files);
$numFiles = count($files);

// The prev/next buttons div
if(!is_dir($dirPath) || $currentPage<=0 || $currentPage>$numFiles) {
	$error = getErrorString("No such page exists on this site", 404);
	echo $error;
	die();
}
echo "<div class='navigator row-flex'>";
echo "<div class='prev-next'>";
if($page>0) {
	$prevPage = $currentPage - 1;
	$url = getUrl($dirId, $prevPage, $readingSubdir);
	echo "<a id='prev' href='$url'><i class='fa fa-arrow-left'></i></a>";
}
echo "</div>";
echo "<div class='read-title'>";
$data = array(
	'dir' => $dirId
);
$url = "info.php?" . http_build_query($data);
echo "<a class='doujin-title' href='$url'>$dir</a>";
echo "</div>";
echo "<div class='prev-next'>";
if($page<$numFiles-1) {
	$nextImgPath = $relative_directory . $dir . "/" . $files[$page+1];
	$nextPage = $currentPage + 1;
	$url = getUrl($dirId, $nextPage, $readingSubdir);
	echo "<a id='next' class='next' href='$url'><i class='fas fa-arrow-right'></i></a>";
}
echo "</div>";
// Close the navigator div as well
echo "</div>";

// Get the image path for html
$imgPath = $relative_directory . $dir . "/" . $files[$page];

$bookmark = "<a id='$dirId-bmk' class='bookmark bmk-no read-bmk info-bmk'><i class='far fa-bookmark'></i><i class='fas fa-bookmark'></i></a>";
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
$currPageIsBmk = false;
if($isBmk) {
	$bmkData = parse_url($bmkUrl);
	$query = null;
	parse_str($bmkData['query'], $query);
	$bmkDir = $query['dir'];
	$bmkPage = $query['page'];
	$bmkSubdir = false;
	if(isset($query['subdir'])) {
		$bmkSubdir = $query['subdir'];
	}
	// Note that the bmkSubdir check only has two equals because subdir can be false or 1
	$currPageIsBmk = $bmkDir===$dirId && $bmkPage===$currentPage && $bmkSubdir==$subdir;
}

// Display the image
$imgPath = htmlspecialchars($imgPath, ENT_QUOTES);
echo "<div class='read'>";
echo "<img class='read-image' src='$imgPath'>";
echo "</div>";


//==============================================================================
if($isBmk && $currPageIsBmk) {
	$bookmark = str_replace("bookmark bmk-no", "bookmark bmk-yes", $bookmark);
}
echo "<div class='read-bmk-div'>$bookmark</div>";
//==============================================================================

// Displaying the navigator (without title & with page count) at the bottom
echo "<div class='navigator row-flex'>";
echo "<div class='prev-next'>";
if($page>0) {
	$prevPage = $currentPage - 1;
	$url = getUrl($dirId, $prevPage, $readingSubdir);
	echo "<a href='$url'><i class='fa fa-arrow-left'></i></a>";
}
echo "</div>";
echo "<div class='read-title'>";
echo "<p class='doujin-title' style='text-decoration: none;'>$currentPage/$numFiles</p>";
echo "</div>";
echo "<div class='prev-next'>";
if($page<$numFiles-1) {
	$nextImgPath = $relative_directory . $dir . "/" . $files[$page+1];
	$nextPage = $currentPage + 1;
	$url = getUrl($dirId, $nextPage, $readingSubdir);
	echo "<a class='next' href='$url'><i class='fas fa-arrow-right'></i></a>";
}
echo "</div>";
// Close the navigator div as well
echo "</div>";

// Close the main div
echo "</div>";
