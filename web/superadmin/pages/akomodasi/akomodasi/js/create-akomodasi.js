$("#form-tambah-akomodasi").submit(async (e) => {
  e.preventDefault();
  await addAkomodasi();
});

const addAkomodasi = async () => {
  startLoading();
  const name = $("#tambah-akomodasi").val();
  const idDesaAdat = $("#tambah-desa-adat").val();
  const location = $("#tambah-alamat").val();
  const description = $("#tambah-deskripsi").val();
  const fotoAkomodasi = $("#tambah-profil-pic").prop("files");
  const coverAkomodasi = $("#tambah-cover-pic").prop("files");
  const fd = new FormData();
  fd.append("name", name);
  fd.append("id_desa", idDesaAdat);
  fd.append("location", location);
  fd.append("description", description);
  fd.append("XAT", `Bearer ${localStorage.getItem("access_token")}`);

  if (fotoAkomodasi.length > 0 && coverAkomodasi.length > 0) {
    fd.append("logo", fotoAkomodasi[0]);

    fd.append("cover", coverAkomodasi[0]);
  }

  const req = await fetch("https://sipanduberadat.com/api/akomodasi/create/", {
    method: "POST",
    body: fd,
  });

  const { status_code, message, data } = await req.json();
  stopLoading();
  swaloading(status_code, "akomodasi.html", addAkomodasi, message);
};
