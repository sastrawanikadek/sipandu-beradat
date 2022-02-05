$("#form-edit-pegawai").submit(async (e) => {
  e.preventDefault();
  startLoading();
  await updatePegawai();
});

const updatePegawai = async () => {
  const id = $("#edit-id").val();
  const id_akomodasi = $("#edit-akomodasi").val();
  const name = $("#edit-name").val();
  const phone = $("#edit-phone").val();
  const birthday = $("#edit-date-of-birth").val();
  const nik = $("#edit-nik").val();
  const gender = $("#edit-gender").val();
  const avatar = $("#edit-avatar").prop("files");
  const active_status = $("#edit-active-status").val();

  const fd = new FormData();
  fd.append("id", id);
  fd.append("id_akomodasi", id_akomodasi);
  fd.append("name", name);
  fd.append("phone", phone);
  fd.append("date_of_birth", birthday);
  fd.append("nik", nik);
  fd.append("gender", gender);
  fd.append("active_status", JSON.parse(active_status));
  if (avatar.length > 0) {
    fd.append("avatar", avatar[0]);
  }
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch(
    "https://sipanduberadat.com/api/pegawai-akomodasi/update/",
    {
      method: "POST",
      body: fd,
    }
  );
  const { status_code, data, message } = await req.json();

  if (status_code === 200) {
    await read_pegawai();
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
    refreshToken(updatePegawai);
  }
};
