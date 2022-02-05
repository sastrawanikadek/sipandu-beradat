$("#form-tambah-banjar").submit(async (e) => {
  e.preventDefault();
  startLoading()
  await tambahBanjar()
});

const tambahBanjar = async () => {
  const id_desa = localStorage.getItem("id_desa")
  const name = $("#tambah-name").val();

  const fd = new FormData();
  fd.append("id_desa", id_desa);
  fd.append("name", name);
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch(
    "https://api-sipandu-beradat.000webhostapp.com/banjar/create/", {
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
    await read_banjar()
    stopLoading()
    Swal.fire({
      title: "Berhasil!",
      text: message,
      icon: "success",
      confirmButtonText: "Tutup"
    })
  } else if (status_code === 400) {
    stopLoading()
    Swal.fire({
      title: "Terjadi Kesalahan!",
      text: message,
      icon: "error",
      confirmButtonText: "Tutup"
    })
  } else if (status_code === 401) {
    refreshToken(tambahBanjar)
  }
};
