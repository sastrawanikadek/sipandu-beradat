$("#form-edit-kulkul").submit(async (e) => {
  e.preventDefault();
  startLoading();
  await updateKulkul();
});

const updateKulkul = async () => {
  const id = $("#edit-id").val();
  const id_akomodasi = $("#edit-id-akomodasi").val();
  const kode = $("#edit-kode").val();
  const alamat = $("#edit-alamat").val();
  const status_aktif = $("#edit-status-aktif").val();
  const foto = $("#edit-foto").prop("files");

  const fd = new FormData();
  fd.append("id", id);
  fd.append("id_akomodasi", id_akomodasi);
  fd.append("code", kode);
  fd.append("location", alamat);
  if (foto.length > 0) {
    fd.append("photo", foto[0]);
  }
  fd.append("active_status", JSON.parse(status_aktif));
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch(
    "https://sipanduberadat.com/api/sirine-akomodasi/update/",
    {
      method: "POST",
      body: fd,
    }
  );
  const { status_code, data, message } = await req.json();

  if (status_code === 200) {
    read_kulkul();
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
