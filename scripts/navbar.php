<?php
function getNavbar($active) {
	$navbar = <<<EOF
<div class="navbar">
	<div class="navbar-item">
		<div class="link first">
			<a href="index.php">Home</a>
			<div id="home-line" class="underline"></div>
		</div>
	</div>
	<div class="navbar-item">
		<div class="link">
			<a href="new.php">New</a>
			<div id="new-line" class="underline"></div>
		</div>
	</div>
	<div class="navbar-item">
		<div class="link">
			<a href="get.php">Download</a>
			<div id="download-line" class="underline"></div>
		</div>
	</div>
	<div class="navbar-item">
		<div class="link">
			<a href="list.php">List</a>
			<div id="list-line" class="underline"></div>
		</div>
	</div>
	<div class="navbar-item">
		<div class="link">
			<a href="tags.php">Tags</a>
			<div id="tags-line" class="underline"></div>
		</div>
	</div>
	<div class="navbar-item">
		<div class="link">
			<a href="authors.php">Authors</a>
			<div id="authors-line" class="underline"></div>
		</div>
	</div>
	<div class="navbar-item">
		<div class="link">
			<a href="favourites.php">Favourites</a>
			<div id="favourites-line" class="underline"></div>
		</div>
	</div>
	<div class="navbar-item">
		<div class="link">
			<a href="bookmarks.php">Bookmarks</a>
			<div id="bookmarks-line" class="underline"></div>
		</div>
	</div>
	<div class="navbar-item">
		<div class="link">
			<a href="add.php"><i class="fa fa-plus-circle"></i></a>
		</div>
	</div>
</div>
EOF;
	$search = $active . "-line\" class=\"underline";
	$replacement = $search . " active";
	$navbar = str_replace($search, $replacement, $navbar);
	return $navbar;
}
