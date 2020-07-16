<?php
require_once("simple_html_dom.php");
require_once("html_funcs.php");
require_once("add.php");
require_once("error.php");
require_once("directory.php");

$id = "";
$fileName = "";
$includeGroups = false;
if(isset($_POST['grpauth'])) {
	$includeGroups = true;
}
$pages = 0;
$mode = 0775;
// Store the directory
$directory = $image_directory;
$baseUrl = "https://nhentai.net/g/";
$listBullet = "fa-angle-right";
$progressFile = $_SERVER['DOCUMENT_ROOT'] . "/tmp/progress";

// Delete the progress file so that no output is put in at first
if(is_file($progressFile)) {
	unlink($progressFile);
}

if (!$dir_exists) {
	$error = getErrorString("Directory does not exist", 0);
	die($error);
}

function getName($fileName, $index, $extension) {
	$imgName = "";
	if($fileName !== "") {
		$imgName = $fileName . " " . $index . $extension;
	}
	else {
		$imgName = $index . $extension;
	}
	return $imgName;
}

$id = trim($_REQUEST['id']);
$fileName = trim($_REQUEST['name']);

if($id === "") {
	$error = getErrorString("No ID provided.", 0);
	die($error);
}

$url = $baseUrl . $id;

$html = getHtml($url);
// Exit if an error occurs (mostly 404)
if(!$html) {
	$error = getErrorString("Could not retrieve the webpage! Teehee!", 0);
	die($error);
}

$title = getTitle($html);
echo "<a class='doujin-title' href='" . $url . "'>" . $title . "</a>";

$pages = getPages($html);
echo "<p class='download-data'>(" . $pages . ")</p>";

// Get artists and display them
$artists = getArtists($html, $includeGroups);
echo "<div class='doujin-artists'>";
foreach ($artists as $artist) {
	echo "<p class='doujin-preview-artist'>" . $artist . "</p>";
}
echo "</div>";

// Get tags and display them
$tags = getTags($html);
echo "<div class='doujin-tags'>";
foreach ($tags as $tag) {
	echo "<p class='doujin-preview-tag'>" . $tag . "</p>";
}
echo "</div>";

$directory = $directory . $title;

echo "<div class='download-data-div'>";
echo "<ul class='fa-ul'>";

if(is_dir($directory)) {
	echo "<li class='download-data'><span class='fa-li'><i class='fas $listBullet'></i></span>Directory already exists</li>";
}
else if(mkdir($directory, $mode)) {
	echo "<li class='download-data'><span class='fa-li'><i class='fas $listBullet'></i></span>Directory created successfully</li>";
}
else {
	echo "<li class='download-data'><span class='fa-li'><i class='fas $listBullet'></i></span>Directory does not exist</li>";
	echo "<li class='download-data'><span class='fa-li'><i class='fas $listBullet'></i></span>Could not create directory</li>";
	echo "<li class='download-data'><span class='fa-li'><i class='fas $listBullet'></i></span>Aborting download</li>";
	die();
}

// Get the general url of image from first page
$imgUrl = getCover($url);
// Get extension of the image
$dotLast = strrpos($imgUrl, ".");
$extension = substr($imgUrl, $dotLast);
// Strip the url of the page number but include the last slash
$slashLast = strrpos($imgUrl, "/");
$imgUrl = substr($imgUrl, 0, $slashLast+1);

$cover = getName($fileName, 1, $extension);
$coverPath = $directory . "/" . $cover;
if(is_file($coverPath)) {
	echo "<li class='download-data'><span class='fa-li'><i class='fas $listBullet'></i></span>File with same name already exists</li>";
	echo "<li class='download-data'><span class='fa-li'><i class='fas $listBullet'></i></span>Aborting download</li>";
	echo "<li class='download-data'><span class='fa-li'><i class='fas $listBullet'></i></span>Did not add directory to database</li>";
	// Close the download-data div even if the program needs to exit
	echo "</ul>";
	echo "</div>";
	die();
}

echo "<li class='download-data'><span class='fa-li'><i class='fas $listBullet'></i></span>File does not already exist</li>";
echo "<li class='download-data'><span class='fa-li'><i class='fas $listBullet'></i></span>Proceeding with download</li>";

$stmntResult = addToDB($title, $cover, $artists, $tags);
if($stmntResult === true) {
	echo "<li class='download-data'><span class='fa-li'><i class='fas $listBullet'></i></span>Added directory to database</li>";
}
else {
	$error = $stmntResult['error'];
	echo "<li class='download-data'><span class='fa-li'><i class='fas $listBullet'></i></span>$error</li>";
	echo "<li class='download-data'><span class='fa-li'><i class='fas $listBullet'></i></span>Could not add directory to database</li>";
}
echo "</ul>";
// Close the download-data div
echo "</div>";

// Exit so the user doesn't have to wait for the download to complete
fastcgi_finish_request();

for($i=1; $i<=$pages; $i++) {
	$imgPath = $directory . "/" . getName($fileName, $i, $extension);
	$downloadUrl = $imgUrl . $i . $extension;
	$cmd = "wget -qO " . escapeshellarg($imgPath) . " " . escapeshellarg($downloadUrl);
	$data = $i . " " . $pages;
	file_put_contents($progressFile, $data, LOCK_EX);
	shell_exec($cmd);
}
