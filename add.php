<?php
require_once("scripts/add.php");
require_once("scripts/login.php");
require_once("scripts/error.php");
require_once("scripts/start.php");

startPage("home", "add");

$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
if($connection->connect_errno) {
	echo "<div class='main'>";
	$error = getErrorString("Could not connect to the database.", $connection->connect_errno);
	echo $error;
	echo "</div>";
	$connection->close();
	die();
}

$table = null;
if(isset($_POST['directorySubmit'])) {
	$table = "directories";
}
else if(isset($_POST['tagSubmit'])) {
	$table = "tags";
}
else if(isset($_POST['authorSubmit'])) {
	$table = "authors";
}
if($table !== null) {
	switch($table) {
		case "directories":
			$dirId = null;
			$tags = null;
			$authors = null;
			$dirName = trim($_POST['dirName']);
			$dirCover = trim($_POST['dirCover']);
			if($dirName !== "" && $dirCover !== "") {
				if(isset($_POST['tags'])) {
					$tags = $_POST['tags'];
				}
				if(isset($_POST['authors'])) {
					$authors = $_POST['authors'];
				}
				$result = addToDB($dirName, $dirCover, $authors, $tags);
				// Don't remove the === true because the method returns error string on error
				if($result === true) {
					$sql = "SELECT dirId FROM directories WHERE dirId=(SELECT MAX(dirId) FROM directories)";
					$maxResult = $connection->query($sql);
					$row = $maxResult->fetch_assoc();
					// Get the id of the just added directory
					$dirId = $row['dirId'];
					$maxResult->close();
					$data = array(
						'dir' => $dirId
					);
					$serverName = $_SERVER['SERVER_NAME'];
					$url = "http://" . $serverName . "/info.php?" . http_build_query($data);
					$htmlDirName = htmlspecialchars($dirName, ENT_QUOTES);
					//----------------------------------------------------------
					// Redirect to info page of the added directory
					//----------------------------------------------------------
					// header("Location: $url");
					//----------------------------------------------------------
					//----------------------------------------------------------
					// Otherwise show a button to redirect
					//----------------------------------------------------------
					// $url = "info.php?" . http_build_query($data);
					// echo "
					// <div class='main'>
					// <div class='frame'>
					// <div class='container'>
					// <div class='frame-head'>
					// <p>$dirName</p>
					// <div class='underline active black last'></div>
					// </div>
					// <div><a href='$url'><button>View</button></a></div>
					// </div>
					// </div>
					// </div>
					// ";
					//----------------------------------------------------------
					// This is default show a success message
					echo "
					<div class='main'>
					<div class='frame'>
					<div class='container'>
					<div class='frame-head'>
					<p>Success</p>
					<div class='underline active black'></div>
					</div>
					<img class='wide-image' src='/assets/bam.gif'>
					<div class='sole-message'>
					<p>Bam! Added \"$htmlDirName\" to directories</p>
					</div>
					<div class='info-read-button'><a href='$url'><button><i class='fa fa-info-circle'></i>Info</button></a></div>
					</div>
					</div>
					</div>
					</div>
					";
					$connection->close();
					exit();
				}
				else {
					$error = $result['error'];
					$errorNo = $result['errorNo'];
					$error = getErrorString($error, $errorNo);
					echo "<div class='main'>";
					echo $error;
					echo "</div>";
					$connection->close();
					die();
				}
			}
			else {
				echo "<div class='main'>";
				$error = getErrorString("Directory name or cover was blank", 400);
				echo $error;
				echo "</div>";
				$connection->close();
				die();
			}
			$connection->close();
			exit();
		break;

		case "tags":
			$tag = trim($_POST['tag']);
			$tag = strtolower($tag);
			if($author === "") {
				echo "<div class='main'>";
				$error = getErrorString("Tag name was blank", 400);
				echo $error;
				echo "</div>";
				$connection->close();
				die();
			}
			$stmnt = $connection->prepare("INSERT INTO tags (tag) VALUES (?)");
			$stmnt->bind_param("s", $tag);
			if($stmnt->execute()) {
				$tag = htmlspecialchars($tag);
				echo "
				<div class='main'>
				<div class='frame'>
				<div class='container'>
				<div class='frame-head'>
				<p>Success</p>
				<div class='underline active black'></div>
				</div>
				<img class='wide-image' src='/assets/bam.gif'>
				<div class='sole-message'>
				<p>Bam! Added \"$tag\" to tags</p>
				</div>
				<div class='underline active black'></div>
				<div class='links'>
				<div><a class='classic-link' href='list.php'>List</a></div>
				<div><a class='classic-link' href='tags.php'>Tags</a></div>
				<div><a class='classic-link' href='authors.php'>Authors</a></div>
				<div><a class='classic-link' href='add.php'>Add</a></div>
				</div>
				</div>
				</div>
				</div>
				</div>
				";
				$stmnt->close();
				$connection->close();
				exit();
			}
			else {
				$error = getErrorString($stmnt->error, $connection->errno);
				echo "<div class='main'>";
				echo $error;
				echo "</div>";
				$stmnt->close();
				$connection->close();
				die();
			}
		break;

		case "authors":
			$author = trim($_POST['author']);
			$author = ucwords($author);
			if($author === "") {
				echo "<div class='main'>";
				$error = getErrorString("Author name was blank", 400);
				echo $error;
				echo "</div>";
				$connection->close();
				die();
			}
			$stmnt = $connection->prepare("INSERT INTO authors (author) VALUES (?)");
			$stmnt->bind_param("s", $author);
			if($stmnt->execute()) {
				$author = htmlspecialchars($author);
				echo "
				<div class='main'>
				<div class='frame'>
				<div class='container'>
				<div class='frame-head'>
				<p>Success</p>
				<div class='underline active black'></div>
				</div>
				<img class='wide-image' src='/assets/bam.gif'>
				<div class='sole-message'>
				<p>Bam! Added \"$author\" to authors</p>
				</div>
				<div class='underline active black'></div>
				<div class='links'>
				<div><a class='classic-link' href='list.php'>List</a></div>
				<div><a class='classic-link' href='tags.php'>Tags</a></div>
				<div><a class='classic-link' href='authors.php'>Authors</a></div>
				<div><a class='classic-link' href='add.php'>Add</a></div>
				</div>
				</div>
				</div>
				</div>
				</div>
				";
				$stmnt->close();
				$connection->close();
				exit();
			}
			else {
				$error = getErrorString($stmnt->error, $connection->errno);
				echo "<div class='main'>";
				echo $error;
				echo "</div>";
				$stmnt->close();
				$connection->close();
				die();
			}
		break;
	}
}

