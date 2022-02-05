$("#btn-hapus-kulkul").click(async () => {
  await deleteKulkul()
  startLoading()
});

const deleteKulkul = async () => {
  const XAT = `Bearer ${localStorage.getItem("access_token")}`;
  const fd = new FormData();
  const id = $("#hapus-id").val();
  fd.append("XAT", XAT);
  fd.append("id", id);

  const req = await fetch(
    "https://api-sipandu-beradat.000webhostapp.com/sirine-desa/delete/", {
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
    await read_kulkul();
    stopLoading();
    Swal.fire({
      title: "Berhasil!",
      text: message,
      icon: "success",
      confirmButtonText: "Tutup"
    })
  } else if (status_code === 400) {
    Swal.fire({
      title: "Terjadi Kesalahan!",
      text: message,
      icon: "error",
      confirmButtonText: "Tutup"
    })
  } else if (status_code === 401) {
    refreshToken(deleteKulkul)
  }
};
