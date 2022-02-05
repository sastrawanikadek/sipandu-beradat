$("#form-edit-instansi").submit(async (e) => {
  e.preventDefault();
  await updateInstansi();
});

const updateInstansi = async () => {
  startLoading();
  const idInstansi = $("#edit-id").val();
  const namaKecamatanEdit = $("#edit-kecamatan").val();
  const namaInstansiEdit = $("#edit-instansi").val();
  const jenisInstansiEdit = $("#edit-jenis-instansi").val();
  const otoritasPelaporanEdit = $("#edit-status-pelaporan").val();
  const statusInstansi = $("#edit-active-status").val();

  const fd = new FormData();
  fd.append("id", idInstansi);
  fd.append("id_kecamatan", namaKecamatanEdit);
  fd.append("id_jenis_instansi", jenisInstansiEdit);
  fd.append("name", namaInstansiEdit);
  fd.append("report_status", otoritasPelaporanEdit);
  fd.append("active_status", JSON.parse(statusInstansi));
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch(
    "https://sipanduberadat.com/api/instansi-petugas/update/",
    {
      method: "POST",
      body: fd,
    }
  );
  const { status_code, data, message } = await req.json();
  stopLoading();
  swaloading(status_code, "instansi.html", updateInstansi, message);
};
