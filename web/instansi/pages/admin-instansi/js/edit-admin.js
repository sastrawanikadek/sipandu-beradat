$("#form-edit-admin").submit(async (e) => {
  e.preventDefault();
  startLoading();
  await updateAdmin();
});

const updateAdmin = async () => {
  startLoading();
  const id = $("#edit-id").val();
  const id_petugas = $("#edit-id-petugas").val();
  const status_aktif = $("#edit-active-status").val();

  const fd = new FormData();
  fd.append("id", id);
  fd.append("id_petugas", id_petugas);
  fd.append("active_status", JSON.parse(status_aktif));
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch(
    "https://sipanduberadat.com/api/admin-instansi/update/",
    {
      method: "POST",
      body: fd,
    }
  );
  const { status_code, message } = await req.json();

  if (status_code === 200) {
    await readAdmin();
    stopLoading();
    Swal.fire({
      title: "Proses berhasil",
      text: message,
      icon: "success",
      confirmButtonText: "Tutup",
    });
  } else if (status_code === 400) {
    stopLoading();
    Swal.fire({
      title: "Terjadi Kesalahan",
      text: message,
      icon: "warning",
      confirmButtonText: "Tutup",
    });
  } else if (status_code === 401) {
    refreshToken(updateAdmin);
  }
};
