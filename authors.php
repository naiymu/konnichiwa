<?php
require_once("scripts/directory.php");
require_once("scripts/login.php");
require_once("scripts/error.php");
require_once("scripts/start.php");

$listBullet = "fa-chevron-circle-right";

startPage("authors");

$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
if($connection->connect_errno) {
	echo "<div class='main'>";
	$error = getErrorString("Could not connect to the database.", $connection->connect_errno);
	echo $error;
	echo "</div>";
	exit();
}

echo "<a href='#bottom' class='to-bottom'><i class='fa fa-arrow-circle-down'></i></a>";
echo "<a href='#top' class='to-up'><i class='fa fa-arrow-circle-up'></i></a>";
echo "<a href='#bottom' class='to-bottom'><i class='fa fa-arrow-circle-down'></i></a>";
echo "<div id='top'></div>";
echo "<div class='main'>";
echo "<div class='frame' style='text-align: left;'>";
echo "<div class='container'>";
echo <<<EOF
<div class='frame-head'>
	<p>Authors</p>
	<div class='underline active black'></div>
</div>
EOF;

$sql = "SELECT * FROM authors ORDER BY author ASC";
$result = $connection->query($sql);

if(!$result) {
	$error = getErrorString("Query unsuccessful", $connection->errno);
	$connection->close();
	die($error);
}

$rows = $result->num_rows;
if($rows > 0) {
	$sectionLetter = '';
	echo "<ul class='fa-ul'>";
	while($row = $result->fetch_assoc()) {
		$author = $row['author'];
		$authorId = $row['authorId'];
		$letter = $author[0];
		if($letter !== $sectionLetter) {
			$sectionLetter = $letter;
			echo "<div class='author-list-letter'>$sectionLetter</div>";
			echo "<div class='bookmark-hanger'></div>";
		}
		$data = array(
			'author' => $authorId
		);
		$url = "byauthor.php?" . http_build_query($data);
		echo "<li class='authors-all'><a class='authors-link' href='$url'><span class='fa-li'><i class='fas $listBullet'></i></span>$author</a></li>";
	}
	echo "</ul>";
	echo "<div id='bottom'></div>";
}
else {
	echo "<div class='frame' style='width: 500px;'>";
	echo "<div class='container'>";
	$error = getErrorString("No authors found", 0);
	echo $error;
	echo "</div>";
	echo "</div>";
}

echo "</div>";
echo "</div>";
