<?php
function addToDB($dirName, $dirCover, $authors = null, $tags = null) {
	include("login.php");

	$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
	if($connection->connect_errno) die("<p>" . $connection->connect_error . "</p>");

	$stmnt = $connection->prepare("INSERT INTO directories (dirName, dirCover) VALUES (?, ?)");
	$stmnt->bind_param("ss", $dirName, $dirCover);
	if(!$stmnt->execute()) {
		$error = $stmnt->error;
		$errorNo = $connection->errno;
		$stmnt->close();
		$connection->close();
		// NOT false but still can be differentiated by === true
		return array(
			'error' => $error,
			'errorNo' => $errorNo
		);
	}

	$sql = "SELECT dirId FROM directories WHERE dirId=(SELECT MAX(dirId) FROM directories)";
	$result = $connection->query($sql);
	$row = $result->fetch_assoc();
	// Get the id of the just added directory
	$dirId = $row['dirId'];
	$result->close();

	if($authors !== null) {
		foreach ($authors as $author) {
			$sql = "SELECT authorId FROM authors WHERE authorId=(SELECT MAX(authorId) FROM authors)";
			$result = $connection->query($sql);
			$row = $result->fetch_assoc();
			$authorId = $row['authorId']+1;
			$result->close();
			$authorQuery = $connection->query("SELECT authorId FROM authors WHERE author='$author'");
			if($authorQuery->num_rows > 0) {
				$authorRow = $authorQuery->fetch_assoc();
				$authorId = $authorRow['authorId'];
			}
			else {
				$stmnt = $connection->prepare("INSERT INTO authors (author) VALUES (?)");
				$stmnt->bind_param("s", $author);
				if(!$stmnt->execute()) {
					$stmnt->close();
					continue;
				}
				$stmnt->close();
			}
			$stmnt = $connection->prepare("INSERT INTO dir_author_link (dirId, authorId) VALUES (?, ?)");
			$stmnt->bind_param("ii", $dirId, $authorId);
			$stmnt->execute();
			$stmnt->close();
		}
	}


	if($tags !== null) {
		foreach ($tags as $tag) {
			$sql = "SELECT tagId FROM tags WHERE tagId=(SELECT MAX(tagId) FROM tags)";
			$result = $connection->query($sql);
			$row = $result->fetch_assoc();
			$tagId = $row['tagId']+1;
			$result->close();
			$tagQuery = $connection->query("SELECT tagId FROM tags WHERE tag='$tag'");
			if($tagQuery->num_rows > 0) {
				$tagRow = $tagQuery->fetch_assoc();
				$tagId = $tagRow['tagId'];
			}
			else {
				$stmnt = $connection->prepare("INSERT INTO tags (tag) VALUES (?)");
				$stmnt->bind_param("s", $tag);
				if(!$stmnt->execute()) {
					$stmnt->close();
					continue;
				}
				$stmnt->close();
			}
			$stmnt = $connection->prepare("INSERT INTO dir_tag_link (dirId, tagId) VALUES (?, ?)");
			$stmnt->bind_param("ii", $dirId, $tagId);
			$stmnt->execute();
			$stmnt->close();
		}
	}

	// If everything went fine return true (even if it didn't with the tags and authors)
	return true;
}
