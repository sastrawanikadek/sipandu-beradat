$("#form-edit-tamu").submit(async (e) => {
  e.preventDefault();
  startLoading();
  await updateTamu();
});

const updateTamu = async () => {
  const id = $("#edit-id").val();
  const id_akomodasi = $("#edit-akomodasi").val();
  const id_negara = $("#edit-negara").val();
  const name = $("#edit-name").val();
  const email = $("#edit-email").val();
  const phone = $("#edit-phone").val();
  const birthday = $("#edit-birthday").val();
  const identity_type = $("#edit-identity-type").val();
  const identity_number = $("#edit-identity-number").val();
  const gender = $("#edit-gender").val();
  const avatar = $("#edit-avatar").prop("files");
  const active_status = $("#edit-status-aktif").val();
  const valid_status = $("#edit-valid-status").val();

  const fd = new FormData();
  fd.append("id", id);
  fd.append("id_akomodasi", id_akomodasi);
  fd.append("id_negara", id_negara);
  fd.append("name", name);
  fd.append("email", email);
  fd.append("phone", phone);
  fd.append("date_of_birth", birthday);
  fd.append("identity_type", identity_type);
  fd.append("identity_number", identity_number);
  fd.append("gender", gender);
  fd.append("active_status", JSON.parse(active_status));
  fd.append("valid_status", JSON.parse(valid_status));
  if (avatar.length > 0) {
    fd.append("avatar", avatar[0]);
  }
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch("https://sipanduberadat.com/api/tamu/update/", {
    method: "POST",
    body: fd,
  });
  const { status_code, data, message } = await req.json();

  if (status_code === 200) {
    await updateCheckIn(id);
  } else if (status_code === 400) {
    stopLoading();
    Swal.fire({
      title: "Terjadi Kesalahan",
      text: message,
      icon: "warning",
      confirmButtonText: "Tutup",
    });
  } else if (status_code === 401) {
    refreshToken(updateTamu);
  }
};

const updateCheckIn = async (id) => {
  const XAT = `Bearer ${localStorage.getItem("access_token")}`;
  const start_time = $("#edit-check-in").val();
  const end_time = $("#edit-check-out").val();

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
    refreshToken(updateCheckIn);
  }
};
