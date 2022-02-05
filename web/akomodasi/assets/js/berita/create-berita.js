$("#form-tambah-berita").submit(async (e) => {
  e.preventDefault();
  startLoading();
  await tambahBerita();
});

const tambahBerita = async () => {
  const title = $("#tambah-title").val();
  const content = CKEDITOR.instances["tambah-content"].getData();
  const cover = $("#tambah-cover").prop("files");

  const fd = new FormData();
  fd.append("title", title);
  fd.append("content", content);
  if (cover.length > 0) {
    fd.append("cover", cover[0]);
  }
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch("https://sipanduberadat.com/api/berita/create/", {
    method: "POST",
    body: fd,
  });
  const { status_code, data, message } = await req.json();

  if (status_code === 200) {
    await readBerita();
    stopLoading();
    Swal.fire({
      title: "Berhasil!",
      text: message,
      icon: "success",
      confirmButtonText: "Tutup",
    });
    $("#form-tambah-berita").find("input:text, textarea").val("");
    $(".preview-img").attr("src", "../../assets/images/upload-file.svg");
  } else if (status_code === 400) {
    stopLoading();
    Swal.fire({
      title: "Terjadi Kesalahan!",
      text: message,
      icon: "error",
      confirmButtonText: "Tutup",
    });
  } else if (status_code === 401) {
    refreshToken(tambahBerita);
  }
};
