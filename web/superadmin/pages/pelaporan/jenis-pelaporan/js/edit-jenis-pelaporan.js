$("#form-edit-jenis-pelaporan").submit(async (e) => {
  e.preventDefault();
  await updateJenisPelaporan();
});

const updateJenisPelaporan = async () => {
  startLoading();
  const idJenisPelaporan = $("#edit-id").val();
  const jenisPelaporanEdit = $("#edit-jenis-pelaporan").val();
  const iconJenisPelaporanEdit = $("#edit-icon").prop("files");
  const statusDaruratEdit = $("#edit-status-pelaporan").val();
  const statusJenisPelaporan = $("#edit-active-status").val();

  const fd = new FormData();
  fd.append("id", idJenisPelaporan);
  fd.append("name", jenisPelaporanEdit);
  fd.append("emergency_status", statusDaruratEdit);
  fd.append("active_status", JSON.parse(statusJenisPelaporan));
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  if (iconJenisPelaporanEdit.length > 0) {
    fd.append("icon", iconJenisPelaporanEdit[0]);
  }

  const req = await fetch(
    "https://sipanduberadat.com/api/jenis-pelaporan/update/",
    {
      method: "POST",
      body: fd,
    }
  );
  stopLoading();
  const { status_code, data, message } = await req.json();
  swaloading(
    status_code,
    "jenis-pelaporan.html",
    updateJenisPelaporan,

    message
  );
};
