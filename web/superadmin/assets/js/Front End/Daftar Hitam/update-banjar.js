$("#form-edit-banjar").submit(async (e) => {
  e.preventDefault();
  startLoading()
  await updateBanjar()
});

const updateBanjar = async () => {
  const id = $("#edit-id").val();
  const id_desa = $("#edit-id-desa").val();
  const name = $("#edit-name").val();
  const active_status = $("#edit-active-status").val();

  const fd = new FormData();
  fd.append("id", id);
  fd.append("id_desa", id_desa);
  fd.append("name", name);
  fd.append("active_status", JSON.parse(active_status));
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch(
    "https://api-sipandu-beradat.000webhostapp.com/banjar/update/", {
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
    await read_banjar();
    stopLoading()
    Swal.fire({
      title: "Proses berhasil",
      text: message,
      icon: "success",
      confirmButtonText: "Tutup"
    })
  } else if (status_code === 400) {
    stopLoading()
    Swal.fire({
      title: "Terjadi Kesalahan",
      text: message,
      icon: "warning",
      confirmButtonText: "Tutup"
    })
  } else if (status_code === 401) {
    refreshToken(updateBanjar)
  }
};
