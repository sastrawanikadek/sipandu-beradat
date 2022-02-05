$(document).ready(() => {
  const v_name = getParameterByName("view-name");
  const v_avatar = getParameterByName("view-avatar");
  const v_valid_status = getParameterByName("view-valid-status");
  const v_active_status = getParameterByName("view-active-status");
  const v_blcok_status = getParameterByName("view-block-status");

  const jenis_krama = getParameterByName("jenis-krama");
  const gender = getParameterByName("gender");
  const nik = getParameterByName("nik");
  const phone = getParameterByName("phone");
  const birth = getParameterByName("birth");
  const banjar = getParameterByName("banjar");
  const desa_adat = getParameterByName("desa-adat");
  const kecamatan = getParameterByName("kecamatan");
  const kabupaten = getParameterByName("kabupaten");

  $("#view-name").html(v_name);
  $("#view-avatar").attr("src", v_avatar);
  $("#view-valid-status").html(v_valid_status === "true" ? '<div class="badge badge-success mr-2">Valid</div>' : '<div class="badge badge-secondary mr-2">Tidak Valid</div>');
  $("#view-active-status").html(v_active_status === "true" ? '<div class="badge badge-success mr-2">Aktif</div>' : '<div class="badge badge-secondary mr-2">Tidak Aktif</div>');
  $("#view-block-status").html(v_blcok_status === "true" ? '<div class="badge badge-danger mr-2">Diblokir</div>' : '<div class="badge badge-secondary mr-2">Tidak Diblokir</div>');

  $("#jenis-krama").val(jenis_krama);
  $("#gender").val(gender);
  $("#nik").val(nik);
  $("#phone").val(phone);
  $("#banjar").val(banjar);
  $("#birth").val(birth);
  $("#desa-adat").val(desa_adat);
  $("#kecamatan").val(kecamatan);
  $("#kabupaten").val(kabupaten);
});
