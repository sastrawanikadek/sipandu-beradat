$("#btn-hapus-petugas").click(async () => {
  startLoading();
  await deletePetugas();
});

const deletePetugas = async () => {
  const XAT = `Bearer ${localStorage.getItem("access_token")}`;
  const fd = new FormData();
  const id = $("#hapus-id").val();
  fd.append("XAT", XAT);
  fd.append("id", id);

  const req = await fetch("https://sipanduberadat.com/api/petugas/delete/", {
    method: "POST",
    body: fd,
  });

  const { status_code, data, message } = await req.json();

  if (status_code === 200) {
    await readPetugas();
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
    refreshToken(deletePetugas);
  }
};
