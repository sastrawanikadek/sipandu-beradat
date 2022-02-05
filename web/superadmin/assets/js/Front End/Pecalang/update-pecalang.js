$("#form-edit-pecalang").submit(async (e) => {
  e.preventDefault();
  startLoading()
  await updatePecalang()
});

const updatePecalang = async () => {
  const id = $("#edit-id").val();
  const id_masyarakat = $("#edit-id-masyarakat").val();
  const sirine_authority = $("#edit-otoritas-sirine").val() === "checked";
  const active_status = $("#edit-status-aktif").val();

  const fd = new FormData();
  fd.append("id", id);
  fd.append("id_masyarakat", id_masyarakat);
  fd.append("sirine_authority", sirine_authority);
  fd.append("active_status", JSON.parse(active_status));
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch(
    "https://api-sipandu-beradat.000webhostapp.com/pecalang/update/", {
      method: "POST",
      body: fd,
    }
  );
  const {
    status_code,
    data,
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
    refreshToken(updatePecalang)
  }
};
