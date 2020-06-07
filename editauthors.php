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

$dirId = null;
$dirName = null;
$infoUrl = null;
$attachedAuthors = [];

if(isset($_GET['dir'])) {
	$dirId = $_GET['dir'];
	$data = array(
		'dir' => $dirId
	);
	$infoUrl = "info.php?" . http_build_query($data);
	$stmnt = $connection->prepare("SELECT * FROM dir_author_link WHERE dirId=(?)");
	$stmnt->bind_param("i", $dirId);
	$stmnt->execute();
	$result = $stmnt->get_result();
	if($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$attachedAuthors[] = $row['authorId'];
		}
	}
	$stmnt->close();
	$stmnt = $connection->prepare("SELECT * FROM directories WHERE dirId=(?)");
	$stmnt->bind_param("i", $dirId);
	$stmnt->execute();
	$result = $stmnt->get_result();
	if($result->num_rows > 0) {
		$row = $result->fetch_assoc();
		$dirName = $row['dirName'];
	}
	else {
		echo "<div class='main'>";
		$error = getErrorString("The directory id does not exist", 404);
		echo $error;
		echo "</div>";
		$result->close();
		$stmnt->close();
		$connection->close();
		exit();
	}
	$result->close();
	$stmnt->close();
	if(isset($_POST['authors']) && !empty($_POST['authors'])) {
		$authorIds = $_POST['authors'];
		$authorNum = count($authorIds);
		$authorsToRemove = array_diff($attachedAuthors, $authorIds);
		$htmlDirName = htmlspecialchars($dirName, ENT_QUOTES);
		echo "
		<div class='main'>
		<div class='frame'>
		<div class='container'>
		<div class='frame-head'>
		<a href='$infoUrl'>$htmlDirName</a>
		<div class='underline active black'></div>
		</div>
		<div>
		<img class='wide-image' src='/assets/bam.gif'>
		</div>
		<div class='sole-message last'>
		<p>Bam! Updated authors for \"$htmlDirName\"</p>
		</div>
		<div class='doujin-tags edittags-tags first'>
		";
		foreach ($authorIds as $authorId) {
			$stmnt = $connection->prepare("SELECT * FROM authors WHERE authorId=(?)");
			$stmnt->bind_param("i", $authorId);
			$stmnt->execute();
			$result = $stmnt->get_result();
			$row = $result->fetch_assoc();
			$author = $row['author'];
			$result->close();
			$stmnt->close();
			$authorBox = "<p class='doujin-preview-artist' title='id : $authorId'>" . ucwords($author) . "</p>";
			$stmnt = $connection->prepare("SELECT * FROM dir_author_link WHERE dirId=(?) AND authorId=(?)");
			$stmnt->bind_param("ii", $dirId, $authorId);
			$stmnt->execute();
			$result = $stmnt->get_result();
			if($result->num_rows > 0) {
				echo $authorBox;
				$result->close();
				$stmnt->close();
				continue;
			}
			$result->close();
			$stmnt->close();
			$stmnt = $connection->prepare("INSERT INTO dir_author_link (dirId, authorId) VALUES (?, ?)");
			$stmnt->bind_param("ii", $dirId, $authorId);
			if(!$stmnt->execute()) {
				$authorBox = str_replace("doujin-preview-artist", "doujin-preview-artist error-doujin-artist", $authorBox);
			}
			else {
				$authorBox = str_replace("doujin-preview-artist", "doujin-preview-artist green-doujin-artist", $authorBox);
			}
			echo $authorBox;
			$stmnt->close();
		}
		foreach ($authorsToRemove as $authorId) {
			$stmnt = $connection->prepare("SELECT * FROM authors WHERE authorId=(?)");
			$stmnt->bind_param("i", $authorId);
			$stmnt->execute();
			$result = $stmnt->get_result();
			$row = $result->fetch_assoc();
			$author = $row['author'];
			$result->close();
			$authorBox = "<p class='doujin-preview-artist red-doujin-artist' title='id : $authorId'>" . ucwords($author) . "</p>";
			$stmnt->close();
			$stmnt = $connection->prepare("DELETE FROM dir_author_link WHERE dirId=(?) AND authorId=(?)");
			$stmnt->bind_param("ii", $dirId, $authorId);
			if(!$stmnt->execute()) {
				$authorBox = str_replace("doujin-preview-artist", "doujin-preview-artist error-doujin-artist", $authorBox);
			}
			$stmnt->close();
			echo $authorBox;
		}
		$connection->close();
		echo "</div>";
		echo "</div>";
		echo "</div>";
		echo "</div>";
		exit();
	}
}
else {
	echo "<div class='main'>";
	$error = getErrorString("Looks like you are accessing this file directly<br>You might want to browse the list first", 400);
	echo $error;
	echo "</div>";
	$connection->close();
	exit();
}

$dirName = htmlspecialchars($dirName, ENT_QUOTES);
echo "<a href='#bottom' class='to-bottom'><i class='fa fa-arrow-circle-down'></i></a>";
echo "<a href='#top' class='to-up'><i class='fa fa-arrow-circle-up'></i></a>";
echo "<a href='#bottom' class='to-bottom'><i class='fa fa-arrow-circle-down'></i></a>";
echo "<div id='top'></div>";
echo "<div class='main'>";
echo "<div class='frame' style='text-align: left;'>";
echo "<div class='container'>";
echo <<<EOF
<div class='frame-head'>
	<a href='$infoUrl'>$dirName</a>
	<div class='underline active black'></div>
</div>
<div class='editauthors-head'>
	<i class='fa fa-edit'></i>
	<p>Edit authors</p>
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
	$data = array(
		'dir' => $dirId
	);
	$url = "editauthors.php?" . http_build_query($data);
	echo "<form method='POST' action='$url'>";
	echo "<div class='author-list'>";
	while($row = $result->fetch_assoc()) {
		$author = $row['author'];
		$authorName = ucwords($author);
		$letter = $authorName[0];
		if($letter !== $sectionLetter) {
			$sectionLetter = $letter;
			echo "<div class='tag-list-letter'>$sectionLetter</div>";
			echo "<div class='bookmark-hanger tag-hanger'></div>";
		}
		$authorId = $row['authorId'];
		$authorCheckbox = "<label for='$authorId'>
		<input type='checkbox' id='$authorId' name='authors[]' value='$authorId'/>
		<span>$authorName</span>
		</label><br>";
		if(in_array($authorId, $attachedAuthors)) {
			$authorCheckbox = str_replace("/>", "checked>", $authorCheckbox);
		}
		echo $authorCheckbox;
	}
	echo "</div>";
	echo "<div class='underline active black authors-list-line'></div>";
	echo "<button id='submit-btn' type='submit'>Submit</button>";
	echo "<button id='clear-btn' type='reset'>Reset</button>";
	echo "</form>";
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
