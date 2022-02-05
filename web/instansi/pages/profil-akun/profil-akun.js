$(document).ready(async () => {
  readProfil();
});

const readProfil = async () => {
  const fd = new FormData();
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);
  const req = await fetch("https://sipanduberadat.com/api/admin-instansi/me/", {
    method: "POST",
    body: fd,
  });
  const { status_code, message, data } = await req.json();

  if (status_code === 200) {
    $("#view-cover").attr("src", data.petugas.avatar);
    $("#view-avatar").attr("src", data.petugas.avatar);
    $("#view-name").html(data.petugas.name);
    $("#view-instansi").html(data.petugas.instansi_petugas.name);
    $("#view-active-status").html(
      Number(data.active_status.name) === 1 ? "Aktif" : "Tidak Aktif"
    );

    $("#admin-name").val(data.petugas.name);
    $("#nik-admin").val(data.petugas.nik);
    $("#gender-admin").val(
      data.petugas.gender === "l" ? "Laki-laki" : "Perempuan"
    );
    $("#phone-admin").val(data.petugas.phone);
    $("#birth-admin").val(data.petugas.date_of_birth);

    $("#kecamatan-admin").val(data.petugas.instansi_petugas.kecamatan.name);
    $("#kabupaten-admin").val(
      data.petugas.instansi_petugas.kecamatan.kabupaten.name
    );
    $("#provinsi-admin").val(
      data.petugas.instansi_petugas.kecamatan.kabupaten.provinsi.name
    );
  } else if (status_code === 401) {
    refreshToken(readProfil);
  }
};
