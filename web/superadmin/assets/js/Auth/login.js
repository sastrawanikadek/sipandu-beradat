const getCaptcha = async () => {
  const req = await fetch("https://sipanduberadat.com/api/captcha/");
  const { status_code, data, message } = await req.json();

  if (status_code === 200) {
    sessionStorage.setItem("captcha_id", data.id);
    $("#captcha-image").attr("src", data.url);
  }
};

$(document).ready(() => {
  getCaptcha();
});

$("#btn-refresh-captcha").click(() => getCaptcha());

$("form").submit(async (e) => {
  startLoading();
  e.preventDefault();

  const username = $("#username").val();
  const password = $("#password").val();
  const captcha = $("#captcha").val();

  const fd = new FormData();
  fd.append("username", username);
  fd.append("password", password);
  fd.append("id_captcha", sessionStorage.getItem("captcha_id"));
  fd.append("captcha", captcha);

  const req = await fetch("https://sipanduberadat.com/api/superadmin/login/", {
    method: "POST",
    body: fd,
  });
  const { status_code, data, message } = await req.json();
  stopLoading();

  if (status_code === 200) {
    localStorage.setItem("access_token", data.access_token);
    localStorage.setItem("refresh_token", data.refresh_token);
    localStorage.setItem("username", username);

    Swal.fire({
      title: "Berhasil!",
      text: message,
      icon: "success",
      confirmButtonText: "Dashboard",
    }).then((result) => {
      if (result.isConfirmed) {
        window.location.href = "https://sipanduberadat.com/superadmin/";
      }
    });
  } else if (status_code === 400) {
    Swal.fire({
      title: "Terjadi Kesalahan",
      text: message,
      icon: "error",
      confirmButtonText: "Tutup",
    });
  }
});
