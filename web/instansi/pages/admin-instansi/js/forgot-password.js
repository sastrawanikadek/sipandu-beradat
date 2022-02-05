$("#btn-send-otp").click(async () => {
  startLoading();
  await sendOtp();
});

$("#btn-resend-otp").click(async () => {
  startLoading();
  await sendOtp();
});

$("#form-forgot-password").submit(async (e) => {
  e.preventDefault();
  startLoading();
  await forgotPassword();
});

const sendOtp = async () => {
  const XAT = `Bearer ${localStorage.getItem("access_token")}`;
  const fd = new FormData();
  const id = $("#forgot-id").val();

  fd.append("id", id);
  fd.append("XAT", XAT);

  const req = await fetch("https://sipanduberadat.com/api/otp/send/", {
    method: "POST",
    body: fd,
  });

  const { status_code, data, message } = await req.json();

  if (status_code === 200) {
    // await readAdmin();
    stopLoading();
    Swal.fire({
      title: "Berhasil!",
      text: message,
      icon: "success",
      confirmButtonText: "Tutup",
    });
    $("#modal-forgot-password-1").modal("hide");
    $("#modal-forgot-password-2").modal("show");
  } else if (status_code === 400) {
    stopLoading();
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
  const XAT = `Bearer ${localStorage.getItem("access_token")}`;
  const fd = new FormData();
  const id = $("#forgot-id").val();
  const code = $("#otp").val();

  fd.append("id", id);
  fd.append("code", code);
  fd.append("XAT", XAT);

  const req = await fetch("https://sipanduberadat.com/api/otp/verify/", {
    method: "POST",
    body: fd,
  });

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
    $("#modal-forgot-password-2").modal("hide");
    $("#modal-forgot-password").modal("show");
  } else if (status_code === 400) {
    stopLoading();
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

const forgotPassword = async () => {
  const XAT = `Bearer ${localStorage.getItem("access_token")}`;
  const fd = new FormData();
  const id = $("#forgot-id").val();
  const code = $("#otp").val();
  const new_password = $("#new-password").val();
  const confirm_password = $("#confirm-password").val();

  if (new_password !== confirm_password) {
    stopLoading();
    Swal.fire({
      title: "Terjadi Kesalahan!",
      text: "Kata sandi tidak sama",
      icon: "error",
      confirmButtonText: "Tutup",
    });
    return;
  }

  fd.append("id", id);
  fd.append("code", code);
  fd.append("new_password", new_password);
  fd.append("XAT", XAT);

  const req = await fetch("https://sipanduberadat.com/api/password/forgot/", {
    method: "POST",
    body: fd,
  });

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
    refreshToken(forgotPassword);
  }
};
