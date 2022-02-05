$(document).ready(() => {
  const title = getParameterByName("title");
  const name = getParameterByName("name");
  const active_status = getParameterByName("active-status");
  const akomodasi = getParameterByName("akomodasi");
  const nik = getParameterByName("nik");
  const gender = getParameterByName("gender");
  const phone = getParameterByName("phone");
  const date_of_birth = getParameterByName("date-of-birth");
  const avatar = getParameterByName("avatar");

  $("#view-avatar").attr("src", avatar);
  $("#view-avatar-cover").attr("src", avatar);
  $("#view-name").text(name);
  $("#view-active-status").html(active_status === "true" ? '<div class="badge badge-pill badge-success mr-2">Aktif</div>' : '<div class="badge badge-pill badge-primary-red mr-2">Tidak Aktif</div>');
  $("#view-akomodasi").text(akomodasi);

  $("#detail-title").val(title);
  $("#detail-name").val(name);
  $("#detail-nik").val(nik);
  $("#detail-gender").val(gender);
	$("#detail-phone").val(phone);
  $("#detail-date-of-birth").val(date_of_birth);

});
