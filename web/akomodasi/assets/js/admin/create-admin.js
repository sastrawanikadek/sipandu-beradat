$("#form-tambah-admin").submit(async (e) => {
  e.preventDefault();
  startLoading();
  await tambahAdmin();
});

const tambahAdmin = async () => {
  const id_pegawai = $("#tambah-admin").val();
  const email = $("#tambah-email").val();
  const username = $("#tambah-username").val();
  const password = $("#tambah-password").val();

  const fd = new FormData();
  fd.append("id_pegawai", id_pegawai);
  fd.append("email", email);
  fd.append("username", username);
  fd.append("password", password);
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch(
    "https://sipanduberadat.com/api/admin-akomodasi/create/",
    {
      method: "POST",
      body: fd,
    }
  );
  const { status_code, data, message } = await req.json();

  if (status_code === 200) {
    await readAdmin();
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
    refreshToken(tambahAdmin);
  }
};
