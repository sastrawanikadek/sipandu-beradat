$("#form-tambah-petugas").submit(async (e) => {
  e.preventDefault();
  startLoading();
  await tambahPetugas();
});

const tambahPetugas = async () => {
  const id_instansi = localStorage.getItem("id_instansi");
  const name = $("#tambah-nama").val();
  const username = $("#tambah-username").val();
  const password = $("#tambah-password").val();
  const email = $("#tambah-email").val();
  const phone = $("#tambah-telp").val();
  const date_of_birth = $("#tambah-tgl-lahir").val();
  const nik = $("#tambah-nik").val();
  const gender = $("#tambah-jenis-kelamin").val();
  const avatar = $("#tambah-avatar").prop("files");

  const fd = new FormData();
  fd.append("id_instansi", id_instansi);
  fd.append("name", name);
  fd.append("username", username);
  fd.append("password", password);
  fd.append("phone", phone);
  fd.append("email", email);
  fd.append("date_of_birth", date_of_birth);
  fd.append("nik", nik);
  fd.append("gender", gender);
  if (avatar.length > 0) {
    fd.append("avatar", avatar[0]);
  }

  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch("https://sipanduberadat.com/api/petugas/create/", {
    method: "POST",
    body: fd,
  });
  const { status_code, data, message } = await req.json();

  if (status_code === 200) {
    $("#form-tambah-petugas").find("input").val("");
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
    refreshToken(tambahPetugas);
  }
};
