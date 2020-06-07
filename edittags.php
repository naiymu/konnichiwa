<?php
require_once("scripts/directory.php");
require_once("scripts/login.php");
require_once("scripts/error.php");
require_once("scripts/start.php");

$listBullet = "fa-chevron-circle-right";

startPage("tags");

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
$attachedTags = [];

if(isset($_GET['dir'])) {
	$dirId = $_GET['dir'];
	$data = array(
		'dir' => $dirId
	);
	$infoUrl = "info.php?" . http_build_query($data);
	$stmnt = $connection->prepare("SELECT * FROM dir_tag_link WHERE dirId=(?)");
	$stmnt->bind_param("i", $dirId);
	$stmnt->execute();
	$result = $stmnt->get_result();
	if($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$attachedTags[] = $row['tagId'];
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
	if(isset($_POST['tags']) && !empty($_POST['tags'])) {
		$tagIds = $_POST['tags'];
		$tagNum = count($tagIds);
		$tagsToRemove = array_diff($attachedTags, $tagIds);
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
		<p>Bam! Updated tags for \"$htmlDirName\"</p>
		</div>
		<div class='doujin-tags edittags-tags first'>
		";
		foreach ($tagIds as $tagId) {
			$stmnt = $connection->prepare("SELECT * FROM tags WHERE tagId=(?)");
			$stmnt->bind_param("i", $tagId);
			$stmnt->execute();
			$result = $stmnt->get_result();
			$row = $result->fetch_assoc();
			$tag = $row['tag'];
			$result->close();
			$stmnt->close();
			$tagBox = "<p class='doujin-preview-tag' title='id : $tagId'>" . ucwords($tag) . "</p>";
			$stmnt = $connection->prepare("SELECT * FROM dir_tag_link WHERE dirId=(?) AND tagId=(?)");
			$stmnt->bind_param("ii", $dirId, $tagId);
			$stmnt->execute();
			$result = $stmnt->get_result();
			if($result->num_rows > 0) {
				echo $tagBox;
				$result->close();
				$stmnt->close();
				continue;
			}
			$result->close();
			$stmnt->close();
			$stmnt = $connection->prepare("INSERT INTO dir_tag_link (dirId, tagId) VALUES (?, ?)");
			$stmnt->bind_param("ii", $dirId, $tagId);
			if(!$stmnt->execute()) {
				$tagBox = str_replace("doujin-preview-tag", "doujin-preview-tag error-doujin-tag", $tagBox);
			}
			else {
				$tagBox = str_replace("doujin-preview-tag", "doujin-preview-tag green-doujin-tag", $tagBox);
			}
			echo $tagBox;
			$stmnt->close();
		}
		foreach ($tagsToRemove as $tagId) {
			$stmnt = $connection->prepare("SELECT * FROM tags WHERE tagId=(?)");
			$stmnt->bind_param("i", $tagId);
			$stmnt->execute();
			$result = $stmnt->get_result();
			$row = $result->fetch_assoc();
			$tag = $row['tag'];
			$result->close();
			$tagBox = "<p class='doujin-preview-tag red-doujin-tag' title='id : $tagId'>" . ucwords($tag) . "</p>";
			$stmnt->close();
			$stmnt = $connection->prepare("DELETE FROM dir_tag_link WHERE dirId=(?) AND tagId=(?)");
			$stmnt->bind_param("ii", $dirId, $tagId);
			if(!$stmnt->execute()) {
				$tagBox = str_replace("doujin-preview-tag", "doujin-preview-tag error-doujin-tag", $tagBox);
			}
			$stmnt->close();
			echo $tagBox;
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
<div class='edittags-head'>
	<i class='fa fa-edit'></i>
	<p>Edit tags</p>
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
	$data = array(
		'dir' => $dirId
	);
	$url = "edittags.php?" . http_build_query($data);
	echo "<form method='POST' action='$url'>";
	echo "<div class='tag-list'>";
	$sectionLetter = 'A';
	echo "<div class='tag-list-letter'>$sectionLetter</div>";
	echo "<div class='bookmark-hanger tag-hanger'></div>";
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
		$tagCheckbox = "<label for='$tagId'>
		<input type='checkbox' id='$tagId' name='tags[]' value='$tagId'/>
		<span>$tagName</span>
		</label><br>";
		if(in_array($tagId, $attachedTags)) {
			$tagCheckbox = str_replace("/>", "checked>", $tagCheckbox);
		}
		echo $tagCheckbox;
	}
	echo "</div>";
	echo "<div class='underline active black tag-list-line'></div>";
	echo "<button id='submit-btn' type='submit'>Submit</button>";
	echo "<button id='clear-btn' type='reset'>Reset</button>";
	echo "</form>";
	echo "<div id='bottom'></div>";
}
else {
	echo "<div class='frame' style='width: 500px;'>";
	echo "<div class='container'>";
	$error = getErrorString("No tags found", 0);
	echo $error;
	echo "</div>";
	echo "</div>";
}

echo "</div>";
echo "</div>";
