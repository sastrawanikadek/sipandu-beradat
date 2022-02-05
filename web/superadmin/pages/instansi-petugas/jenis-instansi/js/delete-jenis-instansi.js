$("#btn-hapus-jenis-instansi").click(async () => {
  await removeJenisInstansi();
});

const removeJenisInstansi = async () => {
  const XAT = `Bearer ${localStorage.getItem("access_token")}`;
  const fd = new FormData();
  const idJenisInstansiHapus = $("#hapus-id").val();
  fd.append("XAT", XAT);
  fd.append("id", idJenisInstansiHapus);

  const req = await fetch(
    "https://sipanduberadat.com/api/jenis-instansi-petugas/delete/",
    {
      method: "POST",
      body: fd,
    }
  );

  const { status_code, data, message } = await req.json();

  swaloading(status_code, "jenis-instansi.html", removeJenisInstansi, message);
};
