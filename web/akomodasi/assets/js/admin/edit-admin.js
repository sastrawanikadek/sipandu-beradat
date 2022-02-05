$("#form-edit-admin").submit(async (e) => {
  e.preventDefault();
  startLoading();
  await updateAdmin();
});

const updateAdmin = async () => {
  const id = $("#edit-id").val();
  const id_pegawai = $("#edit-id-pegawai").val();
  const active_status = $("#edit-active-status").val();
  const email = $("#edit-email").val();

  const fd = new FormData();
  fd.append("id", id);
  fd.append("id_pegawai", id_pegawai);
  fd.append("active_status", JSON.parse(active_status));
  fd.append("email", email);
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch(
    "https://sipanduberadat.com/api/admin-akomodasi/update/",
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
