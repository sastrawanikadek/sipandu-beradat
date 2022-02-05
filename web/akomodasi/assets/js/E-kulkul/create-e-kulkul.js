$("#form-tambah-kulkul").submit(async (e) => {
  e.preventDefault();
  startLoading();
  await tambahKulkul();
});

const tambahKulkul = async () => {
  const id_akomodasi = localStorage.getItem("id_akomodasi");
  const kode = $("#tambah-kode").val();
  const alamat = $("#tambah-alamat").val();
  const foto = $("#tambah-foto").prop("files");

  const fd = new FormData();
  fd.append("id_akomodasi", id_akomodasi);
  fd.append("code", kode);
  fd.append("location", alamat);
  if (foto.length > 0) {
    fd.append("photo", foto[0]);
  }
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch(
    "https://sipanduberadat.com/api/sirine-akomodasi/create/",
    {
      method: "POST",
      body: fd,
    }
  );
  const { status_code, message } = await req.json();

  if (status_code === 200) {
    await read_kulkul();
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
    refreshToken(tambahKulkul);
  }
};
