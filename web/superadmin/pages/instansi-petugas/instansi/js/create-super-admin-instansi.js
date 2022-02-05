$("#form-tambah-super-admin").submit(async (e) => {
  e.preventDefault();
  await addPetugas();
});

const addPetugas = async () => {
  startLoading();
  const id_instansi = $("#edit-id").val();
  const name = $("#admin-name").val();
  const email = $("#admin-email").val();
  const username = $("#admin-username").val();
  const password = $("#admin-password").val();
  const phone = $("#admin-telp").val();
  const date_of_birth = $("#admin-tgl-lahir").val();
  const nik = $("#admin-nik").val();
  const gender = $("#admin-jenis-kelamin").val();
  const avatar = $("#admin-profil-pic").prop("files");

  const fd = new FormData();
  fd.append("id_instansi", id_instansi);
  fd.append("name", name);
  fd.append("email", email);
  fd.append("username", username);
  fd.append("password", password);
  fd.append("phone", phone);
  fd.append("date_of_birth", date_of_birth);
  fd.append("nik", nik);
  fd.append("gender", gender);

  if (avatar.length > 0) {
    fd.append("avatar", avatar[0]);
  }

  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch("https://sipanduberadat.com/api/petugas/create/", {
    method: "POST",
    body: fd,
  });
  const { status_code, message, data } = await req.json();
  if (status_code === 200) {
    addSuperAdminInstansi(data.id);
  } else if (status_code === 400) {
    alert(message);
  } else if (status_code === 401) {
    refreshToken(addPetugas);
  }
};

const addSuperAdminInstansi = async (id) => {
  const fd = new FormData();
  fd.append("id_petugas", id);
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req2 = await fetch(
    "https://sipanduberadat.com/api/admin-instansi/create/",
    {
      method: "POST",
      body: fd,
    }
  );
  const { status_code, message } = await req2.json();
  stopLoading();
  swaloading(status_code, "instansi.html", addSuperAdminInstansi, message);
};
