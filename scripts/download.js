var interval;
function reloadImg() {
	var src = $('#cover').attr('src');
	var date = new Date();
	var src = src+'?dt='+date.getTime();
	$('#cover').attr('src', src);
}
function checkProgress() {
	$("#progress").show();
	var progress = $('.progress').attr('id');
	if(progress == null) {
		progress = "0";
	}
	progress = Number(progress);
	if(progress<1) {
		$('#progress').load('/scripts/progress.php');
	}
	else {
		clearInterval(interval);
		$('#progress-in-words').text("Done!");
	}
}
$(document).ready(function() {
	$('#preview').click(function() {
		$('#loading').show();
		$('#output').html("");
		$('#progress').html("");
		var id = $('#id').val();
		var data = {id: id};
		if($('#grpauth').is(':checked')) {
			data = {id: id, grpauth: "include"};
		}
		$.ajax({
			url: "/scripts/preview.php",
			type: "POST",
			data: data,
			success: function(php_output) {
				$('#loading').hide();
				$('#output').html(php_output);
				reloadImg();
			},
			error: function() {
				$('#loading').hide();
			}
		});
	});
	$('#download').click(function() {
		$('#loading').show();
		$('#output').html("");
		var id = $('#id').val();
		var name = $('#name').val();
		var data = {id: id, name: name};
		if($('#grpauth').is(':checked')) {
			data = {id: id, name: name, grpauth: "include"};
		}
		$.ajax({
			url: "/scripts/download.php",
			type: "POST",
			data: data,
			cache: false,
			success: function(php_output) {
				$('#loading').hide();
				$('#output').html(php_output);
				interval = setInterval(checkProgress, 2000);
				$('#progress').html("");
				checkProgress();
			},
			error: function() { 
				$('#loading').hide();
			}
		});
	});
});