<?php
require_once("scripts/login.php");
require_once("scripts/error.php");
require_once("scripts/start.php");
require_once("scripts/directory.php");

startPage("bookmarks", "add_to_fav");

$connection = new mysqli($db_hostname, $db_username, $db_password, $db_database);
if($connection->connect_errno) {
	echo "<div class='main'>";
	$error = getErrorString("Could not connect to the database.", $connection->connect_errno);
	echo $error;
	echo "</div>";
	$connection->close();
	die();
}

// Get all directories whose dirId are present in the bookmarks table
$sql = "SELECT * FROM bookmarks";
$result = $connection->query($sql);
echo "
<script>
$(document).mousemove(function(e) {
	var xMousePos = e.pageX;
	var yMousePos = e.pageY;
	var scrolledHeight = $(document).scrollTop();
	$('.table-tooltip').css({
		left: xMousePos + 10,
		top: yMousePos + 10 - scrolledHeight
	});
	$('.table-dir-link').hover(function() {
		var sibling = $(this).next();
		sibling.removeClass('moveUp');
		sibling.css('display', 'flex');
		var height = sibling.height();
		var winHeight = $(window).height();
		// Get the new position of the element
		var position = sibling.position();
		var y = position.top;
		if((y+height) > winHeight) {
			sibling.addClass('moveUp');
		}
		else {
			sibling.removeClass('moveUp');
		}
	},
	function() {
		var sibling = $(this).next();
		sibling.css('display', 'none');
		sibling.removeClass('moveUp');
	});
});
</script>
";
echo "<div class='main'>";
echo "<div class='frame'>";
echo "<div class='container'>";
echo <<<EOF
<div class='frame-head'>
	<p>Bookmarks</p>
	<div class='underline active black'></div>
</div>
EOF;
if(!$result) {
	$error = getErrorString("Query unsuccessful", $connection->errno);
	$connection->close();
	die($error);
}

$rows = $result->num_rows;

echo "
<div class='table-container'>
<table>
<tr>
	<th>Directory</th>
	<th>Page number</th>
	<th>Actions</th>
</tr>
";

if($rows > 0) {
	while($row = $result->fetch_assoc()) {
		$dirId = $row['dirId'];
		$bookmark = $row['bookmark'];
		$urlData = parse_url($bookmark);
		parse_str($urlData['query'], $urlData);
		$pageNo = $urlData['page'];
		$dirStmnt = $connection->prepare("SELECT * FROM directories WHERE dirId=(?)");
		$dirStmnt->bind_param("i", $dirId);
		$dirStmnt->execute();
		$dirResult = $dirStmnt->get_result();
		$dirRow = $dirResult->fetch_assoc();
		$dirName = $dirRow['dirName'];
		$dirCover = $dirRow['dirCover'];
		$dirResult->close();
		$dirStmnt->close();
		$coverPath = $relative_directory . $dirName . "/" . htmlspecialchars($dirCover, ENT_QUOTES);
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
		$data = array(
			'dir' => $dirId
		);
		$url = "info.php?" . http_build_query($data);
		$actions = "
		<div class='actions'>
			<div class='actions'>
				<a id='$dirId-fav' class='favourite no table-fav'><i class='far fa-heart'></i><i class='fas fa-heart'></i></a>
				<a id='$dirId-bmk' class='bookmark bmk-yes table-bmk'><i class='far fa-bookmark'></i><i class='fas fa-bookmark'></i></a>
			</div>
		</div>";
		if($isFav) {
			$actions = str_replace("favourite no", "favourite yes", $actions);
		}
		$tooltip = "<div class='table-tooltip'><img src='$coverPath'></div>";
		if(!$dir_exists) {
			$tooltip = "";
		}
		$dirName = htmlspecialchars($dirName, ENT_QUOTES);
		$row = "<tr>
		<td>
			<div id='dir-column' class='dir-column'>
			<a class='table-dir-link' href='$url'>$dirName</a>
			$tooltip
			</div>
		</td>
		<td>$pageNo</td>
		<td>$actions</td>
		</tr>";
		echo $row;
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

echo "</table></div>";

// Close the frame and container div
echo "</div></div>";
// Close the main div
echo "</div>";

$result->close();
$connection->close();
