$(document).ready(() => {
  const v_name = getParameterByName("view-name");
  const v_avatar = getParameterByName("view-avatar");
  const v_avatar_cover = getParameterByName("view-avatar-cover");
  const v_emergency_status = getParameterByName("view-kategori-pelaporan");
  const v_pelapor = getParameterByName("view-pelapor");
  const v_status = getParameterByName("view-status");
  const v_time = new Date(getParameterByName("view-time"));

  const title = getParameterByName("title");
  const name = getParameterByName("name");
  const jenis_pelaporan = getParameterByName("jenis-pelaporan");
  const phone = getParameterByName("phone");
  const gender = getParameterByName("gender");
  const description = getParameterByName("description");
  const photo = getParameterByName("photo");
  const desa_adat = getParameterByName("desa-adat");
  const kecamatan = getParameterByName("kecamatan");
  const kabupaten = getParameterByName("kabupaten");


  if (Number(v_emergency_status) === 1) {
    $("#form-title").hide();
    $("#form-description").hide();
    $("#form-photo").hide();
  }

  $("#view-header").html(v_emergency_status === "0" ? "Keluhan" : "Darurat");
  $("#view-name").html(v_name);
  $("#view-avatar").attr("src", v_avatar);
  $("#view-avatar-cover").attr("src", v_avatar_cover);
  $("#view-kategori-pelaporan").html(v_emergency_status === "0" ? "Keluhan" : "Darurat");
  $("#view-pelapor").html(v_pelapor);
  $("#view-status").html(v_status === "0" ? '<i class="fas in fa-circle fa-xs text-secondary mr-2"></i><span>Menunggu Validasi</span>' :
    v_status === "1" ? '<i class="fas in fa-circle fa-xs text-primary mr-2"></i><span>Sedang Diproses</span>' :
    v_status === "-1" ? '<i class="fas in fa-circle fa-xs text-danger mr-2"></i><span>Tidak Valid</span>' :
    '<i class="fas in fa-circle fa-xs text-success mr-2"></i><span>Selesai</span>');
  $("#view-time").html(`${v_time.getFullYear()}-${(v_time.getMonth() + 1).toString().padStart(2, "0")}-${v_time.getDate().toString().padStart(2, "0")}`);

  //form
  $("#detail-title").val(title);
  $("#detail-nama").val(name);
  $("#detail-jenis-pelaporan").val(jenis_pelaporan);
  $("#detail-description").val(description);
  $("#detail-phone").val(phone);
  $("#detail-gender").val(gender);
  $("#detail-photo").attr("src", photo);
  $("#detail-desa-adat").val(desa_adat);
  $("#detail-kecamatan").val(kecamatan);
  $("#detail-kabupaten").val(kabupaten);
});
