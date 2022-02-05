$("#form-tambah-krama-desa").submit(async (e) => {
  e.preventDefault();
  startLoading()
  await tambahKrama()
});

const tambahKrama = async () => {
  const id_banjar = $("#tambah-banjar").val();
  const name = $("#tambah-name").val();
  const username = $("#tambah-username").val();
  const password = $("#tambah-password").val();
  const phone = $("#tambah-phone").val();
  const date_of_birth = $("#tambah-birthday").val();
  const nik = $("#tambah-nik").val();
  const gender = $("#tambah-gender").val();
  const category = $("#tambah-category").val();
  const avatar = $("#tambah-avatar").prop("files");

  const fd = new FormData();
  fd.append("id_banjar", id_banjar);
  fd.append("name", name);
  fd.append("username", username);
  fd.append("password", password);
  fd.append("phone", phone);
  fd.append("date_of_birth", date_of_birth);
  fd.append("nik", nik);
  fd.append("gender", gender);
  fd.append("category", category);

  if (avatar.length > 0) {
    fd.append("avatar", avatar[0]);
  }

  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch(
    "https://api-sipandu-beradat.000webhostapp.com/masyarakat/create/", {
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
    await read_krama();
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
    refreshToken(tambahKrama)
  }
};
