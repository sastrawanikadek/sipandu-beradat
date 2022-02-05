$("#form-edit-jadwal").submit(async (e) => {
  e.preventDefault();
  startLoading()
  await updateJadwal()
});

const updateJadwal = async () => {
  const id_pecalang = $("#edit-id-pecalang").val();
  const days = $("#edit-otoritas-sirine").val() === "checked";
  const active_status = $("#edit-status-aktif").val();

  const fd = new FormData();
  fd.append("id_pecalang", id_pecalang);
  fd.append("days", days);
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch(
    "https://api-sipandu-beradat.000webhostapp.com/jadwal-pecalang/update/", {
      method: "POST",
      body: fd,
    }
  );
  const {
    status_code,
    message
  } = await req.json();

  if (status_code === 200) {
    await read_pecalang();
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
    refreshToken(updateJadwal)
  }
};
