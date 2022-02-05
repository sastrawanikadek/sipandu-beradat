$("#form-edit-kabupaten").submit(async (e) => {
  e.preventDefault();
  await updateKabupaten();
});

const updateKabupaten = async () => {
  startLoading();
  const idKabupaten = $("#edit-id").val();
  const namaProvinsiEdit = $("#edit-provinsi").val();
  const namaKabupatenEdit = $("#edit-kabupaten").val();
  const statusKabupatenEdit = $("#edit-active-status").val();

  const fd = new FormData();
  fd.append("id", idKabupaten);
  fd.append("id_provinsi", namaProvinsiEdit);
  fd.append("name", namaKabupatenEdit);
  fd.append("active_status", JSON.parse(statusKabupatenEdit));
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch("https://sipanduberadat.com/api/kabupaten/update/", {
    method: "POST",
    body: fd,
  });
  const { status_code, data, message } = await req.json();
  stopLoading();
  swaloading(
    status_code,
    "kabupaten.html",
    updateKabupaten,

    message
  );
};
