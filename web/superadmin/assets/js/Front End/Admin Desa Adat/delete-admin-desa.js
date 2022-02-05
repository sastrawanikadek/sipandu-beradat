$("#btn-hapus-admin").click(async () => {
  startLoading()
  await hapusAdmin()
});

const hapusAdmin = async () => {
  const XAT = `Bearer ${localStorage.getItem("access_token")}`;
  const fd = new FormData();
  const id = $("#hapus-id").val();

  fd.append("id", id);
  fd.append("XAT", XAT);

  const req = await fetch(
    "https://api-sipandu-beradat.000webhostapp.com/admin-desa-adat/delete/", {
      method: "POST",
      body: fd,
    }
  );

  const {
    status_code,
    message
  } = await req.json();

  if (status_code === 200) {
    await read_admin();
    stopLoading();
    Swal.fire({
      title: "Berhasil!",
      text: message,
      icon: "success",
      confirmButtonText: "Tutup"
    })
  } else if (status_code === 400) {
    stopLoading();
    Swal.fire({
      title: "Terjadi Kesalahan!",
      text: message,
      icon: "error",
      confirmButtonText: "Tutup"
    })
  } else if (status_code === 401) {
    refreshToken(hapusAdmin)
  }
};
