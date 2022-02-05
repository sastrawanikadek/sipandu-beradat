$("#form-edit-berita").submit(async (e) => {
  e.preventDefault();
  startLoading();
  await updateBerita();
});

const updateBerita = async () => {
  const id = $("#edit-id").val();
  const title = $("#edit-title").val();
  const content = CKEDITOR.instances["edit-content"].getData();
  const cover = $("#edit-cover").prop("files");
  const active_status = $("#edit-active-status").val();

  const fd = new FormData();
  fd.append("id", id);
  fd.append("title", title);
  fd.append("content", content);
  if (cover.length > 0) {
    fd.append("cover", cover[0]);
  }
  fd.append("active_status", JSON.parse(active_status));
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch("https://sipanduberadat.com/api/berita/update/", {
    method: "POST",
    body: fd,
  });
  const { status_code, data, message } = await req.json();

  if (status_code === 200) {
    await readBerita();
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
    refreshToken(updateBerita);
  }
};
