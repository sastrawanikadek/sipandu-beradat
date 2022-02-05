$("#form-edit-status-aktif-masyarakat").submit(async (e) => {
  e.preventDefault();
  await updateStatusMasyarakat();
});

const updateStatusMasyarakat = async () => {
  startLoading();
  const id = $("#edit-id").val();
  const nama = $("#edit-nama-status").val();
  const status = $("#edit-status-masyarakat").val();
  const active_status = $("#edit-active-status").val();

  const fd = new FormData();
  fd.append("id", id);
  fd.append("name", nama);
  fd.append("status", JSON.parse(status));
  fd.append("active_status", JSON.parse(active_status));
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch(
    "https://sipanduberadat.com/api/status-aktif-masyarakat/update/",
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
    updateStatusMasyarakat,

    message
  );
};
