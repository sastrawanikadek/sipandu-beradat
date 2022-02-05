$("#btn-hapus-otoritas-pelaporan").click(async () => {
  await removeOtoritasPelaporan();
});

const removeOtoritasPelaporan = async () => {
  const XAT = `Bearer ${localStorage.getItem("access_token")}`;
  const fd = new FormData();
  const idOtoritasPelaporan = $("#hapus-id").val();
  fd.append("XAT", XAT);
  fd.append("id", idOtoritasPelaporan);

  const req = await fetch(
    "https://sipanduberadat.com/api/otoritas-pelaporan-instansi/delete/",
    {
      method: "POST",
      body: fd,
    }
  );

  const { status_code, data, message } = await req.json();
  swaloading(
    status_code,
    "otoritas-pelaporan.html",
    removeOtoritasPelaporan,
    message
  );
};
