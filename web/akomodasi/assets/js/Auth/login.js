const getCaptcha = async () => {
  const req = await fetch("https://sipanduberadat.com/api/captcha/");
  const { status_code, data, message } = await req.json();

  if (status_code === 200) {
    stopLoading();
    sessionStorage.setItem("captcha_id", data.id);
    $("#captcha-image").attr("src", data.url);
  }
};

const getMe = async () => {
  const fd = new FormData();
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch(
    "https://sipanduberadat.com/api/admin-akomodasi/me/",
    {
      method: "POST",
      body: fd,
    }
  );
  const { status_code, data } = await req.json();

  if (status_code === 200) {
    localStorage.setItem("id_akomodasi", data.pegawai.akomodasi.id);
    localStorage.setItem("name", data.pegawai.name);
    localStorage.setItem("name_akomodasi", data.pegawai.akomodasi.name);
    localStorage.setItem("avatar", data.pegawai.avatar);
    localStorage.setItem("super_admin_status", data.super_admin_status);
  } else {
    getMe();
  }
};

$(document).ready(() => {
  getCaptcha();
});

$("#btn-refresh-captcha").click(() => {
  startLoading();
  getCaptcha();
});

$("form").submit(async (e) => {
  e.preventDefault();
  startLoading();

  const username = $("#username").val();
  const password = $("#password").val();
  const captcha = $("#captcha").val();

  const fd = new FormData();
  fd.append("username", username);
  fd.append("password", password);
  fd.append("id_captcha", sessionStorage.getItem("captcha_id"));
  fd.append("captcha", captcha);

  const req = await fetch(
    "https://sipanduberadat.com/api/admin-akomodasi/login/",
    {
      method: "POST",
      body: fd,
    }
  );
  const { status_code, data, message } = await req.json();

  if (status_code === 200) {
    localStorage.setItem("access_token", data.access_token);
    localStorage.setItem("refresh_token", data.refresh_token);

    await getMe();
    Swal.fire({
      title: "Berhasil!",
      text: message,
      icon: "success",
      confirmButtonText: '<i class="mdi mdi-apps pr-2"></i>Dashboard',
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = "https://sipanduberadat.com/akomodasi/";
      }
    });
    stopLoading();
  } else if (status_code === 400) {
    Swal.fire({
      title: "Terjadi Kesalahan",
      text: message,
      icon: "error",
      confirmButtonText: "Tutup",
    });
    stopLoading();
  }
});
