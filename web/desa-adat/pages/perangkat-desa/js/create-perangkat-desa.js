$("#form-tambah-pecalang").submit(async (e) => {
  e.preventDefault();
  startLoading();
  await tambahPecalang();
});

const tambahPecalang = async () => {
  const pecalang = $("#tambah-pecalang").val();
  const sirine_authority = $("#tambah-sirine-authority").val();
  const status_prajuru = $("#tambah-status-prajuru").val();

  const fd = new FormData();
  fd.append("id_masyarakat", pecalang);
  fd.append("sirine_authority", sirine_authority);
  fd.append("prajuru_status", status_prajuru);
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch("https://sipanduberadat.com/api/pecalang/create/", {
    method: "POST",
    body: fd,
  });
  const { status_code, data, message } = await req.json();

  if (status_code === 200) {
    await readPecalang();
    stopLoading();
    Swal.fire({
      title: "Berhasil!",
      text: message,
      icon: "success",
      confirmButtonText: "Tutup",
    });
    $("#form-tambah-pecalang").find("input:text").val("");
  } else if (status_code === 400) {
    stopLoading();
    Swal.fire({
      title: "Terjadi Kesalahan!",
      text: message,
      icon: "error",
      confirmButtonText: "Tutup",
    });
  } else if (status_code === 401) {
    refreshToken(tambahPecalang);
  }
};
