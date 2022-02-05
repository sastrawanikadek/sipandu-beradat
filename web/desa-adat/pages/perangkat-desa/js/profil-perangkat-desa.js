$(document).ready(() => {
	const name = getParameterByName("name");
	const avatar = getParameterByName("avatar");
	const sirine_authority = getParameterByName("sirine-authority");
	const working_status = getParameterByName("working-status");
	const active_status = getParameterByName("active-status");
	const prajuru_status = getParameterByName("prajuru-status");

	const category = getParameterByName("category");
	const gender = getParameterByName("gender");
	const nik = getParameterByName("nik");
	const phone = getParameterByName("phone");
	const birth = getParameterByName("birth");
	const banjar = getParameterByName("banjar");

	$("#name").html(name);
	$("#avatar").attr("src", avatar);
	$("#cover").attr("src", avatar);
	$("#category").html(category);
	$("#prajuru-status").html(prajuru_status === "true" ? "Prajuru" : "Pecalang");
	$("#gender").html(gender === "l" ? "Laki-laki" : "Perempuan");
	$("#nik").html(nik);
	$("#phone").html(phone);
	$("#banjar").html(banjar);
	$("#birth").html(birth);

	$("#sirine-authority").html(sirine_authority === "true" ? '<div class="badge badge-pill badge-primary mr-2">Aktif</div>' : '<div class="badge badge-pill badge-secondary mr-2">Tidak Aktif</div>');
	$("#working-status").html(working_status === "true" ? '<div class="badge badge-pill badge-info mr-2">Bekerja</div>' : '<div class="badge badge-pill badge-secondary mr-2">Tidak Bekerja</div>');
	$("#active-status").html(active_status === "true" ? '<div class="badge badge-pill badge-success mr-2">Aktif</div>' : '<div class="badge badge-pill badge-secondary mr-2">Tidak Aktif</div>');
});

$('.modal-img').on('click', function () {
	const img_url = $(this).attr('src');
	$(".show-img").attr('src', img_url);
});