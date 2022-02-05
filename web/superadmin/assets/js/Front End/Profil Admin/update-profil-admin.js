$("#form-edit-admin").submit(async (e) => {
  e.preventDefault();
  startLoading()
  await updateAdmin()
});

const updateAdmin = async () => {
  const id = $("#edit-id").val();
  const id_banjar = $("#edit-id-banjar").val();
  const nama = $("#edit-nama").val();
  const avatar = $("#edit-avatar").prop("files");
  const no_telp = $("#edit-telp").val();
  const nik = $("#edit-nik").val();
  const jenis_kelamin = $("#edit-jenis-kelamin").val();
  const status_valid = $("#edit-status-valid").val();
  const status_aktif = $("#edit-status-aktif").val();
  const tanggal_lahir = $("#edit-tanggal-lahir").val();
  const jenis_krama = $("#edit-jenis-krama").val();

  const fd = new FormData();
  fd.append("id", id);
  fd.append("id_banjar", id_banjar);
  fd.append("name", nama);
  if (avatar.length > 0) {
    fd.append("avatar", avatar[0]);
  }
  fd.append("phone", no_telp);
  fd.append("nik", nik);
  fd.append("gender", jenis_kelamin);
  fd.append("valid_status", JSON.parse(status_valid));
  fd.append("active_status", JSON.parse(status_aktif));
  fd.append("date_of_birth", tanggal_lahir);
  fd.append("category", JSON.parse(jenis_krama));
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch(
    "https://api-sipandu-beradat.000webhostapp.com/masyarakat/update/", {
      method: "POST",
      body: fd,
    }
  );
  const {
    status_code,
    message
  } = await req.json();

  if (status_code === 200) {
    await readAdmin();
    stopLoading();
    Swal.fire({
      title: "Proses berhasil",
      text: message,
      icon: "success",
      confirmButtonText: "Tutup"
    })
  } else if (status_code === 400) {
    stopLoading();
    Swal.fire({
      title: "Terjadi Kesalahan",
      text: message,
      icon: "warning",
      confirmButtonText: "Tutup"
    })
  } else if (status_code === 401) {
    refreshToken(updateAdmin)
  }
};
