$(".btn-logout").click(async () => {
  await logout();
});

const logout = async () => {
  startLoading();
  const fd = new FormData();
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch("https://sipanduberadat.com/api/logout/", {
    method: "POST",
    body: fd,
  });

  const { status_code, data } = await req.json();

  if (status_code === 200) {
    stopLoading();
    localStorage.removeItem("access_token");
    localStorage.removeItem("refresh_token");
    localStorage.removeItem("super_admin_status");
    localStorage.removeItem("avatar");
    localStorage.removeItem("id_instansi");
    localStorage.removeItem("jenis_instansi");
    localStorage.removeItem("name");
    localStorage.removeItem("alamat_instansi");

    window.location.href = "https://sipanduberadat.com/instansi/pages/login/";
  } else if (status_code === 401) {
    refreshToken(logout);
  } else {
    logout();
  }
};
