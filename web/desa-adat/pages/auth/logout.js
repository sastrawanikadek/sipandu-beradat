$(".btn-logout").click(async () => {
  startLoading();
  await logout();
});

const logout = async () => {
  const fd = new FormData();
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch("https://sipanduberadat.com/api/logout/", {
    method: "POST",
    body: fd,
  });

  const { status_code, data } = await req.json();

  if (status_code === 200) {
    stopLoading();
    localStorage.clear();

    window.location.href = "https://sipanduberadat.com/desa-adat/pages/login/";
  } else if (status_code === 401) {
    refreshToken(logout);
  } else {
    logout();
  }
};
