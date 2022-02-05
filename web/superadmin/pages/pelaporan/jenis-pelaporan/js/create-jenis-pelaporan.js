$("#form-tambah-jenis-pelaporan").submit(async (e) => {
  e.preventDefault();
  await addJenisPelaporan();
});

const addJenisPelaporan = async () => {
  startLoading();
  const name = $("#tambah-jenis-pelaporan").val();
  const icon = $("#tambah-icon").prop("files");
  const emergency_status = $("#tambah-status-pelaporan").val();

  const fd = new FormData();
  fd.append("name", name);
  fd.append("emergency_status", emergency_status);

  if (icon.length > 0) {
    fd.append("icon", icon[0]);
  }

  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch(
    "https://sipanduberadat.com/api/jenis-pelaporan/create/",
    {
      method: "POST",
      body: fd,
    }
  );
  stopLoading();
  const { status_code, message, data } = await req.json();
  swaloading(
    status_code,
    "jenis-pelaporan.html",
    addJenisPelaporan,

    message
  );
};
