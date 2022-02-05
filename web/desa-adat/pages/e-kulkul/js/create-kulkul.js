$("#form-tambah-kulkul").submit(async (e) => {
  e.preventDefault();
  startLoading();
  await tambahKulkul();
});

const tambahKulkul = async () => {
  const code = $("#tambah-kode").val();
  const location = $("#tambah-location").val();
  const photo = $("#tambah-photo").prop("files");

  const fd = new FormData();
  fd.append("id_desa", localStorage.getItem("id_desa"));
  fd.append("code", code);
  fd.append("location", location);
  if (photo.length > 0) {
    fd.append("photo", photo[0]);
  }
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch(
    "https://sipanduberadat.com/api/sirine-desa/create/",
    {
      method: "POST",
      body: fd,
    }
  );
  const { status_code, data, message } = await req.json();

  if (status_code === 200) {
    $("#form-tambah-kulkul").find("input:text").val("");
    $(".preview-img").attr("src", "../../assets/img/upload-file-square.svg");
    await readKulkul();
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
