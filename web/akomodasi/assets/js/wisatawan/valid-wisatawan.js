$("#btn-valid-wisatawan").click(async () => {
  startLoading();
  await validWisatawan();
});

const validWisatawan = async () => {
  const XAT = `Bearer ${localStorage.getItem("access_token")}`;
  const fd = new FormData();
  const id = $("#valid-id").val();
  fd.append("id", id);
  fd.append("XAT", XAT);

  const req = await fetch("https://sipanduberadat.com/api/tamu/validate/", {
    method: "POST",
    body: fd,
  });

  const { status_code, message } = await req.json();

  if (status_code === 200) {
    await read_tamu();
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
    refreshToken(validWisatawan);
  }
};
