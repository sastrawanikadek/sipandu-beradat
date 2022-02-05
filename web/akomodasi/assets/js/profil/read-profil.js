$(document).ready(async () => {
  readProfil();
});

const readProfil = async () => {
  const fd = new FormData();
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);
  const req = await fetch(
    "https://sipanduberadat.com/api/admin-akomodasi/me/",
    {
      method: "POST",
      body: fd,
    }
  );
  const { status_code, message, data } = await req.json();

  if (status_code === 200) {
    $("#view-avatar-cover").attr("src", data.pegawai.avatar);
    $("#view-avatar").attr("src", data.pegawai.avatar);
    $("#view-name").html(data.pegawai.name);
    $("#view-active-status").html(
      data.pegawai.active_status
        ? '<div class="badge badge-pill badge-success mr-2">Aktif</div>'
        : '<div class="badge badge-pill badge-primary-red mr-2">Tidak Aktif</div>'
    );
    $("#view-akomodasi").html(data.pegawai.akomodasi.name);

    $("#profil-name").val(data.pegawai.name);
    $("#profil-nik").val(data.pegawai.nik);
    $("#profil-gender").val(
      data.pegawai.gender === "l" ? "Laki-Laki" : "Perempuan  "
    );
    $("#profil-phone").val(data.pegawai.phone);
    $("#profil-date-of-birth").val(data.pegawai.date_of_birth);
  } else if (status_code === 401) {
    refreshToken(readProfil);
  }
};
