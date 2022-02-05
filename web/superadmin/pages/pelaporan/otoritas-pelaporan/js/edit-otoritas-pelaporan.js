$("#form-edit-otoritas-pelaporan").submit(async (e) => {
  e.preventDefault();
  await updateOtoritasPelaporan();
});

const updateOtoritasPelaporan = async () => {
  startLoading();
  const idOtoritasPelaporan = $("#edit-id").val();
  const namaInstansiEdit = $("#edit-instansi").val();
  const jenisPelaporanEdit = $("#edit-jenis-pelaporan").val();

  const fd = new FormData();
  fd.append("id", idOtoritasPelaporan);
  fd.append("id_instansi", namaInstansiEdit);
  fd.append("id_jenis_pelaporan", jenisPelaporanEdit);
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch(
    "https://sipanduberadat.com/api/otoritas-pelaporan-instansi/update/",
    {
      method: "POST",
      body: fd,
    }
  );
  const { status_code, data, message } = await req.json();
  stopLoading();
  swaloading(
    status_code,
    "otoritas-pelaporan.html",
    updateOtoritasPelaporan,

    message
  );
};
