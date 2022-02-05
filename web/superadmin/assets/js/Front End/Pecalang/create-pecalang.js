$("#form-tambah-pecalang").submit(async (e) => {
  e.preventDefault();
  startLoading();
  tambahPecalang();
});

const tambahPecalang = async () => {
  const id_masyarakat = $("#tambah-nama-pecalang").val();
  const sirine_authority = $("#tambah-otoritas-sirine").val() === "checked";

  const fd = new FormData();
  fd.append("id_masyarakat", id_masyarakat);
  fd.append("sirine_authority", sirine_authority);
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch(
    "https://api-sipandu-beradat.000webhostapp.com/pecalang/create/", {
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
    await read_pecalang();
    stopLoading();
    Swal.fire({
      title: "Berhasil!",
      text: message,
      icon: "success",
      confirmButtonText: "Tutup"
    })
  } else if (status_code === 400) {
    stopLoading();
    Swal.fire({
      title: "Terjadi Kesalahan!",
      text: message,
      icon: "error",
      confirmButtonText: "Tutup"
    })
  } else if (status_code === 401) {
    refreshToken(tambahPecalang);
  }
};
