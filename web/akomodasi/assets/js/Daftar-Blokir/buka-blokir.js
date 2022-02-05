$("#btn-setuju-buka-blokir").click(async () => {
  startLoading();
  await BukaBlokir();
});

const BukaBlokir = async () => {
  const XAT = `Bearer ${localStorage.getItem("access_token")}`;
  const fd = new FormData();
  const id = $("#data-id").val();
  fd.append("XAT", XAT);
  fd.append("id", id);

  const req = await fetch("https://sipanduberadat.com/api/tamu/unblock/", {
    method: "POST",
    body: fd,
  });

  const { status_code, data, message } = await req.json();

  if (status_code === 200) {
    await readBlokir();
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
    refreshToken(deleteBanjar);
  }
};
