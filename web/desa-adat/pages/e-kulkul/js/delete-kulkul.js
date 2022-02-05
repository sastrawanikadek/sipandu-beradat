$("#btn-hapus-kulkul").click(async () => {
  startLoading();
  await deleteKulkul();
});

const deleteKulkul = async () => {
  const XAT = `Bearer ${localStorage.getItem("access_token")}`;
  const fd = new FormData();
  const id = $("#hapus-id").val();
  fd.append("id", id);
  fd.append("XAT", XAT);

  const req = await fetch(
    "https://sipanduberadat.com/api/sirine-desa/delete/",
    {
      method: "POST",
      body: fd,
    }
  );

  const { status_code, message } = await req.json();

  if (status_code === 200) {
    await readKulkul();
    stopLoading();
    Swal.fire({
      title: "Berhasil!",
      text: message,
      icon: "success",
      confirmButtonText: "Tutup",
    });
  } else if (status_code === 400) {
    stopLoading();
    Swal.fire({
      title: "Terjadi Kesalahan!",
      text: message,
      icon: "error",
      confirmButtonText: "Tutup",
    });
  } else if (status_code === 401) {
    refreshToken(deleteKulkul);
  }
};
