$(document).ready(() => {
  const name = getParameterByName("view-name");
  const avatar = getParameterByName("view-avatar");
  const valid_status = getParameterByName("valid-status");
  const active_status = getParameterByName("view-active-status");
  const block_status = getParameterByName("block-status");

  const category = getParameterByName("category");
  const gender = getParameterByName("gender");
  const nik = getParameterByName("nik");
  const phone = getParameterByName("phone");
  const birth = getParameterByName("birth");
  const kecamatan = getParameterByName("kecamatan");

  $("#name").html(name);
  $("#avatar").attr("src", avatar);
  $("#cover").attr("src", avatar);
  $("#category").html(category);
  $("#gender").html(gender);
  $("#nik").html(nik);
  $("#phone").html(phone);
  $("#kecamatan").html(kecamatan);
  $("#birth").html(birth);

  $("#valid-status").html(
    valid_status === "true"
      ? '<div class="badge badge-pill badge-success mr-2">Valid</div>'
      : '<div class="badge badge-pill badge-secondary mr-2">Tidak Valid</div>'
  );
  $("#active-status").html(
    active_status === "true"
      ? '<div class="badge badge-pill badge-success mr-2">Aktif</div>'
      : '<div class="badge badge-pill badge-secondary mr-2">Nonaktif</div>'
  );
  $("#block-status").html(
    block_status === "true"
      ? '<div class="badge badge-pill badge-danger mr-2">Diblokir</div>'
      : '<div class="badge badge-pill badge-secondary mr-2">Tidak Diblokir</div>'
  );
});

$(".modal-img").on("click", function () {
  const img_url = $(this).attr("src");
  $(".show-img").attr("src", img_url);
});
