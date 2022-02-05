$("#form-edit-fasilitas").submit(async (e) => {
  e.preventDefault();
  await updateFasilitas();
});

const updateFasilitas = async () => {
  startLoading();
  const idFasilitas = $("#edit-id").val();
  const namaFasilitasEdit = $("#edit-fasilitas").val();
  const iconFasilitasEdit = $("#edit-icon").prop("files");
  const statusFasilitas = $("#edit-active-status").val();

  const fd = new FormData();
  fd.append("id", idFasilitas);
  fd.append("name", namaFasilitasEdit);
  fd.append("active_status", JSON.parse(statusFasilitas));
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  if (iconFasilitasEdit.length > 0) {
    fd.append("icon", iconFasilitasEdit[0]);
  }

  const req = await fetch("https://sipanduberadat.com/api/fasilitas/update/", {
    method: "POST",
    body: fd,
  });
  const { status_code, data, message } = await req.json();
  stopLoading();
  swaloading(
    status_code,
    "fasilitas.html",
    updateFasilitas,

    message
  );
};
