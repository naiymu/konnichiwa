<?php
require_once("scripts/directory.php");
require_once("scripts/login.php");
require_once("scripts/error.php");
require_once("scripts/start.php");

$listBullet = "fa-chevron-circle-right";

startPage("list", "add_to_fav");

$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
if($connection->connect_errno) {
	echo "<div class='main'>";
	$error = getErrorString("Could not connect to the database.", $connection->connect_errno);
	echo $error;
	echo "</div>";
	exit();
}

echo "<a href='#top' class='to-up'><i class='fa fa-arrow-circle-up'></i></a>";
echo "<a href='#bottom' class='to-bottom'><i class='fa fa-arrow-circle-down'></i></a>";
echo "<div id='top'></div>";
echo "<div class='main'>";
echo "<div class='frame' style='text-align: left;'>";
echo "<div class='container'>";
echo <<<EOF
<div class='frame-head'>
	<p>List</p>
	<div class='underline active black'></div>
</div>
EOF;

$sql = "SELECT * FROM directories ORDER BY dirName ASC";
$result = $connection->query($sql);

if(!$result) {
	$error = getErrorString("Query unsuccessful", $connection->errno);
	$connection->close();
	die($error);
}

$rows = $result->num_rows;
if($rows > 0) {
	$sectionLetter = '';
	echo "<ul class='list fa-ul'>";
	while($row = $result->fetch_assoc()) {
		$dirName = $row['dirName'];
		$letter = $dirName[0];
		if($letter !== $sectionLetter) {
			$sectionLetter = $letter;
			echo "<div class='list-letter'>$sectionLetter</div>";
			echo "<div class='bookmark-hanger'></div>";
		}
		$dirId = $row['dirId'];
		$dirCover = $row['dirCover'];
		$data = array(
			'dir' => $dirId
		);
		$url = "info.php?" . http_build_query($data);
		// Check if the dirId is in favourites
		$isFav = false;
		$favStmnt = $connection->prepare("SELECT * FROM favourites WHERE dirId=(?)");
		$favStmnt->bind_param("i", $dirId);
		$favStmnt->execute();
		$favs = $favStmnt->get_result();
		if($favs->num_rows > 0) {
			$isFav = true;
		}
		$favs->close();
		$favStmnt->close();
		$favourite = "<a id='$dirId-fav' class='favourite no list-fav'><i class='far fa-heart'></i><i class='fas fa-heart'></i></a>";
		// Check if the dirId is in bookmarks
		$isBmk = false;
		$bmkStmnt = $connection->prepare("SELECT * FROM bookmarks WHERE dirId=(?)");
		$bmkStmnt->bind_param("i", $dirId);
		$bmkStmnt->execute();
		$bmks = $bmkStmnt->get_result();
		if($bmks->num_rows > 0) {
			$isBmk = true;
		}
		$bmks->close();
		$bmkStmnt->close();
		$bookmark = "<a id='$dirId-bmk' class='bookmark bmk-no list-bmk'><i class='far fa-bookmark'></i><i class='fas fa-bookmark'></i></a>";
		if($isFav) {
			$favourite = str_replace("favourite no", "favourite yes", $favourite);
		}
		if($isBmk) {
			$bookmark = str_replace("bookmark bmk-no", "bookmark bmk-yes", $bookmark);
		}
		echo "
		<li class='list-all'>
			<a class='list-link' href='$url'>
			<span class='fa-li'>
				<i class='fas $listBullet'></i>
			</span>
			$dirName
			</a>
			<div class='list-icons'>
			$favourite
			$bookmark
			</div>
		</li>
		";
	}
	echo "</ul>";
	echo "<div id='bottom'></div>";
}
else {
	echo "<div class='frame' style='width: 500px;'>";
	echo "<div class='container'>";
	$error = getErrorString("No directory starting with '$letter'", 0);
	echo $error;
	echo "</div>";
	echo "</div>";
}

echo "</div>";
echo "</div>";
