$("#btn-send-otp").click(async () => {
  await sendOtp();
});

$("#btn-resend-otp").click(async () => {
  await sendOtp();
});

$("#form-change-password").submit(async (e) => {
  e.preventDefault();
  await changePassword();
});

const sendOtp = async () => {
  startLoading();
  const XAT = `Bearer ${localStorage.getItem("access_token")}`;
  const fd = new FormData();
  fd.append("XAT", XAT);

  const req = await fetch("https://sipanduberadat.com/api/otp/send/", {
    method: "POST",
    body: fd,
  });

  const { status_code, data, message } = await req.json();

  stopLoading();

  if (status_code === 200) {
    Swal.fire({
      title: "Berhasil!",
      text: message,
      icon: "success",
      confirmButtonText: "Tutup",
    });
    $("#modal-change-password-1").modal("hide");
    $("#modal-change-password-2").modal("show");
  } else if (status_code === 400) {
    Swal.fire({
      title: "Terjadi Kesalahan!",
      text: message,
      icon: "error",
      confirmButtonText: "Tutup",
    });
  } else if (status_code === 401) {
    refreshToken(sendOtp);
  }
};

const verifOtp = async () => {
  startLoading();
  const XAT = `Bearer ${localStorage.getItem("access_token")}`;
  const fd = new FormData();
  const code = $("#otp").val();

  fd.append("code", code);
  fd.append("XAT", XAT);

  const req = await fetch("https://sipanduberadat.com/api/otp/verify/", {
    method: "POST",
    body: fd,
  });

  const { status_code, data, message } = await req.json();

  stopLoading();

  if (status_code === 200) {
    Swal.fire({
      title: "Berhasil!",
      text: message,
      icon: "success",
      confirmButtonText: "Tutup",
    });
    $("#modal-change-password-2").modal("hide");
    $("#modal-change-password").modal("show");
  } else if (status_code === 400) {
    Swal.fire({
      title: "Terjadi Kesalahan!",
      text: message,
      icon: "error",
      confirmButtonText: "Tutup",
    });
  } else if (status_code === 401) {
    refreshToken(verifOtp);
  }
};

const changePassword = async () => {
  startLoading();
  const XAT = `Bearer ${localStorage.getItem("access_token")}`;
  const fd = new FormData();
  const code = $("#otp").val();
  const old_password = $("#old-password").val();
  const new_password = $("#new-password").val();
  const confirm_password = $("#confirm-password").val();

  if (new_password !== confirm_password) {
    Swal.fire({
      title: "Terjadi Kesalahan!",
      text: "Kata sandi tidak sama",
      icon: "error",
      confirmButtonText: "Tutup",
    });
    return;
  }

  fd.append("code", code);
  fd.append("old_password", old_password);
  fd.append("new_password", new_password);
  fd.append("XAT", XAT);

  const req = await fetch("https://sipanduberadat.com/api/password/change/", {
    method: "POST",
    body: fd,
  });

  const { status_code, data, message } = await req.json();

  stopLoading();

  if (status_code === 200) {
    Swal.fire({
      title: "Berhasil!",
      text: message,
      icon: "success",
      confirmButtonText: "Tutup",
    });
  } else if (status_code === 400) {
    Swal.fire({
      title: "Terjadi Kesalahan!",
      text: message,
      icon: "error",
      confirmButtonText: "Tutup",
    });
  } else if (status_code === 401) {
    refreshToken(changePassword);
  }
};
