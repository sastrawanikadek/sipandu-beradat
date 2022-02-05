$("#form-tambah-wisatawan").submit(async (e) => {
  e.preventDefault();
  startLoading();
  await tambahTamu();
});

const tambahTamu = async () => {
  const id_akomodasi = localStorage.getItem("id_akomodasi");
  const id_negara = $("#tambah-negara").val();
  const name = $("#tambah-name").val();
  const email = $("#tambah-email").val();
  const username = $("#tambah-username").val();
  const password = $("#tambah-password").val();
  const confirm_password = $("#tambah-confirm-password").val();
  const phone = $("#tambah-phone").val();
  const date_of_birth = $("#tambah-birthday").val();
  const identity_type = $("#tambah-identity-type").val();
  const identity_number = $("#tambah-identity-number").val();
  const gender = $("#tambah-gender").val();
  const avatar = $("#tambah-avatar").prop("files");

  if (password !== confirm_password) {
    stopLoading();
    Swal.fire({
      title: "Terjadi Kesalahan!",
      text: "Kata sandi tidak sama",
      icon: "error",
      confirmButtonText: "Tutup",
    });
    return;
  }

  const fd = new FormData();
  fd.append("id_akomodasi", id_akomodasi);
  fd.append("id_negara", id_negara);
  fd.append("name", name);
  fd.append("email", email);
  fd.append("username", username);
  fd.append("password", password);
  fd.append("phone", phone);
  fd.append("date_of_birth", date_of_birth);
  fd.append("identity_type", identity_type);
  fd.append("identity_number", identity_number);
  fd.append("gender", gender);
  if (avatar.length > 0) {
    fd.append("avatar", avatar[0]);
  }

  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch("https://sipanduberadat.com/api/tamu/create/", {
    method: "POST",
    body: fd,
  });
  const { status_code, message, data } = await req.json();

  if (status_code === 200) {
    await tambahCheckIn(data.id);
  } else if (status_code === 400 || status_code === 500) {
    stopLoading();
    Swal.fire({
      title: "Terjadi Kesalahan!",
      text: message,
      icon: "error",
      confirmButtonText: "Tutup",
    });
  } else if (status_code === 401) {
    refreshToken(tambahTamu);
  }
};

const tambahCheckIn = async (id) => {
  const XAT = `Bearer ${localStorage.getItem("access_token")}`;
  const start_time = $("#tambah-check-in").val();
  const end_time = $("#tambah-check-out").val();

  const fd = new FormData();
  fd.append("XAT", XAT);
  fd.append("id_tamu", id);
  fd.append("start_time", start_time);
  fd.append("end_time", end_time);

  const req = await fetch("https://sipanduberadat.com/api/tamu/check-in/", {
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
  } else if (status_code === 400 || status_code === 500) {
    stopLoading();
    Swal.fire({
      title: "Terjadi Kesalahan!",
      text: message,
      icon: "error",
      confirmButtonText: "Tutup",
    });
  } else if (status_code === 401) {
    refreshToken(tambahCheckIn);
  }
};
