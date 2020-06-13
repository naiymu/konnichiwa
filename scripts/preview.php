<?php
require_once("simple_html_dom.php");
require_once("html_funcs.php");
require_once("error.php");

// The variables to store the ID and BASE_URL
$id = "";
$baseUrl = "https://nhentai.net/g/";

// Get the id from the user filled form
$id = trim($_POST['id']);
$includeGroups = false;
if(isset($_POST['grpauth'])) {
	$includeGroups = true;
}
if($id === "") {
	$error = getErrorString("No ID provided.", 0);
	die($error);
}

// Make the url from the base url and the given id
$url = $baseUrl . $id;

$html = getHtml($url);
// Exit if an error occurs (mostly 404)
if(!$html) {
	$error = getErrorString("Looks like an error occurred! Teehee!", 0);
	die($error);
}

/*================================== TITLE ===================================*/
$title = getTitle($html);
echo "<a class='doujin-title' href='" . $url . "'>" . $title . "</a>";
/*============================================================================*/

/*================================== PAGES ===================================*/
$pages = getPages($html);
echo "<p class='doujin-pageno'>(" . $pages . ")</p>";
/*============================================================================*/

/*================================= ARTISTS ==================================*/
$artists = getArtists($html, $includeGroups);
echo "<div class='doujin-artists'>";
foreach ($artists as $artist) {
	echo "<p class='doujin-preview-artist'>" . $artist . "</p>";
}
echo "</div>";
/*============================================================================*/

/*================================== TAGS ====================================*/
$tags = getTags($html);
echo "<div class='doujin-tags'>";
foreach ($tags as $tag) {
	echo "<p class='doujin-preview-tag'>" . $tag . "</p>";
}
echo "</div>";
/*============================================================================*/

/*================================== COVER ===================================*/
$src = getCover($url);
$path = $_SERVER['DOCUMENT_ROOT'] . "/tmp/img";
shell_exec("wget -O '" . $path . "' '" . "$src" . "'");
echo "<img class='preview' id='cover' src='/tmp/img'>";
/*============================================================================*/