$tagsCheckboxes = "";
$authorsCheckboxes = "";

$result = $connection->query("SELECT * FROM tags ORDER BY tag ASC");
$letter = "";
$first = true;
if($result->num_rows > 0) {
	while ($row = $result->fetch_assoc()) {
		$tag = $row['tag'];
		$tagId = $row['tagId'];
		$tagName = ucwords($tag);
		if($tagName[0] != $letter) {
			$letter = $tagName[0];
			if($first) {
				$tagsCheckboxes .= "<div class='tag-list-letter first embeded-tag-list-letter'>$letter</div>";
				$first = !$first;
			}
			else {
				$tagsCheckboxes .= "<div class='tag-list-letter embeded-tag-list-letter'>$letter</div>";
			}
		}
		$string = "<label for='tag-$tagId'>
		<input type='checkbox' id='tag-$tagId' name='tags[]' value='$tag'/>
		<span>$tagName</span>
		</label><br>";
		$tagsCheckboxes .= $string;
	}
}

$result = $connection->query("SELECT * FROM authors ORDER BY author ASC");
$letter = "";
$first = true;
if($result->num_rows > 0) {
	while ($row = $result->fetch_assoc()) {
		$author = $row['author'];
		$authorId = $row['authorId'];
		$authorName = ucwords($author);
		if($authorName[0] != $letter) {
			$letter = $authorName[0];
			if($first) {
				$authorsCheckboxes .= "<div class='tag-list-letter first embeded-tag-list-letter'>$letter</div>";
				$first = !$first;
			}
			else {
				$authorsCheckboxes .= "<div class='tag-list-letter embeded-tag-list-letter'>$letter</div>";
			}
		}
		$string = "<label for='author-$authorId'>
		<input type='checkbox' id='author-$authorId' name='authors[]' value='$author'/>
		<span>$authorName</span>
		</label><br>";
		$authorsCheckboxes .= $string;
	}
}

echo "
<div class='main'>
<div class='frame'>
<div class='container'>

<div class='frame-head'>
<p>Add</p>
<div class='underline active black'></div>
</div>

<div class='tab-div'>
	<p class='label tab-label'>Select table</p>
	<div class='tabs'>
		<div id='directories-tab-link' class='tab-link tab-link-active'>Directories</div>
		<div id='tags-tab-link' class='tab-link'>Tags</div>
		<div id='authors-tab-link' class='tab-link'>Authors</div>
	</div>
</div>

<form id='directories-form' method='POST' action='add.php'>
	<div>
	<span class='required'><i class='fa fa-asterisk'></i></span>
	<input class='textbox' type='text' name='dirName' placeholder='Directory name' required pattern='.*\S+.*' title='Should not be empty'>
	</div>
	<div>
	<span class='required'><i class='fa fa-asterisk'></i></span>
	<input class='textbox' type='text' name='dirCover' placeholder='Directory cover' required pattern='.*\S+.*' title='Should not be empty'>
	</div>
	<div class='checkboxes-container'>
		<p class='label'>Add authors</p>
		<div class='checkboxes'>
		$authorsCheckboxes
		</div>
	</div>
	<div class='checkboxes-container last'>
		<p class='label'>Add tags</p>
		<div class='checkboxes'>
		$tagsCheckboxes
		</div>
	</div>
	<button name='directorySubmit' type='submit'>Add directory</button>
</form>
<form id='tags-form' method='POST' action='add.php'>
	<div>
	<span class='required'><i class='fa fa-asterisk'></i></span>
	<input class='textbox last' type='text' name='tag' placeholder='Tag name' required pattern='.*\S+.*' title='Should not be empty'>
	</div>
	<button name='tagSubmit' type='submit'>Add tag</button>
</form>
<form id='authors-form' method='POST' action='add.php'>
	<div>
	<span class='required'><i class='fa fa-asterisk'></i></span>
	<input class='textbox last' type='text' name='author' placeholder='Author name' required pattern='.*\S+.*' title='Should not be empty'>
	</div>
	<button name='authorSubmit' type='submit'>Add author</button>
</form>
</div>
</div>
</div>
";
$connection->close();
