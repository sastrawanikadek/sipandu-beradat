$("#form-action-kulkul").submit(async (e) => {
  e.preventDefault();
  startLoading();
  await actionKulkul();
});

function convertTime(time) {
  time = time * 6000;
  return time;
}

const actionKulkul = async () => {
  const idDevice = $("#action-id").val();
  const duration = convertTime($("#action-duration").val());
  const jenis_pelaporan = $("#action-jenis-pelaporan").val();

  const fd = new FormData();
  fd.append("code", idDevice);
  fd.append("id_jenis_pelaporan", jenis_pelaporan);
  fd.append("duration", duration);
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch(
    "https://sipanduberadat.com/api/sirine-akomodasi/ring/",
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
    refreshToken(actionKulkul);
  }
};
