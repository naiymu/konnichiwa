<?php
require_once("scripts/login.php");
require_once("scripts/error.php");
require_once("scripts/start.php");
require_once("scripts/directory.php");

startPage("home", "add_to_fav");

$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
if($connection->connect_errno) {
	echo "<div class='main'>";
	$error = getErrorString("Could not connect to the database.", $connection->connect_errno);
	echo $error;
	echo "</div>";
	$connection->close();
	die();
}

$letter = "spcl";
$validLetters = range('A', 'Z');

// Check if the letter has been passed as URL parameter
if(isset($_GET['l'])) {
	$l = $_GET['l'];
	if(strlen($l) > 1 && $l[0] !== '%') {
		$letter = $l[0];
	}
	else if(strlen($l) === 1) {
		$letter = $l;
	}
}

$letter = strtoupper($letter);

// If the url paramater letter is not a valid letter
// just select the '#' page
if(!in_array($letter, $validLetters)) {
	$letter = "spcl";
}

$sql = "SELECT * FROM directories WHERE dirName LIKE '$letter%' ORDER BY dirName";

if($letter === 'spcl') {
	$sql = "SELECT * FROM directories WHERE dirName REGEXP '^[^a-zA-Z]' ORDER BY dirName";
}

$result = $connection->query($sql);

echo "<div class='main column-flex'>";
echo "<div class='letter-selector'>";

// Got to print the special character link separately
if($letter === 'spcl') {
	echo "<a class='letter-link ll-active' href='/index.php?l=%spcl'>#</a>";
}
else {
	echo "<a class='letter-link' href='/index.php?l=%spcl'>#</a>";
}

foreach ($validLetters as $char) {
	if($char === $letter) {
		echo "<a class='letter-link ll-active' href='/index.php?l=$char'>$char</a>";
	}
	else {
		echo "<a class='letter-link' href='/index.php?l=$char'>$char</a>";
	}
}

// Close the letter selector div
echo "</div>";

echo "<div class='row-flex'>";
if(!$result) {
	$error = getErrorString("Query unsuccessful", $connection->errno);
	$connection->close();
	die($error);
}

$rows = $result->num_rows;

if($rows > 0) {
	while($row = $result->fetch_assoc()) {
		$dirName = $row['dirName'];
		$dirCover = $row['dirCover'];
		$dirId = $row['dirId'];
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
		$coverPath = $relative_directory . $dirName . "/" . htmlspecialchars($dirCover, ENT_QUOTES);
		if(!$dir_exists) {
			$coverPath = "";
		}
		$data = array(
			'dir' => $dirId
		);
		$url = "info.php?" . http_build_query($data);
		$dirName = htmlspecialchars($dirName, ENT_QUOTES);
		$div = <<<EOF
		<div class="img-card">
		<a id='$dirId-fav' class='favourite no'><i class='far fa-heart'></i><i class='fas fa-heart'></i></a>
		<a id='$dirId-bmk' class='bookmark bmk-no'><i class='far fa-bookmark'></i><i class='fas fa-bookmark'></i></a>
		<a href='$url'>
			<div class="img-container">
				<img src="$coverPath">
			</div>
			<div class="img-data">
				<p class="img-data-text">$dirName</p>
			</div>
		</a>
		</div>
EOF;
		if($isFav) {
			$div = str_replace("favourite no", "favourite yes", $div);
		}
		if($isBmk) {
			$div = str_replace("bookmark bmk-no", "bookmark bmk-yes", $div);
		}
		echo $div;
	}
}
else {
	echo "<div class='frame' style='width: 500px;'>";
	echo "<div class='container'>";
	$error = getErrorString("No directory starting with '$letter'", 0);
	echo $error;
	echo "</div>";
	echo "</div>";
}
// Close the row-flex div
echo "</div>";
// Close the main div
echo "</div>";

$result->close();
$connection->close();
