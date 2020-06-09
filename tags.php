<?php
require_once("scripts/directory.php");
require_once("scripts/login.php");
require_once("scripts/error.php");
require_once("scripts/start.php");

$listBullet = "fa-chevron-circle-right";

startPage("tags", "tag_check");

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
	<p>Tags</p>
	<div class='underline active black'></div>
</div>
EOF;

$sql = "SELECT * FROM tags ORDER BY tag ASC";
$result = $connection->query($sql);

if(!$result) {
	$error = getErrorString("Query unsuccessful", $connection->errno);
	$connection->close();
	die($error);
}

$rows = $result->num_rows;
if($rows > 0) {
	echo "<form method='POST' action='bytag.php'>";
	echo "<div class='tag-list'>";
	$sectionLetter = '';
	while($row = $result->fetch_assoc()) {
		$tag = $row['tag'];
		$tagName = ucwords($tag);
		$letter = $tagName[0];
		if($letter !== $sectionLetter) {
			$sectionLetter = $letter;
			echo "<div class='tag-list-letter'>$sectionLetter</div>";
			echo "<div class='bookmark-hanger tag-hanger'></div>";
		}
		$tagId = $row['tagId'];
		echo "<label for='$tagId'>
		<input type='checkbox' id='$tagId' name='tags[]' value='$tag'/>
		<span>$tagName</span>
		</label><br>";
	}
	echo "</div>";
	echo "<div class='underline active black tag-list-line'></div>";
	echo "<button id='submit-btn' type='submit' disabled>Get Results</button>";
	echo "<button id='clear-btn' type='reset' disabled>Clear All</button>";
	echo "</form>";
	echo "<div id='bottom'></div>";
}
else {
	$error = getErrorString("No tags found", 0);
	echo $error;
}

echo "</div>";
echo "</div>";
