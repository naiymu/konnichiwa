<?php
require_once("scripts/start.php");

startPage("download", "download");
$listBullet = "fa-chevron-circle-right";

$htmlPage = <<<EOF
<div class="main">
	<div class="frame">
		<div class="container">
			<div class='frame-head'>
				<p>Download</p>
				<div class='underline active black'></div>
			</div>
			<form id="form" class="column-flex">
				<input class="textbox" type="text" name="id" placeholder="Enter ID" id="id"><br>
				<input class="textbox" type="text" name="name" placeholder="Enter name (Optional)" id="name"><br>
				<div class="single-checkbox">
					<label for='grpauth'>
						<input type='checkbox' id='grpauth' name='grpauth' value='include' checked>
						<span>Include groups in authors</span>
					</label>
				</div>
			</form>
			<div class="buttons">
				<button id="preview"><i class='fa fa-eye'></i>Preview</button><button id="download"><i class='fa fa-download'></i>Download</button>
			</div>
			<div>
				<div id="loading" hidden="true">
					<div class="loader"></div>
					<p class="wait-pls">Matte kudasai! Onii-chan!</p>
				</div>
			</div>
			<div id="output"></div>
			<div id="progress" hidden="true"></div>
		</div>
	</div>
</div>
EOF;

echo $htmlPage;
