$("#form-tambah-admin").submit(async (e) => {
  e.preventDefault();
  startLoading()
  await tambahAdmin()
});

const tambahAdmin = async () => {
  const id_masyarakat = $("#tambah-admin").val();

  const fd = new FormData();
  fd.append("id_masyarakat", id_masyarakat);
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch(
    "https://api-sipandu-beradat.000webhostapp.com/admin-desa-adat/create/", {
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
    refreshToken(tambahAdmin)
  }
};
