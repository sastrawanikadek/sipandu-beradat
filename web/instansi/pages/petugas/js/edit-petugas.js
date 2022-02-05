$("#form-edit-petugas").submit(async (e) => {
  e.preventDefault();
  startLoading();
  await updatePetugas();
});

const updatePetugas = async () => {
  startLoading();
  const id = $("#edit-id").val();
  const id_instansi = $("#edit-id-instansi").val();
  const nama = $("#edit-nama").val();
  const phone = $("#edit-telp").val();
  const birth = $("#edit-tgl-lahir").val();
  const nik = $("#edit-nik").val();
  const gender = $("#edit-jenis-kelamin").val();
  const active_status = $("#edit-active-status").val();
  const avatar = $("#edit-profil-pic").prop("files");
  const email = $("#edit-email").val();

  const fd = new FormData();
  fd.append("id", id);
  fd.append("id_instansi", id_instansi);
  fd.append("name", nama);
  fd.append("phone", phone);
  fd.append("date_of_birth", birth);
  fd.append("nik", nik);
  fd.append("gender", gender);
  fd.append("email", email);
  fd.append("active_status", JSON.parse(active_status));
  if (avatar.length > 0) {
    fd.append("avatar", avatar[0]);
  }
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch("https://sipanduberadat.com/api/petugas/update/", {
    method: "POST",
    body: fd,
  });
  const { status_code, message } = await req.json();
  if (status_code === 200) {
    await readPetugas();
    stopLoading();
    Swal.fire({
      title: "Proses berhasil",
      text: message,
      icon: "success",
      confirmButtonText: "Tutup",
    });
  } else if (status_code === 400) {
    stopLoading();
    Swal.fire({
      title: "Terjadi Kesalahan",
      text: message,
      icon: "warning",
      confirmButtonText: "Tutup",
    });
  } else if (status_code === 401) {
    refreshToken(updatePetugas);
  }
};
