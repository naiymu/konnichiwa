<?php
// Get content of the given url
function getHtml($url) {
	$html = file_get_html($url);
	return $html;
}

// Get the title of the page
// The title of the page has the site name in it and a down-angle-quote
// So we remove those
function getTitle($html) {
	$title = $html->find('title')[0]->innerText();
	$siteTitle = strpos($title, "nhentai");
	$sepLen = strlen("&raquo");
	$title = trim(substr($title, 0, $siteTitle-$sepLen-2));
	return $title;
}

// The artist name contains the number of books associated with it
// So we remove it as well
function getArtists($html) {
	// Get all the links (which also contain the artists name)
	$links = $html->find('a');
	$artists = [];
	// Find all artists and save them in an array
	foreach ($links as $link) {
		$href = $link->href;
		if(strpos($href, "artist/")) {
			$artist = $link->innerText();
			// Remove any span (or other) html tags that may be
			// present in the link-inner-text
			$artist = strip_tags($artist);
			$bracket = strpos($artist, "(");
			$artist = substr($artist, 0, $bracket);
			$artist = ucwords($artist);
			$artists[] = trim($artist);
		}
	}
	return $artists;
}

function getTags($html) {
	// Get all the links (which also contain the tags)
	$links = $html->find('a');
	$tags = [];
	// Find all artists and save them in an array
	foreach ($links as $link) {
		$href = $link->href;
		if(strpos($href, "tag/")) {
			$tag = $link->innerText();
			$tag = strip_tags($tag);
			$bracket = strpos($tag, "(");
			$tag = substr($tag, 0, $bracket);
			$tag = ucwords($tag);
			$tags[] = trim($tag);
		}
	}
	return $tags;
}

// Return the cover image src
function getCover($url) {
	$src = "";
	$url = $url . "/1";
	$imgHtml = file_get_html($url);
	$imgs = $imgHtml->find('img');
	foreach ($imgs as $img) {
		$src = $img->src;
		if(strpos($src, "1.")) {
			$cover = $src;
			break;
		}
	}
	return $src;
}

// Get number of pages
function getPages($html) {
	$pages = "";
	$divs = $html->find('div');
	foreach ($divs as $div) {
		$text = $div->innerText();
		if(strpos($text, "pages")) {
			preg_match("/[0-9]+ pages/", $text, $pages);
			$pages = $pages[0];
			$pages = preg_replace("/[^0-9]/", "", $pages);
			break;
		}
	}
	$pages = (int) $pages;
	return $pages;
}
