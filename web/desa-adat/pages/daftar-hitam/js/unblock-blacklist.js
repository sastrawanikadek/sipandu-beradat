$("#btn-unblock").click(async () => {
  startLoading();
  await Unblock();
});

const Unblock = async () => {
  const XAT = `Bearer ${localStorage.getItem("access_token")}`;
  const fd = new FormData();
  const id = $("#block-id").val();
  fd.append("id", id);
  fd.append("XAT", XAT);

  const req = await fetch(
    "https://sipanduberadat.com/api/masyarakat/unblock/",
    {
      method: "POST",
      body: fd,
    }
  );

  const { status_code, message } = await req.json();

  if (status_code === 200) {
    await readBlacklist();
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
    refreshToken(Unblock);
  }
};
