$("#form-edit-kulkul").submit(async (e) => {
  e.preventDefault();
  startLoading()
  await updateKulkul()
});

const updateKulkul = async () => {
  const id = $("#edit-id").val();
  const id_desa = $("#edit-id-desa").val();
  const kode = $("#edit-kode").val();
  const alamat = $("#edit-alamat").val();
  const status_aktif = $("#edit-status-aktif").val();

  const fd = new FormData();
  fd.append("id", id);
  fd.append("id_desa", id_desa);
  fd.append("code", kode);
  fd.append("location", alamat);
  fd.append("active_status", JSON.parse(status_aktif));
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch(
    "https://api-sipandu-beradat.000webhostapp.com/sirine-desa/update/", {
      method: "POST",
      body: fd,
    }
  );
  const {
    status_code,
    data,
    message
  } = await req.json();

  if (status_code === 200) {
    read_kulkul();
    stopLoading();
    Swal.fire({
      title: "Proses berhasil",
      text: message,
      icon: "success",
      confirmButtonText: "Tutup"
    })
  } else if (status_code === 400) {
    stopLoading();
    Swal.fire({
      title: "Terjadi Kesalahan",
      text: message,
      icon: "warning",
      confirmButtonText: "Tutup"
    })
  } else if (status_code === 401) {
    refreshToken(updateKulkul);
  }
};
