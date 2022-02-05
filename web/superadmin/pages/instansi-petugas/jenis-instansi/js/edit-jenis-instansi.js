$("#form-edit-jenis-instansi").submit(async (e) => {
  e.preventDefault();
  await updateJenisInstansi();
});

const updateJenisInstansi = async () => {
  startLoading();
  const idJenisInstansi = $("#edit-id").val();
  const namaJenisInstansiEdit = $("#edit-jenis-instansi").val();
  const statusJenisInstansiEdit = $("#edit-active-status").val();

  const fd = new FormData();
  fd.append("id", idJenisInstansi);
  fd.append("name", namaJenisInstansiEdit);
  fd.append("active_status", JSON.parse(statusJenisInstansiEdit));
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch(
    "https://sipanduberadat.com/api/jenis-instansi-petugas/update/",
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
    updateJenisInstansi,

    message
  );
};
