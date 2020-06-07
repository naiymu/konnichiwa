<?php
function getErrorString($error, $errno) {
	if($errno !== 0) {
		$error = <<<EOF
		<div class="error lone">
		<p class="apology">$errno - Yurushite Kudasai!</p>
		<p class="err-msg">
			$error
		</p>
		</div>
EOF;
	}
	else {
		$error = <<<EOF
		<div class="error">
		<p class="err-msg">
			$error
		</p>
		</div>
EOF;
	}
	return $error;
}
