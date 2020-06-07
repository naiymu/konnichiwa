$(document).ready(function() {
	$('#directories-tab-link').click(function() {
		$('.tab-link-active').removeClass('tab-link-active');
		$(this).addClass('tab-link-active');
		$('#tags-form').hide();
		$('#authors-form').hide();
		$('#directories-form').show();
	});
	$('#tags-tab-link').click(function() {
		$('.tab-link-active').removeClass('tab-link-active');
		$(this).addClass('tab-link-active');
		$('#directories-form').hide();
		$('#authors-form').hide();
		$('#tags-form').show();
	});
	$('#authors-tab-link').click(function() {
		$('.tab-link-active').removeClass('tab-link-active');
		$(this).addClass('tab-link-active');
		$('#tags-form').hide();
		$('#directories-form').hide();
		$('#authors-form').show();
	});
});