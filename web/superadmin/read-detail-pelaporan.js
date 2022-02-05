$(document).ready(() => {
  const title = getParameterByName("title");
  const name = getParameterByName("name");
  const category = getParameterByName("category");
  const gender = getParameterByName("gender");
  const phone = getParameterByName("phone");
  const avatar = getParameterByName("avatar");
  const jenis_pelaporan = getParameterByName("jenis-pelaporan");
  const description = getParameterByName("description");
  const photo = getParameterByName("photo");
  const desa_adat = getParameterByName("desa-adat");
  const kecamatan = getParameterByName("kecamatan");
  const kabupaten = getParameterByName("kabupaten");
  const status = getParameterByName("status");
  const time = getParameterByName("time");
  const emergency_status = getParameterByName("emergency-status");

  if (JSON.parse(emergency_status)) {
    $("#form-title").hide();
    $("#form-description").hide();
    $("#form-photo").hide();
  }

  $("#view-avatar").attr("src", avatar);
  $("#view-avatar-cover").attr("src", avatar);
  $("#view-name").text(name);
  $("#view-status").text(
    status === "0"
      ? "Menunggu Validasi"
      : status === "1"
      ? "Sedang Diproses"
      : status === "2"
      ? "Selesai"
      : "Tida Valid"
  );
  $("#view-pelapor").text(category);
  $("#view-kategori-pelaporan").text(
    JSON.parse(emergency_status) ? "Darurat" : "Keluhan"
  );
  $("#view-time").text(time);
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
