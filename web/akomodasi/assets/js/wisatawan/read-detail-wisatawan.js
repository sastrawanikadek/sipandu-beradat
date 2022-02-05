$(document).ready(() => {
  const title = getParameterByName("title");
  const name = getParameterByName("name");
  const email = getParameterByName("email");
  const identity_type = getParameterByName("identity-type");
  const identity_number = getParameterByName("identity-number");
  const negara = getParameterByName("negara");
  const gender = getParameterByName("gender");
  const phone = getParameterByName("phone");
  const date_of_birth = getParameterByName("date-of-birth");
  const check_in = getParameterByName("check-in");
  const check_out = getParameterByName("check-out");
  const block_status = getParameterByName("block-status");
  const active_status = getParameterByName("active-status");
  const avatar = getParameterByName("avatar");

  $("#view-avatar").attr("src", avatar);
  $("#view-avatar-cover").attr("src", avatar);
  $("#view-name").text(name);
  $("#view-negara").text(negara);
	$("#view-block-status").html(block_status === "true" ? '<div class="badge badge-pill badge-danger mr-2">Diblokir</div>' : '<div class="badge badge-pill badge-success mr-2">Tidak Diblokir</div>');
  $("#view-active-status").html(active_status === "true" ? '<div class="badge badge-pill badge-success mr-2">Aktif</div>' : '<div class="badge badge-pill badge-primary-red mr-2">Tidak Aktif</div>');
  $("#detail-title").val(title);
  $("#detail-name").val(name);
  $("#detail-email").val(email);
  $("#detail-identity-type").val(identity_type);
  $("#detail-identity-number").val(identity_number);
  $("#detail-negara").val(negara);
  $("#detail-date-of-birth").val(date_of_birth);
  $("#detail-check-in").val(check_in);
  $("#detail-check-out").val(check_out);
  $("#detail-gender").val(gender);
	$("#detail-phone").val(phone);

});
