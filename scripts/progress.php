<?php
$file = "../tmp/progress";
if(!is_file($file)) die();

$progress = file_get_contents($file);
$progress = trim($progress);
$space = strpos($progress, " ");
// Number of pages downloaded
$done = substr($progress, 0, $space);
// Number of total pages
$total = substr($progress, $space+1);

$totalWidth = 300;
$progress = abs($done/$total);
$id = $progress;
$progress = $progress * $totalWidth;

$bar = <<<EOF
<div class="progress" id="$id">
	<div class="progress-bar">
		<div class="progress-fill" style="width: ${progress}px;"></div>
		<p class="progress-in-words" id="progress-in-words">$done/$total</p>
	</div>
</div>
EOF;

echo $bar;
