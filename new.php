<?php
require_once("scripts/login.php");
require_once("scripts/error.php");
require_once("scripts/start.php");
require_once("scripts/directory.php");

startPage("new", "add_to_fav");

$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
if($connection->connect_errno) {
	echo "<div class='main'>";
	$error = getErrorString("Could not connect to the database.", $connection->connect_errno);
	echo $error;
	echo "</div>";
	$connection->close();
	die();
}

$offset = 0;
$N = 50;
$result = null;
if(isset($_GET['offset'])) {
	$offset = trim($_GET['offset']);
	if(!ctype_digit($offset)) {
		$error = getErrorString("Given offset is not a valid integer", 400);
		echo "<div class='main'>";
		echo $error;
		echo "</div>";
		$connection->close();
		die();
	}
}
if(isset($_GET['N'])) {
	$N = trim($_GET['N']);
	if(!ctype_digit($N)) {
		$error = getErrorString("Given limit (N) is not a valid integer", 400);
		echo "<div class='main'>";
		echo $error;
		echo "</div>";
		$connection->close();
		die();
	}
}

// Select the last 'N' directories ordered from newest to oldest
$sql = "SELECT * FROM directories ORDER BY dirId DESC LIMIT ?, ?";
$stmnt = $connection->prepare($sql);
$stmnt->bind_param("ii", $offset, $N);
if($stmnt->execute()) {
	$result = $stmnt->get_result();
}
else {
	echo "<div class='main'>";
	echo "<div class='frame'>";
	echo "<div class='container'>";
	$error = getErrorString("Query unsuccessful", $connection->errno);
	echo $error;
	echo "</div>";
	echo "</div>";
	echo "</div>";
	$stmnt->close();
	$connection->close();
	die();
}

$first = $offset + 1;
$last = $N + $offset;
$bracketValue = "$first to $last";
echo "
<div class='main column-flex'>
<div class='frame tag-head-frame new-head'>
	<div class='container tag-head-container new-head-container'>
		<p class='tag-title new-title'>Latest ($N)</p>
		<span class='new-title-count'>$bracketValue</span>
	</div>
</div>
<div class='new-input column-flex last'>
	<form method='GET' action='new.php'>
		<div class='row-flex'>
			<div class='in-row'>
				<p class='label wide'>Offset</p>
				<input name='offset' type='number' min='0' value='$offset'></input>
			</div>
			<div class='in-row in-row-last'>
				<p class='label wide'>Get latest N directories</p>
				<input name='N' type='number' min='10' value='$N'></input>
			</div>
		</div>
		<button type='submit' class='new-form-button'>Submit</button>
	</form>
</div>
<div class='row-flex'>
";

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
	echo "<div class='frame'>";
	echo "<div class='container'>";
	$error = getErrorString("No new directories found", 0);
	echo $error;
	echo "</div>";
	echo "</div>";
}
// Close the row-flex div
echo "</div>";
// Close the main div
echo "</div>";

$stmnt->close();
$connection->close();
