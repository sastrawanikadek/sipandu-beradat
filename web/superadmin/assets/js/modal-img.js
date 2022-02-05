$('.modal-img').on('click', function () {
	$("#modal-show-img").modal('show');
	const img_url = $(this).attr('src');
	$(".show-img").attr('src', img_url);
	$('body').css('overflow', 'hidden');
});