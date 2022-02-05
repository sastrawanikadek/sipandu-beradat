$("#btn-hapus-jadwal").click(async () => {
  startLoading();
  await deleteJadwal();
});

const deleteJadwal = async () => {
  const XAT = `Bearer ${localStorage.getItem("access_token")}`;
  const fd = new FormData();
  const id = $("#hapus-id").val();
  
  fd.append("id_pecalang", id);
  fd.append("XAT", XAT);

  const req = await fetch(
    "https://api-sipandu-beradat.000webhostapp.com/jadwal-pecalang/delete/", {
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
    await read_jadwal();
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
    refreshToken(deleteJadwal)
  }
};
