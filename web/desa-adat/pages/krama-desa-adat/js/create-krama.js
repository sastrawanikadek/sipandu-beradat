$("#form-tambah-krama").submit(async (e) => {
  e.preventDefault();
  startLoading();
  await tambahKrama();
});

const tambahKrama = async () => {
  const banjar = $("#tambah-banjar").val();
  const name = $("#tambah-name").val();
  const username = $("#tambah-username").val();
  const password = $("#tambah-password").val();
  const phone = $("#tambah-phone").val();
  const email = $("#tambah-email").val();
  const birth = $("#tambah-birth").val();
  const nik = $("#tambah-nik").val();
  const gender = $("#tambah-gender").val();
  const category = $("#tambah-category").val();
  const avatar = $("#tambah-avatar").prop("files");

  const fd = new FormData();
  fd.append("id_banjar", banjar);
  fd.append("name", name);
  fd.append("username", username);
  fd.append("password", password);
  fd.append("phone", phone);
  fd.append("email", email);
  fd.append("date_of_birth", birth);
  fd.append("nik", nik);
  fd.append("gender", gender);
  fd.append("category", category);
  if (avatar.length > 0) {
    fd.append("avatar", avatar[0]);
  }
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch("https://sipanduberadat.com/api/masyarakat/create/", {
    method: "POST",
    body: fd,
  });
  const { status_code, data, message } = await req.json();

  if (status_code === 200) {
    await readKrama();
    stopLoading();
    $("#modal-tambah-krama").modal("hide");
    Swal.fire({
      title: "Berhasil!",
      text: message,
      icon: "success",
      confirmButtonText: "Tutup",
    });
    $("#form-tambah-krama").find("input").val("");
    $(".preview-img").attr("src", "../../assets/img/upload-file-square.svg");
  } else if (status_code === 400) {
    stopLoading();
    $("#modal-tambah-krama").modal("hide");
    Swal.fire({
      title: "Terjadi Kesalahan!",
      text: message,
      icon: "error",
      confirmButtonText: "Tutup",
    });
  } else if (status_code === 401) {
    refreshToken(tambahKrama);
  }
};
