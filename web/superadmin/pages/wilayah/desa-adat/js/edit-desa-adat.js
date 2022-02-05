$("#form-edit-desa-adat").submit(async (e) => {
  e.preventDefault();
  await updateDesaAdat();
});

const updateDesaAdat = async () => {
  startLoading();
  const idDesaAdat = $("#edit-id").val();
  const namaKecamatanEdit = $("#edit-kecamatan").val();
  const namaDesaAdatEdit = $("#edit-desa-adat").val();
  const latitudeDesaAdatEdit = $("#edit-latitude").val();
  const longitudeDesaAdatEdit = $("#edit-longitude").val();
  const statusDesaAdat = $("#edit-active-status").val();

  const fd = new FormData();
  fd.append("id", idDesaAdat);
  fd.append("id_kecamatan", namaKecamatanEdit);
  fd.append("name", namaDesaAdatEdit);
  fd.append("latitude", latitudeDesaAdatEdit);
  fd.append("longitude", longitudeDesaAdatEdit);
  fd.append("active_status", JSON.parse(statusDesaAdat));
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch("https://sipanduberadat.com/api/desa-adat/update/", {
    method: "POST",
    body: fd,
  });
  const { status_code, data, message } = await req.json();
  stopLoading();
  swaloading(status_code, "desa-adat.html", updateDesaAdat, message);
};
