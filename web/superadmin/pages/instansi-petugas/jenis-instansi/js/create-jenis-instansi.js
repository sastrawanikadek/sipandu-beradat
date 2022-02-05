$("#form-tambah-jenis-instansi").submit(async (e) => {
  e.preventDefault();
  await addJenisInstansi();
});

const addJenisInstansi = async () => {
  startLoading();
  const name = $("#tambah-jenis-instansi").val();

  const fd = new FormData();
  fd.append("name", name);
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch(
    "https://sipanduberadat.com/api/jenis-instansi-petugas/create/",
    {
      method: "POST",
      body: fd,
    }
  );
  const { status_code, data, message } = await req.json();
  stopLoading();
  swaloading(
    status_code,
    "jenis-instansi.html",
    addJenisInstansi,

    message
  );
};
