$("#btn-hapus-instansi").click(async () => {
  await removeInstansi();
});

const removeInstansi = async () => {
  const XAT = `Bearer ${localStorage.getItem("access_token")}`;
  const fd = new FormData();
  const idInstansiHapus = $("#hapus-id").val();
  fd.append("XAT", XAT);
  fd.append("id", idInstansiHapus);

  const req = await fetch(
    "https://sipanduberadat.com/api/instansi-petugas/delete/",
    {
      method: "POST",
      body: fd,
    }
  );

  const { status_code, data, message } = await req.json();
  swaloading(status_code, "instansi.html", removeInstansi, message);
};
