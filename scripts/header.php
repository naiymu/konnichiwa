<?php
function getHead(...$scripts) {
	$head = <<<EOF
<head>
	<link rel="shortcut icon" type="image/png" href="/assets/logo.png"/>
	<link rel="stylesheet" href="/styles/site.css">
	<link rel="stylesheet" href="/assets/fontawesome/css/all.css">
	<script src="/scripts/jquery.js"></script>
EOF;
	foreach ($scripts as $script) {
		$head .= "<script src='/scripts/" . $script . ".js'></script>";
	}
	$head .= "</head>";
	$head .= "<meta charset='utf-8'>";
	return $head;
}
function getTitlebar() {
	$titlebar = <<<EOF
<title>Konnichiwa!</title>
<div class="header">
	<a href="index.php">
		<h1 class="title">Konnichiwa!</h1>
	</a>
</div>
<div class='search-div'>
	<form action="/search.php" method="GET">
		<div class="search-box">
			<input type="search" class="text" name="q" placeholder="Search" required pattern=".*\S+.*" title="Should not be empty">
			<button class='search-btn'><i class='fa fa-search'></i></button>
		</div>
	</form>
</div>
EOF;
	return $titlebar;
}
