<?php
// Get content of the given url
function getHtml($url) {
	$html = file_get_html($url);
	return $html;
}

// Get the title of the page
function getTitle($html) {
	$title = $html->find('h1.title')[0];
	$title = $title->find('span.pretty')[0];
	$title = $title->innerText();
	$title = html_entity_decode($title, ENT_QUOTES);
	$title = preg_replace('/[^A-Za-z0-9\- ]/', '', $title);
	$title = preg_replace('/\s+/', " ", $title);
	return $title;
}

// The artist name contains the number of books associated with it
// So we remove it as well
function getArtists($html, $includeGroups) {
	// Get all the links (which also contain the artists name)
	$links = $html->find('a.tag');
	$artists = [];
	// Find all artists and save them in an array
	foreach ($links as $link) {
		$href = $link->href;
		if(strpos($href, "/artist/")===0 || ($includeGroups && strpos($href, "/group/")===0)) {
			$artist = $link->find('span.name')[0]->innerText();
			$artist = ucwords($artist);
			$artists[] = trim($artist);
		}
	}
	return $artists;
}

function getTags($html) {
	// Get all the links (which also contain the tags)
	$links = $html->find('a.tag');
	$tags = [];
	// Find all artists and save them in an array
	foreach ($links as $link) {
		$href = $link->href;
		if(strpos($href, "/tag/")===0) {
			$tag = $link->find('span.name')[0]->innerText();
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
	$imgs = $imgHtml->find('#image-container')[0];
	$img = $imgs->find('img')[0];
	$src = $img->src;
	return $src;
}

// Get number of pages
function getPages($html) {
	$pages = null;
	$links = $html->find('a.tag');
	foreach ($links as $link) {
		$href = $link->href;
		if(strpos($href, "q=pages")) {
			$pages = $link->find('span.name')[0]->innerText();
			$pages = trim($pages);
			break;
		}
	}
	$pages = (int) $pages;
	return $pages;
}
