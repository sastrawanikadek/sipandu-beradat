$("#form-tambah-jadwal").submit(async (e) => {
  e.preventDefault();
  startLoading();
  tambahJadwal();
});

const tambahJadwal = async () => {

  var arrayDays = []
  $("input:checkbox[name=type]:checked").each(function () {
    arrayDays.push($(this).val());
  });

  const id_pecalang = $("#tambah-id-pecalang").val();

  const fd = new FormData();
  fd.append("id_pecalang", id_pecalang);
  fd.append("days", `[${arrayDays}]`);
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch(
    "https://api-sipandu-beradat.000webhostapp.com/jadwal-pecalang/create/", {
      method: "POST",
      body: fd,
    }
  );
  const {
    status_code,
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
    refreshToken(tambahJadwal);
  }
};
