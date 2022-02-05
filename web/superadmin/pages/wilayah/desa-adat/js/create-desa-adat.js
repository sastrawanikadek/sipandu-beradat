$("#form-tambah-desa-adat").submit(async (e) => {
  e.preventDefault();
  await addDesaAdat();
});

const addDesaAdat = async () => {
  startLoading();
  const name = $("#tambah-desa-adat").val();
  const idKecamatan = $("#tambah-kecamatan").val();
  const latitude = $("#tambah-latitude").val();
  const longitude = $("#tambah-longitude").val();

  const fd = new FormData();
  fd.append("id_kecamatan", idKecamatan);
  fd.append("name", name);
  fd.append("latitude", latitude);
  fd.append("longitude", longitude);
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  const req = await fetch("https://sipanduberadat.com/api/desa-adat/create/", {
    method: "POST",
    body: fd,
  });
  const { status_code, message, data } = await req.json();
  stopLoading();
  swaloading(status_code, "desa-adat.html", addDesaAdat, message);
};
