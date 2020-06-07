<?php
function startPage($page, ...$scripts) {
	require_once("scripts/header.php");
	require_once("scripts/navbar.php");
	$head = getHead(...$scripts);
	$titlebar = getTitlebar();
	$start = <<<EOF
	<!DOCTYPE html>
		$head
	<script>
	$(window).on(function() {
		$("body").removeClass("preload");
	});
	</script>
	<body class="preload">
		$titlebar
	<div id='message' class='message-box' style='display:none;'>
		<p id='message-text'></p>
		<a id='message-close' class='close-btn'>
			<i class='fa fa-times'></i>
		</a>
	</div>
EOF;
	echo $start;

	$navbar = getNavbar($page);
	echo $navbar;
}
