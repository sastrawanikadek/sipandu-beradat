$("#btn-hapus-jenis-pelaporan").click(async () => {
  await removeJenisPelaporan();
});

const removeJenisPelaporan = async () => {
  const XAT = `Bearer ${localStorage.getItem("access_token")}`;
  const fd = new FormData();
  const idJenisPelaporan = $("#hapus-id").val();
  fd.append("XAT", XAT);
  fd.append("id", idJenisPelaporan);

  const req = await fetch(
    "https://sipanduberadat.com/api/jenis-pelaporan/delete/",
    {
      method: "POST",
      body: fd,
    }
  );

  const { status_code, data, message } = await req.json();
  swaloading(
    status_code,
    "jenis-pelaporan.html",
    removeJenisPelaporan,
    message
  );
};
