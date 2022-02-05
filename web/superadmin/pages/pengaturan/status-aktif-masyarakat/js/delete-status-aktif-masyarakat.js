$("#btn-hapus-status-aktif-masyarakat").click(async () => {
  await removeStatusMasyarakat();
});

const removeStatusMasyarakat = async () => {
  startLoading();
  const XAT = `Bearer ${localStorage.getItem("access_token")}`;
  const fd = new FormData();
  const id = $("#hapus-id").val();
  fd.append("XAT", XAT);
  fd.append("id", id);

  const req = await fetch(
    "https://sipanduberadat.com/api/status-aktif-masyarakat/delete/",
    {
      method: "POST",
      body: fd,
    }
  );

  const { status_code, data, message } = await req.json();
  stopLoading();
  swaloading(
    status_code,
    "status-aktif-masyarakat.html",
    removeStatusMasyarakat,

    message
  );
};
