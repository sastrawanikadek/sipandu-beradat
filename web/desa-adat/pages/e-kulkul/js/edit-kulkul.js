$("#form-edit-kulkul").submit(async (e) => {
  e.preventDefault();
  startLoading();
  await updateKulkul();
});

const updateKulkul = async () => {
  const id = $("#edit-id").val();
  const id_desa = $("#edit-id-desa").val();
  const code = $("#edit-code").val();
  const location = $("#edit-location").val();
  const photo = $("#edit-photo").prop("files");
  const active_status = $("#edit-active-status").val();

  const fd = new FormData();
  fd.append("id", id);
  fd.append("id_desa", id_desa);
  fd.append("code", code);
  fd.append("location", location);
  if (photo.length > 0) {
    fd.append("photo", photo[0]);
  }
  fd.append("active_status", JSON.parse(active_status));
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch(
    "https://sipanduberadat.com/api/sirine-desa/update/",
    {
      method: "POST",
      body: fd,
    }
  );
  const { status_code, message } = await req.json();

  if (status_code === 200) {
    await readKulkul();
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
    refreshToken(updateKulkul);
  }
};
