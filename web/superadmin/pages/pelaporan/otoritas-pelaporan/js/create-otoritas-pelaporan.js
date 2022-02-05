$("#form-tambah-otoritas-pelaporan").submit(async (e) => {
  e.preventDefault();
  await addOtoritasPelaporan();
});

const addOtoritasPelaporan = async () => {
  const namaInstansi = $("#tambah-instansi").val();
  const jenisPelaporan = $("#tambah-jenis-pelaporan").val();

  const fd = new FormData();
  fd.append("id_instansi", namaInstansi);
  fd.append("id_jenis_pelaporan", jenisPelaporan);
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch(
    "https://sipanduberadat.com/api/otoritas-pelaporan-instansi/create/",
    {
      method: "POST",
      body: fd,
    }
  );
  const { status_code, message, data } = await req.json();
  swaloading(
    status_code,
    "otoritas-pelaporan.html",
    addOtoritasPelaporan,
    message
  );
};
