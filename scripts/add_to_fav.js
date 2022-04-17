$(document).ready(function() {
	$('#message').click(function() {
		$('#message').fadeOut();
	});
	$('a.favourite').click(function() {
		var dirId = $(this).attr('id');
		match = dirId.match(/\d/g);
		dirId = match.join("");
		$.ajax({
			url: "/scripts/add_to_fav.php",
			type: "POST",
			data: {dirId: dirId},
			success: function(php_output) {
				var id = '#'+dirId+'-fav';
				var classes = $(id).attr('class');
				if(classes.indexOf(" yes") >= 0) {
					$(id).removeClass("yes");
					$(id).addClass("no");
				}
				else if(classes.indexOf(" no") >= 0) {
					$(id).removeClass("no");
					$(id).addClass("yes");
				}
				// Remove the plain text message first
				$('#message').html($('#message').children());
				$('#message').addClass('msg-success').addClass('row-flex');
				$('#message-close').before(php_output);
				$('#message').fadeIn();
			},
			error: function(request) {
				// Remove the plain text message (if already present) first
				$('#message').html($('#message').children());
				$('#message').addClass('msg-error').addClass('row-flex');
				$('#message-close').before(request.responseText);
				$('#message').fadeIn();
			}
		});
	});
	$('a.bookmark').click(function() {
		var dirId = $(this).attr('id');
		digits = dirId.match(/\d/g);
		dirId = digits.join("");
		var bookmark = window.location.href;
		var last = bookmark.length-1;
		var lastSlash = bookmark.lastIndexOf('/');
		console.log(bookmark);
		// If there is a slash in the string and it is not the last character
		if(lastSlash >= 0 && lastSlash !== last) {
			bookmark = bookmark.substring(lastSlash+1);
		}
		console.log(bookmark);
		$.ajax({
			url: "/scripts/bookmark.php",
			type: "POST",
			data: {dirId: dirId, bookmark: bookmark},
			success: function(php_output) {
				var id = '#'+dirId+'-bmk';
				var classes = $(id).attr('class');
				if(classes.indexOf(" bmk-yes") >= 0) {
					$(id).removeClass("bmk-yes");
					$(id).addClass("bmk-no");
				}
				else if(classes.indexOf(" bmk-no") >= 0) {
					$(id).removeClass("bmk-no");
					$(id).addClass("bmk-yes");
				}
				// Remove the plain text message first
				$('#message').html($('#message').children());
				$('#message').addClass('msg-success').addClass('row-flex');
				$('#message-close').before(php_output);
				$('#message').fadeIn();
			},
			error: function(request) {
				// Remove the plain text message (if already present) first
				$('#message').html($('#message').children());
				$('#message').addClass('msg-error').addClass('row-flex');
				// $('#message-close').before('Could not add/remove/update bookmark');
				$('#message-close').before(request.responseText);
				$('#message').fadeIn();
			}
		});
	});
});