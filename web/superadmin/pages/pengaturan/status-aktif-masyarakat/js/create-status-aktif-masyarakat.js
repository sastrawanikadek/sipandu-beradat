$("#form-tambah-status-aktif-masyarakat").submit(async (e) => {
  e.preventDefault();
  await addStatusMasyarakat();
});

const addStatusMasyarakat = async () => {
  startLoading();
  const name = $("#tambah-nama-status").val();
  const status = $("#tambah-status-masyarakat").val();

  const fd = new FormData();
  fd.append("name", name);
  fd.append("status", JSON.parse(status));
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch(
    "https://sipanduberadat.com/api/status-aktif-masyarakat/create/",
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
    addStatusMasyarakat,

    message
  );
};
